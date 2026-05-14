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
    'missing_fields' => 'Veuillez compléter tous les champs.',
    'invalid_email'  => 'Veuillez entrer une adresse email valide.',
    'weak_password'  => 'Le mot de passe doit contenir au moins 6 caractères.',
    'email_exists'   => 'Un compte existe déjà pour cet email.',
    'server'         => 'Une erreur est survenue. Veuillez réessayer.',
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
    <title>Inscription — RecruitPro</title>
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
            <a href="signin.php" class="auth-tab">Connexion</a>
            <a href="signup.php" class="auth-tab active">Inscription</a>
        </div>

        <!-- Erreur -->
        <?php if (isset($errors[$errorKey])): ?>
            <div class="message error"><?php echo e($errors[$errorKey]); ?></div>
        <?php endif; ?>

        <!-- Role selector -->
        <p style="font-size:13px; font-weight:600; color:var(--navy); margin-bottom:10px;">
            Je suis...
        </p>
        <div class="role-selector" style="margin-bottom:20px;">
            <div class="role-option active" id="role-candidate" onclick="selectRole('candidate')">
                <span class="role-option-icon">👤</span>
                Candidat
            </div>
            <div class="role-option" id="role-recruiter" onclick="selectRole('recruiter')">
                <span class="role-option-icon">🧳</span>
                Recruteur
            </div>
        </div>

        <!-- Formulaire -->
        <form method="POST" action="auth/signup.php" id="signup-form">
            <input type="hidden" name="role" id="role-input" value="candidate">

            <div class="form-row two-col">
                <div class="form-field">
                    <label for="first_name">Prénom</label>
                    <input type="text" id="first_name" name="first_name"
                           placeholder="Jean" required>
                </div>
                <div class="form-field">
                    <label for="last_name">Nom</label>
                    <input type="text" id="last_name" name="last_name"
                           placeholder="Dupont" required>
                </div>
            </div>

            <div class="form-field" style="margin-bottom:14px;">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       placeholder="vous@exemple.com" required>
            </div>

            <div class="form-field" style="margin-bottom:22px;">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password"
                       placeholder="6 caractères minimum" minlength="6" required>
            </div>

            <button class="button full" type="submit">
                Créer mon compte →
            </button>
        </form>

        <div class="switch-link">
            Déjà un compte ?
            <a href="signin.php">Se connecter</a>
        </div>

    </div>
</div>

<script>
function selectRole(role) {
    document.getElementById('role-input').value = role;
    document.getElementById('role-candidate').classList.toggle('active', role === 'candidate');
    document.getElementById('role-recruiter').classList.toggle('active', role === 'recruiter');
}
</script>
</body>
</html>