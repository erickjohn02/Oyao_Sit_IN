<?php
require_once 'includes/db_connect.php';

echo "<h2>Admin Page Functionality Test</h2>";

// Test 1: Check if admin user exists and can log in
echo "<h3>Test 1: Admin Authentication</h3>";
$result = $conn->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "Admin user found:<br>";
    echo "Username: " . htmlspecialchars($admin['username']) . "<br>";
    echo "Role: " . htmlspecialchars($admin['role']) . "<br><br>";
} else {
    echo "No admin user found. Please create an admin user first.<br><br>";
}

// Test 2: Check if there are any student users
echo "<h3>Test 2: Student Users</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total student users: " . $row['count'] . "<br>";
    if ($row['count'] > 0) {
        echo "Sample student data:<br>";
        $result = $conn->query("SELECT id, idno, firstname, lastname, course, remaining_sessions 
                               FROM users 
                               WHERE role = 'student' 
                               LIMIT 1");
        if ($student = $result->fetch_assoc()) {
            echo "ID: " . $student['id'] . "<br>";
            echo "ID Number: " . htmlspecialchars($student['idno']) . "<br>";
            echo "Name: " . htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) . "<br>";
            echo "Course: " . htmlspecialchars($student['course']) . "<br>";
            echo "Remaining Sessions: " . $student['remaining_sessions'] . "<br><br>";
        }
    } else {
        echo "No student users found. Please add some student users.<br><br>";
    }
}

// Test 3: Check if there are any available labs
echo "<h3>Test 3: Available Labs</h3>";
$result = $conn->query("SELECT * FROM labs WHERE status = 'available'");
if ($result && $result->num_rows > 0) {
    echo "Available labs found:<br>";
    while ($lab = $result->fetch_assoc()) {
        echo "Lab: " . htmlspecialchars($lab['name']) . "<br>";
        echo "Location: " . htmlspecialchars($lab['location']) . "<br>";
        echo "Capacity: " . $lab['capacity'] . "<br><br>";
    }
} else {
    echo "No available labs found. Please add some labs.<br><br>";
}

// Test 4: Check active sit-ins
echo "<h3>Test 4: Active Sit-ins</h3>";
$result = $conn->query("SELECT s.*, u.idno, u.firstname, u.lastname, u.course 
                       FROM sit_in_records s 
                       JOIN users u ON s.user_id = u.id 
                       WHERE s.time_out IS NULL");
if ($result && $result->num_rows > 0) {
    echo "Active sit-ins found:<br>";
    while ($sit_in = $result->fetch_assoc()) {
        echo "Student: " . htmlspecialchars($sit_in['firstname'] . ' ' . $sit_in['lastname']) . "<br>";
        echo "Date: " . $sit_in['date'] . "<br>";
        echo "Time In: " . $sit_in['time_in'] . "<br>";
        echo "Lab: " . htmlspecialchars($sit_in['lab']) . "<br><br>";
    }
} else {
    echo "No active sit-ins found.<br><br>";
}

// Test 5: Verify table relationships
echo "<h3>Test 5: Table Relationships</h3>";
$result = $conn->query("SELECT COUNT(*) as count 
                       FROM sit_in_records s 
                       LEFT JOIN users u ON s.user_id = u.id 
                       WHERE u.id IS NULL");
if ($result) {
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        echo "Warning: Found " . $row['count'] . " sit-in records with invalid user references.<br><br>";
    } else {
        echo "All sit-in records have valid user references.<br><br>";
    }
}

// Test 6: Check remaining sessions
echo "<h3>Test 6: Student Sessions</h3>";
$result = $conn->query("SELECT idno, firstname, lastname, remaining_sessions 
                       FROM users 
                       WHERE role = 'student' 
                       ORDER BY remaining_sessions DESC 
                       LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "Top 5 students by remaining sessions:<br>";
    while ($student = $result->fetch_assoc()) {
        echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) . 
             " (" . htmlspecialchars($student['idno']) . "): " . 
             $student['remaining_sessions'] . " sessions<br>";
    }
} else {
    echo "No student data found.<br><br>";
}
?> 