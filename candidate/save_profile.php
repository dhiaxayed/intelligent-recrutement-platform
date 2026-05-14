<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'candidate') {
    header('Location: ../signin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

function redirectWithError(string $error): void
{
    header('Location: profile.php?error=' . urlencode($error));
    exit;
}

function isValidHttpUrl(string $url): bool
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $scheme = parse_url($url, PHP_URL_SCHEME);
    return in_array(strtolower((string) $scheme), ['http', 'https'], true);
}

function removeOldCv(?string $relativePath): void
{
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

if ($phone === '' || $linkedin === '') {
    redirectWithError('missing_fields');
}

if (!isValidHttpUrl($linkedin) || ($github !== '' && !isValidHttpUrl($github))) {
    redirectWithError('invalid_url');
}

$stmt = $pdo->prepare('SELECT id, cv_path FROM candidate_profiles WHERE candidate_id = ? LIMIT 1');
$stmt->execute([$candidateId]);
$existingProfile = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

$cvUpload = $_FILES['cv'] ?? null;
if ($cvUpload && (!isset($cvUpload['error']) || is_array($cvUpload['error']))) {
    redirectWithError('invalid_cv');
}

$hasUploadedCv = $cvUpload && $cvUpload['error'] !== UPLOAD_ERR_NO_FILE;
$newCvPath = $existingProfile['cv_path'] ?? null;

if (!$existingProfile && !$hasUploadedCv) {
    redirectWithError('missing_cv');
}

if ($hasUploadedCv) {
    $cv = $cvUpload;

    if ($cv['error'] !== UPLOAD_ERR_OK) {
        redirectWithError('upload_failed');
    }

    if ($cv['size'] > 5 * 1024 * 1024) {
        redirectWithError('cv_too_large');
    }

    // Vérification extension seulement — sans finfo
    $extension = strtolower(pathinfo($cv['name'], PATHINFO_EXTENSION));
    if ($extension !== 'pdf') {
        redirectWithError('invalid_cv');
    }

    $uploadDir = __DIR__ . '/../uploads/cvs';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        redirectWithError('upload_failed');
    }

    $fileName = 'cv_candidate_' . $candidateId . '_' . time() . '.pdf';
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($cv['tmp_name'], $destination)) {
        redirectWithError('upload_failed');
    }

    $newCvPath = 'uploads/cvs/' . $fileName;
}

try {
    if ($existingProfile) {
        $stmt = $pdo->prepare('UPDATE candidate_profiles SET phone = ?, linkedin = ?, github = ?, cv_path = ? WHERE candidate_id = ?');
        $stmt->execute([$phone, $linkedin, $github !== '' ? $github : null, $newCvPath, $candidateId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO candidate_profiles (candidate_id, phone, linkedin, github, cv_path) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$candidateId, $phone, $linkedin, $github !== '' ? $github : null, $newCvPath]);
    }

    if ($hasUploadedCv && $existingProfile && $existingProfile['cv_path'] !== $newCvPath) {
        removeOldCv($existingProfile['cv_path']);
    }

    header('Location: profile.php?success=1');
    exit;
} catch (PDOException $e) {
    if ($hasUploadedCv && $newCvPath) {
        removeOldCv($newCvPath);
    }

    redirectWithError('server');
}