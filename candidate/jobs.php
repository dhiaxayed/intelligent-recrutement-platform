<?php
session_start();

// Jobs are visible only to authenticated candidates.
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'candidate') {
    header('Location: ../signin.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

function e(string $value): string
{
    // Always escape database content before rendering it in the page.
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function textBlock(?string $value): string
{
    // Preserve line breaks from job descriptions while still escaping user-entered text.
    $value = trim((string) $value);
    return $value === '' ? 'Not specified.' : nl2br(e($value));
}

$candidateId = (int) $_SESSION['user_id'];

// A candidate can browse jobs without a profile, but cannot apply until this row exists.
$stmt = $pdo->prepare('SELECT id FROM candidate_profiles WHERE candidate_id = ? LIMIT 1');
$stmt->execute([$candidateId]);
$profileExists = (bool) $stmt->fetch();

// LEFT JOIN pulls the candidate's own application status without hiding jobs they have not applied to.
$stmt = $pdo->prepare(
    'SELECT jp.id, jp.title, jp.company, jp.location, jp.description, jp.requirements, jp.created_at, a.status
     FROM job_profiles jp
     LEFT JOIN applications a ON a.job_id = jp.id AND a.candidate_id = ?
     ORDER BY jp.created_at DESC'
);
$stmt->execute([$candidateId]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$successMessages = [
    'applied' => 'Application submitted successfully.',
];

$errorMessages = [
    'invalid_request' => 'Invalid application request.',
    'missing_profile' => 'Complete your profile and upload your CV before applying.',
    'invalid_job' => 'The selected job does not exist.',
    'already_applied' => 'You have already applied to this job.',
    'server' => 'Something went wrong. Please try again.',
];

$successKey = $_GET['success'] ?? '';
$errorKey = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Jobs</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/main.js" defer></script>
</head>
<body>
  <header class="site-header">
    <a class="brand" href="dashboard.php">🏢 RecruitPro</a>
    <nav class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="../auth/logout.php" class="logout-link">Logout</a>
    </nav>
</header>

    <main class="page-shell wide">
        <section class="page-heading">
            <h1>Available Jobs</h1>
            <?php if (!$profileExists): ?>
                <div class="message warning">Complete your profile and upload your CV before applying.</div>
            <?php endif; ?>

            <?php if (isset($successMessages[$successKey])): ?>
                <div class="message success"><?php echo e($successMessages[$successKey]); ?></div>
            <?php endif; ?>

            <?php if (isset($errorMessages[$errorKey])): ?>
                <div class="message error"><?php echo e($errorMessages[$errorKey]); ?></div>
            <?php endif; ?>
        </section>

        <section class="job-grid">
            <?php if (!$jobs): ?>
                <div class="panel">
                    <p>No jobs have been posted yet.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($jobs as $job): ?>
                <article class="job-card">
                    <div class="job-card-header">
                        <div>
                            <h2><?php echo e($job['title']); ?></h2>
                            <p class="job-meta"><?php echo e($job['company']); ?> - <?php echo e($job['location']); ?></p>
                        </div>
                        <?php if ($job['status']): ?>
                            <span class="status-badge <?php echo e($job['status']); ?>"><?php echo e($job['status']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="job-section">
                        <h3>Description</h3>
                        <p><?php echo textBlock($job['description']); ?></p>
                    </div>

                    <div class="job-section">
                        <h3>Requirements</h3>
                        <p><?php echo textBlock($job['requirements']); ?></p>
                    </div>

                    <p class="small-note">Posted on <?php echo e(date('M j, Y', strtotime($job['created_at']))); ?></p>

                    <?php if (!$job['status']): ?>
                        <!-- Apply must stay POST-only because it creates a database record. -->
                        <form method="POST" action="apply.php" data-apply-form>
                            <input type="hidden" name="job_id" value="<?php echo (int) $job['id']; ?>">
                            <button class="button" type="submit" <?php echo $profileExists ? '' : 'disabled'; ?>>Apply</button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
