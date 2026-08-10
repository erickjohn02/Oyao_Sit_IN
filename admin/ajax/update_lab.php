<?php
require_once '../includes/header.php';

if (!isset($_POST['id']) || !isset($_POST['name']) || !isset($_POST['location']) || !isset($_POST['capacity']) || !isset($_POST['status'])) {
    die(json_encode(['success' => false, 'message' => 'All fields are required']));
}

$id = (int)$_POST['id'];
$name = sanitize_input($_POST['name']);
$location = sanitize_input($_POST['location']);
$capacity = (int)$_POST['capacity'];
$status = sanitize_input($_POST['status']);

$query = "UPDATE labs SET name = ?, location = ?, capacity = ?, status = ?, last_updated = NOW() WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssisi", $name, $location, $capacity, $status, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating lab']);
}
?> 