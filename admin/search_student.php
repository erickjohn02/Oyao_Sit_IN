<?php
require_once 'includes/db_connect.php';

// Check if it's an AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search = sanitize_input($_POST['search']);
    
    // Search in users table
    $query = "SELECT idno, CONCAT(firstname, ' ', lastname) as name, remaining_sessions 
              FROM users 
              WHERE idno LIKE ? OR CONCAT(firstname, ' ', lastname) LIKE ?
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $searchTerm = "%$search%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($student = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'student' => $student
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Student not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
} 