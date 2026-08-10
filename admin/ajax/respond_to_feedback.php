<?php
require_once '../includes/header.php';

if (!isset($_POST['id']) || !isset($_POST['admin_response'])) {
    die(json_encode(['success' => false, 'message' => 'Feedback ID and response are required']));
}

$id = (int)$_POST['id'];
$admin_response = sanitize_input($_POST['admin_response']);

$query = "UPDATE feedback SET admin_response = ?, status = 'responded', updated_at = NOW() WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $admin_response, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating feedback']);
}
?> 