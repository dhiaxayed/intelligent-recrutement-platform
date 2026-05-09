<?php
session_start();

// Recruiter-only page.
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'recruiter') {
    header('Location: ../signin.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$recruiterId = (int) $_SESSION['user_id'];

// Fetch recruiter name
$stmt = $pdo->prepare('SELECT first_name FROM users WHERE id = ? AND role = ? LIMIT 1');
$stmt->execute([$recruiterId, 'recruiter']);
$recruiter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recruiter) {
    header('Location: ../auth/logout.php');
    exit;
}

// Fetch all recruiter's job offers
$stmt = $pdo->prepare('SELECT id, title, company, location, created_at FROM job_profiles WHERE recruiter_id = ? ORDER BY created_at DESC');
$stmt->execute([$recruiterId]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending applications count
$stmt = $pdo->prepare(
    'SELECT COUNT(*) as count FROM applications a
     INNER JOIN job_profiles jp ON jp.id = a.job_id
     WHERE jp.recruiter_id = ? AND a.status = ?'
);
$stmt->execute([$recruiterId, 'pending']);
$pendingCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruiter Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="dashboard.php">Recruitment Platform</a>
        <nav class="nav-links">
            <a href="create_job.php">Post Job</a>
            <a href="applications.php">Applications</a>
            <a href="../auth/logout.php">Logout</a>
        </nav>
    </header>

    <main class="page-shell">
        <section class="panel">
            <h1>Recruiter Dashboard</h1>
            <p>Welcome, <?php echo e($recruiter['first_name']); ?>.</p>

            <div class="action-row">
                <a class="button" href="create_job.php">Post New Job</a>
                <a class="button secondary" href="applications.php">View Applications (<?php echo (int) $pendingCount; ?> pending)</a>
            </div>
        </section>

        <section class="jobs-list">
            <h2>Your Job Offers</h2>
            <?php if (!$jobs): ?>
                <div class="panel">
                    <p>You haven't posted any jobs yet. <a href="create_job.php">Post your first job</a>.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($jobs as $job): ?>
                <article class="job-card">
                    <div class="job-card-header">
                        <div>
                            <h3><?php echo e($job['title']); ?></h3>
                            <p class="job-meta"><?php echo e($job['company']); ?> - <?php echo e($job['location']); ?></p>
                        </div>
                        <p class="small-note">Posted on <?php echo e(date('M j, Y', strtotime($job['created_at']))); ?></p>
                    </div>
                    <div class="action-row">
                        <a class="button secondary" href="applications.php?job=<?php echo (int) $job['id']; ?>">View Applications</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</body>
</html>
