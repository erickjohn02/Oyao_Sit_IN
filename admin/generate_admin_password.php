<?php
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "Original password: " . $password . "\n";
echo "Hashed password: " . $hashed_password . "\n";

// Test the hash
if (password_verify($password, $hashed_password)) {
    echo "Password verification successful!\n";
} else {
    echo "Password verification failed!\n";
}
?> 