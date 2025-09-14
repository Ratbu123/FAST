<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Clear "Remember Me" cookie if it exists
if (isset($_COOKIE['rememberme'])) {
    setcookie('rememberme', '', time() - 3600, "/");
}

// Redirect to login page
header("Location: login.php");
exit();
?>
