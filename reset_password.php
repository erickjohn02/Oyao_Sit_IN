<?php
include 'db_connect.php';

// Set new password
$username = 'ej';
$new_password = 'password123'; // You can change this to your desired password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update the password in the database
$query = "UPDATE users SET password = ? WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $hashed_password, $username);

if ($stmt->execute()) {
    echo "Password has been reset successfully!<br>";
    echo "New password hash: " . $hashed_password . "<br>";
    echo "You can now login with:<br>";
    echo "Username: " . $username . "<br>";
    echo "Password: " . $new_password . "<br>";
} else {
    echo "Error resetting password: " . $stmt->error;
}
?> 