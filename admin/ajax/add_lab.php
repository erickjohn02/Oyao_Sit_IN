<?php
require_once '../includes/header.php';

if (!isset($_POST['name']) || !isset($_POST['location']) || !isset($_POST['capacity']) || !isset($_POST['status'])) {
    die(json_encode(['success' => false, 'message' => 'All fields are required']));
}

$name = sanitize_input($_POST['name']);
$location = sanitize_input($_POST['location']);
$capacity = (int)$_POST['capacity'];
$status = sanitize_input($_POST['status']);

$query = "INSERT INTO labs (name, location, capacity, status, created_at, last_updated) VALUES (?, ?, ?, ?, NOW(), NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssis", $name, $location, $capacity, $status);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error adding lab']);
}
?> 