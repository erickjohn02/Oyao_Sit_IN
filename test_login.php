<?php
include 'db_connect.php';

// Test user credentials
$test_username = 'ej';
$test_password = 'password123'; // Replace with the actual password you're using

// Query for the user
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $test_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "User found in database.<br>";
    echo "Stored password hash: " . $user['password'] . "<br>";
    
    // Test password verification
    if (password_verify($test_password, $user['password'])) {
        echo "Password verification successful!<br>";
    } else {
        echo "Password verification failed!<br>";
        echo "This means either:<br>";
        echo "1. The password you entered is incorrect<br>";
        echo "2. The stored password hash is not in the correct format<br>";
    }
} else {
    echo "User not found in database.<br>";
}

// Display all users for debugging
echo "<br>All users in database:<br>";
$result = $conn->query("SELECT username, password FROM users");
while ($row = $result->fetch_assoc()) {
    echo "Username: " . $row['username'] . "<br>";
    echo "Password hash: " . $row['password'] . "<br><br>";
}
?> 