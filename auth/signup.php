<?php
// Minimal signup handler so Person 2's candidate module can be tested.
// Person 1 can replace or extend this file without changing candidate pages.
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../signup.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

// Basic server-side validation stays here even if the frontend validates too.
if ($firstName === '' || $lastName === '' || $email === '' || $password === '' || !in_array($role, ['candidate', 'recruiter'], true)) {
    header('Location: ../signup.php?error=missing_fields');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../signup.php?error=invalid_email');
    exit;
}

if (strlen($password) < 6) {
    header('Location: ../signup.php?error=weak_password');
    exit;
}

try {
    // Prepared statements are used everywhere to keep user input out of SQL strings.
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        header('Location: ../signup.php?error=email_exists');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([
        $firstName,
        $lastName,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        $role,
    ]);

    // Do not auto-login here; this keeps the test auth flow simple and explicit.
    header('Location: ../signin.php?success=registered');
    exit;
} catch (PDOException $e) {
    // Keep database details out of the URL and browser.
    header('Location: ../signup.php?error=server');
    exit;
}
