<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Clear "Remember Me" cookie if it exists
if (isset($_COOKIE['rememberme'])) {
    setcookie('rememberme', '', time() - 3600, "/", "", false, true);
}

// Redirect to login page with logout success flag
header("Location: admin-login.php?logged_out=1");
exit();
?>
