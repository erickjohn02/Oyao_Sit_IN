<?php
require_once '../includes/header.php';

if (!isset($_POST['id'])) {
    die(json_encode(['success' => false, 'message' => 'Feedback ID is required']));
}

$id = (int)$_POST['id'];

$query = "UPDATE feedback SET status = 'resolved', updated_at = NOW() WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error resolving feedback']);
}
?> 