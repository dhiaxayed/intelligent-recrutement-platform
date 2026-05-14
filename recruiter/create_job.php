<?php
session_start();

// Recruiter-only page.
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'recruiter') {
    header('Location: ../signin.php');
    exit;
}

$errors = [
    'missing_fields' => 'Please complete all required fields.',
    'invalid_input' => 'Please check your input and try again.',
    'server' => 'Something went wrong. Please try again.',
];

$errorKey = $_GET['error'] ?? '';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Job</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/main.js" defer></script>
</head>
<body>
<header class="site-header">
    <a class="brand" href="dashboard.php">🏢 RecruitPro</a>
    <nav class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="applications.php">Applications</a>
        <a href="../auth/logout.php" class="logout-link">Logout</a>
    </nav>
</header>

    <main class="page-shell narrow">
        <section class="panel">
            <h1>Post a New Job</h1>

            <?php if (isset($errors[$errorKey])): ?>
                <div class="message error"><?php echo e($errors[$errorKey]); ?></div>
            <?php endif; ?>

            <form class="form-card" method="POST" action="save_job.php">
                <label for="title">Job Title *</label>
                <input type="text" id="title" name="title" required>

                <label for="company">Company Name *</label>
                <input type="text" id="company" name="company" required>

                <label for="location">Location *</label>
                <input type="text" id="location" name="location" required>

                <label for="description">Job Description *</label>
                <textarea id="description" name="description" rows="6" required></textarea>

                <label for="requirements">Requirements *</label>
                <textarea id="requirements" name="requirements" rows="6" required></textarea>

                <button class="button" type="submit">Post Job</button>
            </form>

            <p class="small-note"><a href="dashboard.php">Back to Dashboard</a></p>
        </section>
    </main>
</body>
</html>
