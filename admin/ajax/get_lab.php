<?php
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    die(json_encode(['success' => false, 'message' => 'Lab ID is required']));
}

$id = (int)$_GET['id'];
$query = "SELECT * FROM labs WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$lab = $result->fetch_assoc();

if (!$lab) {
    die(json_encode(['success' => false, 'message' => 'Lab not found']));
}

echo json_encode($lab);
?> 