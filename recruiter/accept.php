<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../phpmailer/src/Exception.php';
require __DIR__ . '/../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/src/SMTP.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'recruiter') {
    header('Location: ../signin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: applications.php?error=invalid_request');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$recruiterId   = (int) $_SESSION['user_id'];
$applicationId = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);

if (!$applicationId) {
    header('Location: applications.php?error=invalid_request');
    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT a.id, a.status, a.candidate_id, a.job_id,
                u.email, u.first_name, u.last_name,
                jp.title AS job_title,
                ru.first_name AS recruiter_first, ru.last_name AS recruiter_last
         FROM applications a
         INNER JOIN job_profiles jp ON jp.id      = a.job_id
         INNER JOIN users        u  ON u.id        = a.candidate_id
         INNER JOIN users        ru ON ru.id       = jp.recruiter_id
         WHERE a.id = ? AND jp.recruiter_id = ?
         LIMIT 1'
    );
    $stmt->execute([$applicationId, $recruiterId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        header('Location: applications.php?error=invalid_request');
        exit;
    }

    if ($application['status'] !== 'pending') {
        header('Location: applications.php?error=already_processed');
        exit;
    }

    $update = $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?');
    $update->execute(['accepted', $applicationId]);

    // ── Person 4 : envoi email via Nodemailer ────────────────────────
$candidateName  = $application['first_name'] . ' ' . $application['last_name'];
$candidateEmail = $application['email'];
$jobTitle       = $application['job_title'];
$meetLink       = 'https://meet.google.com/abc-defg-hij';

$payload = json_encode([
    'candidateEmail' => $candidateEmail,
    'candidateName'  => $candidateName,
    'jobTitle'       => $jobTitle,
    'meetLink'       => $meetLink
]);

$ch = curl_init('http://localhost:3000/send-acceptance-email');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_exec($ch);
curl_close($ch);
// ── Fin Person 4 ─────────────────────────────────────────────────

    header('Location: applications.php?success=accepted');
    exit;

} catch (PDOException $e) {
    header('Location: applications.php?error=server');
    exit;
}