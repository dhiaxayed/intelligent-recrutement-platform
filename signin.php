<?php
session_start();

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

$errors = [
    'invalid_credentials' => 'Email ou mot de passe incorrect.',
    'missing_fields'      => 'Veuillez compléter tous les champs.',
    'server'              => 'Une erreur est survenue. Veuillez réessayer.',
];

$errorKey = $_GET['error'] ?? '';

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — RecruitPro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js" defer></script>
</head>
<body>
<div class="auth-shell">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <div class="auth-logo-icon">🧳</div>
            <h1>Bienvenue sur RecruitPro</h1>
            <p class="auth-subtitle">Connectez-vous ou créez un compte</p>
        </div>

        <!-- Tabs -->
        <div class="auth-tabs">
            <a href="signin.php" class="auth-tab active">Connexion</a>
            <a href="signup.php" class="auth-tab">Inscription</a>
        </div>

        <!-- Erreur -->
        <?php if (isset($errors[$errorKey])): ?>
            <div class="message error"><?php echo e($errors[$errorKey]); ?></div>
        <?php endif; ?>

        <!-- Formulaire -->
        <form method="POST" action="auth/signin.php">

            <div class="form-field" style="margin-bottom:14px;">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       placeholder="vous@exemple.com" required autofocus>
            </div>

            <div class="form-field" style="margin-bottom:24px;">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password"
                       placeholder="Votre mot de passe" required>
            </div>

            <button class="button full" type="submit">
                Se connecter →
            </button>

        </form>

        <div class="switch-link">
            Pas encore de compte ?
            <a href="signup.php">Créer un compte</a>
        </div>

    </div>
</div>
</body>
</html>