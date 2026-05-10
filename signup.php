<?php
session_start();

// If a user is already logged in, keep them out of the auth pages.
if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'candidate') {
        header('Location: candidate/dashboard.php');
        exit;
    }

    if (($_SESSION['role'] ?? '') === 'recruiter') {
        header('Location: recruiter/dashboard.php');
        exit;
    }
}

// Frontend signup page for the minimal auth handler in auth/signup.php.
$errors = [
    'missing_fields' => 'Please complete all fields.',
    'invalid_email' => 'Please enter a valid email address.',
    'weak_password' => 'Password must be at least 6 characters.',
    'email_exists' => 'An account already exists for that email.',
    'server' => 'Something went wrong. Please try again.',
];

$errorKey = $_GET['error'] ?? '';

function e(string $value): string
{
    // Escape query-string driven messages before printing.
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="page-shell narrow">
        <section class="panel">
            <h1>Create Account</h1>

            <?php if (isset($errors[$errorKey])): ?>
                <div class="message error"><?php echo e($errors[$errorKey]); ?></div>
            <?php endif; ?>

            <!-- This posts to the temporary auth handler; Person 1 can keep the same field names. -->
            <form class="form-card" method="POST" action="auth/signup.php">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required>

                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" minlength="6" required>

                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="candidate">Candidate</option>
                    <option value="recruiter">Recruiter</option>
                </select>

                <button class="button" type="submit">Create Account</button>
            </form>

            <p class="small-note">Already have an account? <a href="signin.php">Sign in</a>.</p>
        </section>
    </main>
</body>
</html>
