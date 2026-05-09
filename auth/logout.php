<?php
// Shared logout endpoint for all roles.
session_start();

// Clear session data before destroying the session itself.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    // Expire the session cookie so the browser also drops the old session ID.
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();

header('Location: ../signin.php');
exit;
