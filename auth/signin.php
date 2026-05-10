<?php
// Minimal signin handler expected by the candidate module.
// It sets the two session keys Person 2 relies on: user_id and role.
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../signin.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: ../signin.php?error=missing_fields');
    exit;
}

try {
    // Load only the fields required to authenticate and route the user.
    $stmt = $pdo->prepare('SELECT id, password, role FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        header('Location: ../signin.php?error=invalid_credentials');
        exit;
    }

    // Candidate pages enforce role again, so this redirect is only a convenience.
    if ($user['role'] === 'candidate') {
        // Regenerate the session ID after login to reduce session fixation risk.
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role'] = $user['role'];

        header('Location: ../candidate/dashboard.php');
        exit;
    }

    if ($user['role'] === 'recruiter') {
        // Regenerate the session ID after login to reduce session fixation risk.
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role'] = $user['role'];

        header('Location: ../recruiter/dashboard.php');
        exit;
    }

    header('Location: ../signin.php?error=invalid_credentials');
    exit;
} catch (PDOException $e) {
    // Do not echo database errors in authentication screens.
    header('Location: ../signin.php?error=server');
    exit;
}
