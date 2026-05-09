<?php
session_start();

// Every write endpoint performs its own auth check.
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'recruiter') {
    header('Location: ../signin.php');
    exit;
}

// Reject application only through POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: applications.php?error=invalid_request');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$recruiterId = (int) $_SESSION['user_id'];
$applicationId = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);

if (!$applicationId) {
    header('Location: applications.php?error=invalid_request');
    exit;
}

try {
    // Verify the application belongs to one of the recruiter's jobs
    $stmt = $pdo->prepare(
        'SELECT a.id, a.status, a.candidate_id, a.job_id, u.email
         FROM applications a
         INNER JOIN job_profiles jp ON jp.id = a.job_id
         INNER JOIN users u ON u.id = a.candidate_id
         WHERE a.id = ? AND jp.recruiter_id = ? LIMIT 1'
    );
    $stmt->execute([$applicationId, $recruiterId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        header('Location: applications.php?error=invalid_request');
        exit;
    }

    if ($application['status'] !== 'pending') {
        header('Location: applications.php?error=invalid_request');
        exit;
    }

    // Update application status
    $stmt = $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?');
    $stmt->execute(['rejected', $applicationId]);

    // TODO: Person 4 - Call Nodemailer mail service to send rejection email
    // The email should be professional and include next steps for the candidate

    header('Location: applications.php?success=rejected');
    exit;
} catch (PDOException $e) {
    header('Location: applications.php?error=server');
    exit;
}

