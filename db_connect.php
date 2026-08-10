<?php
$servername = "localhost"; // Change if needed
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password is empty
$dbname = "sitin_monitoring"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set character set to UTF-8 (prevents encoding issues)
$conn->set_charset("utf8");

// Optional: Suppress warnings (for production)
error_reporting(E_ALL & ~E_WARNING);
?>
