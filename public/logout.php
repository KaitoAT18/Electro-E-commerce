<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (isLoggedIn()) {
    // Get user ID
    $userId = getUserIdFromJwt();

    // Delete the remember token from the database
    if (isset($_COOKIE['remember_token'])) {
        $rememberToken = $_COOKIE['remember_token'];
        $sql = "UPDATE users SET remember_token = NULL, remember_expires = NULL WHERE user_id = ?";
        updateRecord($sql, [$userId]);

        // Delete the remember token cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }

    // Delete the JWT token from the database
    setcookie('jwt', '', time() - 3600, '/', '', false, true);

    // Successfully logged out
    setFlashData('msg', 'You have successfully logged out.');
    setFlashData('msg_type', 'success');
} else {
    // User is not logged in
    setFlashData('msg', 'You are not logged in.');
    setFlashData('msg_type', 'info');
}

// Redirect to the login page
header('Location: ' . BASE_URL . '/public/login.php');
exit();
?>