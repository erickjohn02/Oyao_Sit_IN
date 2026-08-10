<?php
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    die(json_encode(['success' => false, 'message' => 'Feedback ID is required']));
}

$id = (int)$_GET['id'];
$query = "SELECT f.*, u.firstname, u.lastname, u.course 
          FROM feedback f 
          JOIN users u ON f.user_id = u.id 
          WHERE f.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$feedback = $result->fetch_assoc();

if (!$feedback) {
    die(json_encode(['success' => false, 'message' => 'Feedback not found']));
}

echo json_encode($feedback);
?> 