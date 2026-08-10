<?php
include 'db_connect.php';

// Test registration data
$test_user = [
    'idno' => 'TEST123',
    'lastname' => 'Test',
    'firstname' => 'User',
    'middlename' => 'Middle',
    'course' => 'BSIT',
    'yearlevel' => '1st Year',
    'address' => 'Test Address',
    'username' => 'testuser',
    'email' => 'test@example.com',
    'password' => 'testpass123'
];

// Check if test user already exists
$check_query = "SELECT * FROM users WHERE username = ? OR idno = ? OR email = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("sss", $test_user['username'], $test_user['idno'], $test_user['email']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Test user already exists. Deleting...<br>";
    $delete_query = "DELETE FROM users WHERE username = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("s", $test_user['username']);
    $stmt->execute();
}

// Hash the password
$hashed_password = password_hash($test_user['password'], PASSWORD_DEFAULT);

// Insert test user
$insert_query = "INSERT INTO users (idno, lastname, firstname, middlename, course, yearlevel, username, email, address, password, profile_pic) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'default.png')";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("ssssssssss", 
    $test_user['idno'],
    $test_user['lastname'],
    $test_user['firstname'],
    $test_user['middlename'],
    $test_user['course'],
    $test_user['yearlevel'],
    $test_user['username'],
    $test_user['email'],
    $test_user['address'],
    $hashed_password
);

if ($stmt->execute()) {
    echo "Test user created successfully!<br>";
    
    // Verify the user was created
    $verify_query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("s", $test_user['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    echo "<br>Verifying user details:<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Password hash: " . $user['password'] . "<br>";
    
    // Test password verification
    if (password_verify($test_user['password'], $user['password'])) {
        echo "<br>Password verification successful!<br>";
        echo "You can now login with:<br>";
        echo "Username: " . $test_user['username'] . "<br>";
        echo "Password: " . $test_user['password'] . "<br>";
    } else {
        echo "<br>Password verification failed!<br>";
    }
} else {
    echo "Error creating test user: " . $stmt->error;
}

// Display all users for debugging
echo "<br><br>All users in database:<br>";
$result = $conn->query("SELECT username, password FROM users");
while ($row = $result->fetch_assoc()) {
    echo "Username: " . $row['username'] . "<br>";
    echo "Password hash: " . $row['password'] . "<br><br>";
}
?> 