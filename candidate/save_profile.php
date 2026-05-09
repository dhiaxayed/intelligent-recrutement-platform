<?php
session_start();

// This endpoint changes candidate-owned data, so it must enforce role on its own.
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'candidate') {
    header('Location: ../signin.php');
    exit;
}

// Profiles are saved only through POST form submissions.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

function redirectWithError(string $error): void
{
    // Keep redirects predictable for the simple message handling in profile.php.
    header('Location: profile.php?error=' . urlencode($error));
    exit;
}

function isValidHttpUrl(string $url): bool
{
    // FILTER_VALIDATE_URL accepts several schemes; candidate profile links should be web URLs only.
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $scheme = parse_url($url, PHP_URL_SCHEME);
    return in_array(strtolower((string) $scheme), ['http', 'https'], true);
}

function removeOldCv(?string $relativePath): void
{
    // Only delete files that resolve inside uploads/cvs. This protects against path tricks.
    if (!$relativePath) {
        return;
    }

    $baseDir = realpath(__DIR__ . '/../uploads/cvs');
    $oldFile = realpath(__DIR__ . '/../' . $relativePath);

    if ($baseDir && $oldFile && strpos($oldFile, $baseDir . DIRECTORY_SEPARATOR) === 0 && is_file($oldFile)) {
        unlink($oldFile);
    }
}

$candidateId = (int) $_SESSION['user_id'];
$phone = trim($_POST['phone'] ?? '');
$linkedin = trim($_POST['linkedin'] ?? '');
$github = trim($_POST['github'] ?? '');

// Server-side validation is required because browser/JS validation can be bypassed.
if ($phone === '' || $linkedin === '') {
    redirectWithError('missing_fields');
}

if (!isValidHttpUrl($linkedin) || ($github !== '' && !isValidHttpUrl($github))) {
    redirectWithError('invalid_url');
}

$stmt = $pdo->prepare('SELECT id, cv_path FROM candidate_profiles WHERE candidate_id = ? LIMIT 1');
$stmt->execute([$candidateId]);
$existingProfile = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

// First-time profile creation requires a CV. Existing profiles can update text fields without reuploading.
$cvUpload = $_FILES['cv'] ?? null;
if ($cvUpload && (!isset($cvUpload['error']) || is_array($cvUpload['error']))) {
    // The form expects exactly one file; reject crafted cv[] uploads.
    redirectWithError('invalid_cv');
}

$hasUploadedCv = $cvUpload && $cvUpload['error'] !== UPLOAD_ERR_NO_FILE;
$newCvPath = $existingProfile['cv_path'] ?? null;

if (!$existingProfile && !$hasUploadedCv) {
    redirectWithError('missing_cv');
}

if ($hasUploadedCv) {
    $cv = $cvUpload;

    // PHP upload errors are handled before checking file content.
    if ($cv['error'] !== UPLOAD_ERR_OK) {
        redirectWithError('upload_failed');
    }

    // Match the project requirement: PDFs only, maximum 5 MB.
    if ($cv['size'] > 5 * 1024 * 1024) {
        redirectWithError('cv_too_large');
    }

    // Check both extension and MIME type. Neither is perfect alone, but together they reject common bad uploads.
    $extension = strtolower(pathinfo($cv['name'], PATHINFO_EXTENSION));
    if ($extension !== 'pdf') {
        redirectWithError('invalid_cv');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($cv['tmp_name']);
    if (!in_array($mimeType, ['application/pdf', 'application/x-pdf'], true)) {
        redirectWithError('invalid_cv');
    }

    $uploadDir = __DIR__ . '/../uploads/cvs';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        redirectWithError('upload_failed');
    }

    // Generate the stored name ourselves; never reuse the browser-provided filename.
    $fileName = 'cv_candidate_' . $candidateId . '_' . time() . '.pdf';
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($cv['tmp_name'], $destination)) {
        redirectWithError('upload_failed');
    }

    $newCvPath = 'uploads/cvs/' . $fileName;
}

try {
    // candidate_id is UNIQUE, so each candidate has at most one profile row.
    if ($existingProfile) {
        $stmt = $pdo->prepare('UPDATE candidate_profiles SET phone = ?, linkedin = ?, github = ?, cv_path = ? WHERE candidate_id = ?');
        $stmt->execute([$phone, $linkedin, $github !== '' ? $github : null, $newCvPath, $candidateId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO candidate_profiles (candidate_id, phone, linkedin, github, cv_path) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$candidateId, $phone, $linkedin, $github !== '' ? $github : null, $newCvPath]);
    }

    if ($hasUploadedCv && $existingProfile && $existingProfile['cv_path'] !== $newCvPath) {
        // Delete the previous CV only after the database update succeeds.
        removeOldCv($existingProfile['cv_path']);
    }

    header('Location: profile.php?success=1');
    exit;
} catch (PDOException $e) {
    if ($hasUploadedCv && $newCvPath) {
        // If the DB write fails after upload, remove the new orphan file.
        removeOldCv($newCvPath);
    }

    redirectWithError('server');
}
