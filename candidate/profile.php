<?php
session_start();

// Candidate-only page. Person 1's auth only needs to set user_id and role.
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'candidate') {
    header('Location: ../signin.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

function e(string $value): string
{
    // Escape everything printed back into HTML.
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$candidateId = (int) $_SESSION['user_id'];

// Name and email remain owned by the users table; this page only edits profile details.
$stmt = $pdo->prepare('SELECT first_name, last_name, email FROM users WHERE id = ? AND role = ? LIMIT 1');
$stmt->execute([$candidateId, 'candidate']);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    // Session is stale or user role changed; logout avoids showing another user's data.
    header('Location: ../auth/logout.php');
    exit;
}

// Existing profile data is optional because first-time candidates land here before uploading a CV.
$stmt = $pdo->prepare('SELECT phone, linkedin, github, cv_path FROM candidate_profiles WHERE candidate_id = ? LIMIT 1');
$stmt->execute([$candidateId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

// Query-string messages keep this module simple and avoid depending on a shared flash system.
$errors = [
    'missing_fields' => 'Phone and LinkedIn are required.',
    'invalid_url' => 'Please enter valid LinkedIn and GitHub URLs.',
    'missing_cv' => 'Please upload your CV as a PDF.',
    'invalid_cv' => 'Your CV must be a valid PDF file.',
    'cv_too_large' => 'Your CV must not exceed 5 MB.',
    'upload_failed' => 'The CV upload failed. Please try again.',
    'server' => 'Something went wrong. Please try again.',
];

$errorKey = $_GET['error'] ?? '';
$hasCv = $profile && !empty($profile['cv_path']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Profile</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/main.js" defer></script>
</head>
<body>
    <header class="site-header">
    <a class="brand" href="dashboard.php">🏢 RecruitPro</a>
    <nav class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="jobs.php">Jobs</a>
        <a href="../auth/logout.php" class="logout-link">Logout</a>
    </nav>
</header>

    <main class="page-shell narrow">
        <section class="panel">
            <h1>Candidate Profile</h1>

            <?php if (($_GET['success'] ?? '') === '1'): ?>
                <div class="message success">Profile saved successfully.</div>
            <?php endif; ?>

            <?php if (isset($errors[$errorKey])): ?>
                <div class="message error"><?php echo e($errors[$errorKey]); ?></div>
            <?php endif; ?>

            <form class="form-card" method="POST" enctype="multipart/form-data" action="save_profile.php" data-profile-form data-has-cv="<?php echo $hasCv ? '1' : '0'; ?>" novalidate>
                <div class="client-errors" data-client-errors aria-live="polite"></div>

                <!-- Readonly fields come from users; save_profile.php intentionally does not update them. -->
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" value="<?php echo e($candidate['first_name']); ?>" readonly>

                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" value="<?php echo e($candidate['last_name']); ?>" readonly>

                <label for="email">Email</label>
                <input type="email" id="email" value="<?php echo e($candidate['email']); ?>" readonly>

                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo e($profile['phone'] ?? ''); ?>" required>

                <label for="linkedin">LinkedIn URL</label>
                <input type="url" id="linkedin" name="linkedin" value="<?php echo e($profile['linkedin'] ?? ''); ?>" required>

                <label for="github">GitHub URL</label>
                <input type="url" id="github" name="github" value="<?php echo e($profile['github'] ?? ''); ?>">

                <label for="cv">CV PDF</label>
                <input type="file" id="cv" name="cv" accept="application/pdf,.pdf">

                <?php if ($hasCv): ?>
                    <p class="small-note">
                        <!-- Stored path is relative to the project root, so profile.php links one level up. -->
                        <a href="../<?php echo e($profile['cv_path']); ?>" target="_blank" rel="noopener">View current CV</a>
                    </p>
                <?php else: ?>
                    <p class="small-note">Upload a PDF CV before applying to jobs.</p>
                <?php endif; ?>

                <button type="submit" class="button">Save Profile</button>
            </form>
        </section>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
