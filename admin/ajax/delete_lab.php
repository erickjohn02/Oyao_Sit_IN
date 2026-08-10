<?php
require_once '../includes/header.php';

if (!isset($_POST['id'])) {
    die(json_encode(['success' => false, 'message' => 'Lab ID is required']));
}

$id = (int)$_POST['id'];

// Check if lab is in use
$check_query = "SELECT COUNT(*) as count FROM sit_in_records WHERE lab = (SELECT name FROM labs WHERE id = ?)";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['count'];

if ($count > 0) {
    die(json_encode(['success' => false, 'message' => 'Cannot delete lab that has sit-in records']));
}

$query = "DELETE FROM labs WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting lab']);
}
?> 