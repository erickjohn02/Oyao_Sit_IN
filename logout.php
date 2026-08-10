<?php
session_start();
session_unset(); // Unset all session variables
session_destroy(); // Destroy 

// Prevent users from going back to the dashboard using the back button
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login page
header("Location: login.php");
exit();
?>