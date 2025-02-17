<?php
// Set session cookie to expire when the browser closes.
session_set_cookie_params(0);

// Start the session (make sure this is the very first output)
session_start();

// Set timeout duration (e.g., 30 minutes)
$timeout_duration = 1800;

// Check if the user is logged in; if not, redirect to login page.
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Enforce inactivity timeout.
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();
?>
