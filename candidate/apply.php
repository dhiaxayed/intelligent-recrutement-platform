<?php
session_start();

// Every write endpoint performs its own auth check; do not depend on jobs.php hiding buttons.
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'candidate') {
    header('Location: ../signin.php');
    exit;
}

// Applying changes server state, so GET requests are rejected.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: jobs.php?error=invalid_request');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$candidateId = (int) $_SESSION['user_id'];
$jobId = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);

// Treat missing, non-numeric, and zero job IDs as invalid user input.
if (!$jobId) {
    header('Location: jobs.php?error=invalid_request');
    exit;
}

// Candidates must complete a profile and upload a CV before applying.
$stmt = $pdo->prepare('SELECT id FROM candidate_profiles WHERE candidate_id = ? LIMIT 1');
$stmt->execute([$candidateId]);
if (!$stmt->fetch()) {
    header('Location: jobs.php?error=missing_profile');
    exit;
}

// Never trust the posted job_id; make sure the job still exists.
$stmt = $pdo->prepare('SELECT id FROM job_profiles WHERE id = ? LIMIT 1');
$stmt->execute([$jobId]);
if (!$stmt->fetch()) {
    header('Location: jobs.php?error=invalid_job');
    exit;
}

// Check first for a nicer user message. The database UNIQUE constraint is still the final protection.
$stmt = $pdo->prepare('SELECT id FROM applications WHERE candidate_id = ? AND job_id = ? LIMIT 1');
$stmt->execute([$candidateId, $jobId]);
if ($stmt->fetch()) {
    header('Location: jobs.php?error=already_applied');
    exit;
}

try {
    // New applications always start as pending. Person 3 owns accept/reject behavior.
    $stmt = $pdo->prepare('INSERT INTO applications (candidate_id, job_id, status) VALUES (?, ?, ?)');
    $stmt->execute([$candidateId, $jobId, 'pending']);

    header('Location: jobs.php?success=applied');
    exit;
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        // Handles race conditions where two requests apply to the same job at nearly the same time.
        header('Location: jobs.php?error=already_applied');
        exit;
    }

    header('Location: jobs.php?error=server');
    exit;
}
