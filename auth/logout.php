<?php
/**
 * TasteBook – Digital Recipe Book
 * User Logout Page (auth/logout.php)
 * 
 * ICT 2209 - Web Technologies Mini Project
 * Rajarata University of Sri Lanka
 */

require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash_message('error', 'Your session expired. Refresh the page and try again.');
    header('Location: ../index.php');
    exit();
}

// Unset all session variables
$_SESSION = [];

// Delete session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Start fresh temporary session to deliver logged-out flash message
session_start();
set_flash_message('info', 'You have been successfully logged out. See you again soon!');

// Redirect to home page
header("Location: ../index.php");
exit();
?>
