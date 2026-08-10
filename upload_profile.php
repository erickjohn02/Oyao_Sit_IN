<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Retrieve form data
$idno = $_POST['idno'];
$lastname = $_POST['lastname'];
$firstname = $_POST['firstname'];
$middlename = $_POST['middlename'];
$course = $_POST['course'];
$yearlevel = $_POST['yearlevel'];
$email = $_POST['email'];
$address = $_POST['address'];

// Handle profile upload
$targetDir = "images/";
$defaultPic = "default.png";
$profilePic = $defaultPic;

if (!empty($_FILES["profile_pic"]["name"])) {
    $fileName = time() . "_" . basename($_FILES["profile_pic"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Allow only image files
    $allowedTypes = array("jpg", "jpeg", "png", "gif");
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFilePath)) {
            $profilePic = $fileName;
        } else {
            die("Error uploading file.");
        }
    } else {
        die("Invalid file format. Only JPG, JPEG, PNG, & GIF allowed.");
    }
} else {
    // Keep existing profile picture if no new file is uploaded
    $query = "SELECT profile_pic FROM users WHERE idno = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $idno);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $profilePic = !empty($row['profile_pic']) ? $row['profile_pic'] : $defaultPic;
    $stmt->close();
}

// Update user data
$sql = "UPDATE users SET lastname=?, firstname=?, middlename=?, course=?, address=?, yearlevel=?, email=?, profile_pic=? WHERE idno=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssss", $lastname, $firstname, $middlename, $course, $address, $yearlevel, $email, $profilePic, $idno);

if ($stmt->execute()) {
    header("Location: dashboard.php");
    exit();
} else {
    echo "Error updating record: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
