<?php
session_start();

// Every write endpoint performs its own auth check.
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'recruiter') {
    header('Location: ../signin.php');
    exit;
}

// Job creation only through POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create_job.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

function redirectWithError(string $error): void
{
    header('Location: create_job.php?error=' . urlencode($error));
    exit;
}

$recruiterId = (int) $_SESSION['user_id'];
$title = trim($_POST['title'] ?? '');
$company = trim($_POST['company'] ?? '');
$location = trim($_POST['location'] ?? '');
$description = trim($_POST['description'] ?? '');
$requirements = trim($_POST['requirements'] ?? '');

// Server-side validation is required.
if ($title === '' || $company === '' || $location === '' || $description === '' || $requirements === '') {
    redirectWithError('missing_fields');
}

// Basic length validation to prevent database abuse.
if (strlen($title) > 255 || strlen($company) > 255 || strlen($location) > 255) {
    redirectWithError('invalid_input');
}

if (strlen($description) < 10 || strlen($requirements) < 10) {
    redirectWithError('invalid_input');
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO job_profiles (recruiter_id, title, company, location, description, requirements, created_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([$recruiterId, $title, $company, $location, $description, $requirements]);

    header('Location: applications.php?success=job_created');
    exit;
} catch (PDOException $e) {
    redirectWithError('server');
}

