<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Clear "Remember Me" cookie if it exists
if (isset($_COOKIE['rememberme'])) {
<<<<<<< HEAD
    setcookie('rememberme', '', time() - 3600, "/", "", false, true);
=======
    setcookie('rememberme', '', time() - 3600, "/");
>>>>>>> 422a8f9085c217362d8c2725850f61b0e001d3ee
}

// Redirect to login page
header("Location: login.php");
exit();
?>
