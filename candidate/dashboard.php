<?php
session_start();

// Repeat this guard on every candidate endpoint. Do not rely on navigation hiding links.
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'candidate') {
    header('Location: ../signin.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

function e(string $value): string
{
    // Small local escape helper for safe HTML output.
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$candidateId = (int) $_SESSION['user_id'];

// Fetch the candidate from users so the dashboard reflects the authentication source of truth.
$stmt = $pdo->prepare('SELECT first_name FROM users WHERE id = ? AND role = ? LIMIT 1');
$stmt->execute([$candidateId, 'candidate']);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    // If the session points to a deleted or changed user, force a clean login.
    header('Location: ../auth/logout.php');
    exit;
}

// Profile completion controls whether the candidate can apply to jobs.
$stmt = $pdo->prepare('SELECT id FROM candidate_profiles WHERE candidate_id = ? LIMIT 1');
$stmt->execute([$candidateId]);
$profileExists = (bool) $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/main.js" defer></script>
</head>
<body>
    <header class="site-header">
    <a class="brand" href="dashboard.php">🏢 RecruitPro</a>
    <nav class="nav-links">
        <a href="profile.php">Profile</a>
        <a href="jobs.php">Jobs</a>
        <a href="../auth/logout.php" class="logout-link">Logout</a>
    </nav>
</header>

    <main class="page-shell">
        <section class="panel">
            <h1>Candidate Dashboard</h1>
            <p>Welcome, <?php echo e($candidate['first_name']); ?>.</p>

            <?php if ($profileExists): ?>
                <div class="message success">Your profile is complete. You can apply to jobs.</div>
            <?php else: ?>
                <div class="message warning">Please complete your profile and upload your CV before applying to jobs.</div>
            <?php endif; ?>

            <div class="action-row">
                <a class="button" href="profile.php">Manage Profile</a>
                <a class="button secondary" href="jobs.php">View Jobs</a>
            </div>
        </section>
    </main>
</body>
</html>
