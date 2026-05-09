<?php
// Frontend signin page for the minimal auth handler in auth/signin.php.
$errors = [
    'missing_fields' => 'Please enter your email and password.',
    'invalid_credentials' => 'Invalid email or password.',
    'server' => 'Something went wrong. Please try again.',
];

$successMessages = [
    'registered' => 'Account created. You can sign in now.',
];

$errorKey = $_GET['error'] ?? '';
$successKey = $_GET['success'] ?? '';

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
    <title>Sign In</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="page-shell narrow">
        <section class="panel">
            <h1>Sign In</h1>

            <?php if (isset($successMessages[$successKey])): ?>
                <div class="message success"><?php echo e($successMessages[$successKey]); ?></div>
            <?php endif; ?>

            <?php if (isset($errors[$errorKey])): ?>
                <div class="message error"><?php echo e($errors[$errorKey]); ?></div>
            <?php endif; ?>

            <!-- Candidate login must produce $_SESSION['user_id'] and $_SESSION['role']. -->
            <form class="form-card" method="POST" action="auth/signin.php">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <button class="button" type="submit">Sign In</button>
            </form>

            <p class="small-note">Need an account? <a href="signup.php">Create one</a>.</p>
        </section>
    </main>
</body>
</html>
