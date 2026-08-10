<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "<script>alert('Access Denied!'); window.location.href='login.php';</script>";
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idno = $_POST['idno'];
    $lastname = $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $yearlevel = $_POST['yearlevel'];

    $updateQuery = "UPDATE users SET idno=?, lastname=?, firstname=?, username=?, email=?, course=?, yearlevel=? WHERE id=?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssssssi", $idno, $lastname, $firstname, $username, $email, $course, $yearlevel, $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('User updated successfully!'); window.location.href='admin.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
</head>
<body>
    <h2>Edit User</h2>
    <form method="post">
        <label>IDNO:</label>
        <input type="text" name="idno" value="<?= $user['idno'] ?>" required><br>
        <label>Last Name:</label>
        <input type="text" name="lastname" value="<?= $user['lastname'] ?>" required><br>
        <label>First Name:</label>
        <input type="text" name="firstname" value="<?= $user['firstname'] ?>" required><br>
        <label>Username:</label>
        <input type="text" name="username" value="<?= $user['username'] ?>" required><br>
        <label>Email:</label>
        <input type="email" name="email" value="<?= $user['email'] ?>" required><br>
        <label>Course:</label>
        <input type="text" name="course" value="<?= $user['course'] ?>" required><br>
        <label>Year Level:</label>
        <input type="text" name="yearlevel" value="<?= $user['yearlevel'] ?>" required><br>
        <button type="submit">Update</button>
        <a href="admin.php">Cancel</a>
    </form>
</body>
</html>
