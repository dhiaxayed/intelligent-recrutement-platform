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

// Optional filter by job
$jobFilter = filter_input(INPUT_GET, 'job', FILTER_VALIDATE_INT);

// Build base query to fetch applications for recruiter's jobs
$baseQuery = '
    SELECT a.id, a.status, a.applied_at, jp.title as job_title, u.first_name, u.last_name, u.email, cp.phone, cp.linkedin, cp.github, cp.cv_path
    FROM applications a
    INNER JOIN job_profiles jp ON jp.id = a.job_id
    INNER JOIN users u ON u.id = a.candidate_id
    LEFT JOIN candidate_profiles cp ON cp.candidate_id = u.id
    WHERE jp.recruiter_id = ?
';

$params = [$recruiterId];

if ($jobFilter) {
    // Verify the job belongs to this recruiter before filtering
    $stmt = $pdo->prepare('SELECT id FROM job_profiles WHERE id = ? AND recruiter_id = ? LIMIT 1');
    $stmt->execute([$jobFilter, $recruiterId]);
    if ($stmt->fetch()) {
        $baseQuery .= ' AND a.job_id = ?';
        $params[] = $jobFilter;
    }
}

$baseQuery .= ' ORDER BY a.applied_at DESC';

$stmt = $pdo->prepare($baseQuery);
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch jobs list for filter dropdown
$stmt = $pdo->prepare('SELECT id, title FROM job_profiles WHERE recruiter_id = ? ORDER BY created_at DESC');
$stmt->execute([$recruiterId]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$successMessages = [
    'job_created' => 'Job posted successfully.',
    'accepted' => 'Application accepted.',
    'rejected' => 'Application rejected.',
];

$successKey = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="dashboard.php">Recruitment Platform</a>
        <nav class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="create_job.php">Post Job</a>
            <a href="../auth/logout.php">Logout</a>
        </nav>
    </header>

    <main class="page-shell wide">
        <section class="page-heading">
            <h1>Applications</h1>

            <?php if (isset($successMessages[$successKey])): ?>
                <div class="message success"><?php echo e($successMessages[$successKey]); ?></div>
            <?php endif; ?>
        </section>

        <section class="filter-section">
            <form method="GET" action="applications.php">
                <label for="job">Filter by Job:</label>
                <select id="job" name="job">
                    <option value="">All Jobs</option>
                    <?php foreach ($jobs as $job): ?>
                        <option value="<?php echo (int) $job['id']; ?>" <?php echo ($jobFilter === (int) $job['id']) ? 'selected' : ''; ?>>
                            <?php echo e($job['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="button" type="submit">Filter</button>
            </form>
        </section>

        <section class="applications-list">
            <?php if (!$applications): ?>
                <div class="panel">
                    <p>No applications yet.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($applications as $app): ?>
                <article class="application-card">
                    <div class="application-header">
                        <div>
                            <h3><?php echo e($app['first_name']); ?> <?php echo e($app['last_name']); ?></h3>
                            <p class="app-meta"><?php echo e($app['job_title']); ?></p>
                        </div>
                        <span class="status-badge <?php echo e($app['status']); ?>"><?php echo e($app['status']); ?></span>
                    </div>

                    <div class="application-details">
                        <p><strong>Email:</strong> <a href="mailto:<?php echo e($app['email']); ?>"><?php echo e($app['email']); ?></a></p>
                        <?php if ($app['phone']): ?>
                            <p><strong>Phone:</strong> <?php echo e($app['phone']); ?></p>
                        <?php endif; ?>
                        <?php if ($app['linkedin']): ?>
                            <p><strong>LinkedIn:</strong> <a href="<?php echo e($app['linkedin']); ?>" target="_blank"><?php echo e($app['linkedin']); ?></a></p>
                        <?php endif; ?>
                        <?php if ($app['github']): ?>
                            <p><strong>GitHub:</strong> <a href="<?php echo e($app['github']); ?>" target="_blank"><?php echo e($app['github']); ?></a></p>
                        <?php endif; ?>
                        <?php if ($app['cv_path']): ?>
                            <p><strong>CV:</strong> <a href="<?php echo e($app['cv_path']); ?>" download>Download</a></p>
                        <?php endif; ?>
                    </div>

                    <?php if ($app['status'] === 'pending'): ?>
                        <div class="action-row">
                            <form method="POST" action="accept.php" style="display: inline;">
                                <input type="hidden" name="application_id" value="<?php echo (int) $app['id']; ?>">
                                <button class="button" type="submit">Accept</button>
                            </form>
                            <form method="POST" action="reject.php" style="display: inline;">
                                <input type="hidden" name="application_id" value="<?php echo (int) $app['id']; ?>">
                                <button class="button secondary" type="submit">Reject</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</body>
</html>
