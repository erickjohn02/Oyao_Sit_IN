<?php
require_once 'includes/db_connect.php';

echo "<h2>Database Connection Test</h2>";
echo "Connected successfully to database: $dbname<br><br>";

// Test users table
echo "<h3>Users Table Test</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total users: " . $row['count'] . "<br>";
    
    // Check for admin user
    $result = $conn->query("SELECT * FROM users WHERE role = 'admin'");
    if ($result->num_rows > 0) {
        echo "Admin user exists<br>";
    } else {
        echo "No admin user found<br>";
    }
    
    // Check for student users
    $result = $conn->query("SELECT * FROM users WHERE role = 'student'");
    if ($result->num_rows > 0) {
        echo "Student users exist: " . $result->num_rows . " found<br>";
    } else {
        echo "No student users found<br>";
    }
} else {
    echo "Error checking users table: " . $conn->error . "<br>";
}

// Test labs table
echo "<h3>Labs Table Test</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM labs");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total labs: " . $row['count'] . "<br>";
    
    // Check for available labs
    $result = $conn->query("SELECT * FROM labs WHERE status = 'available'");
    if ($result->num_rows > 0) {
        echo "Available labs: " . $result->num_rows . "<br>";
    } else {
        echo "No available labs found<br>";
    }
} else {
    echo "Error checking labs table: " . $conn->error . "<br>";
}

// Test sit_in_records table
echo "<h3>Sit-in Records Table Test</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM sit_in_records");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total sit-in records: " . $row['count'] . "<br>";
    
    // Check for active sit-ins
    $result = $conn->query("SELECT COUNT(*) as count FROM sit_in_records WHERE time_out IS NULL");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Active sit-ins: " . $row['count'] . "<br>";
    }
} else {
    echo "Error checking sit_in_records table: " . $conn->error . "<br>";
}

// Test table structure
echo "<h3>Table Structure Test</h3>";
$tables = ['users', 'labs', 'sit_in_records'];
foreach ($tables as $table) {
    $result = $conn->query("DESCRIBE $table");
    if ($result) {
        echo "<br>Structure of $table table:<br>";
        echo "<pre>";
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "Error checking $table structure: " . $conn->error . "<br>";
    }
}
?> 