<?php
// ================================
// Database connection - F.A.S.T System
// ================================

// Database credentials
$host = "localhost";
$db   = "fast_system";
$user = "root";   // Change if your DB username is different
$pass = "";       // Change if your DB password is set

// Create connection using mysqli with error handling
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error); // Log error
    die("We are experiencing technical difficulties. Please try again later."); // Hide details from user
}

// Set charset to UTF-8 (for security & compatibility)
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $conn->error);
    die("Database error.");
}
?>
