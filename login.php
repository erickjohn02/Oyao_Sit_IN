<?php
include 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Query for both users and admins separately
    $query = "SELECT 'user' AS role, username, password FROM users WHERE username = ?
              UNION 
              SELECT 'admin' AS role, username, password FROM admins WHERE username = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Debug information
        error_log("Attempting login for user: " . $username);
        error_log("Stored password hash: " . $row['password']);
        
        // Correct password verification for hashed passwords
        if (password_verify($password, $row['password'])) {
            error_log("Password verification successful");
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; // Store role in session

            if ($row['role'] == 'admin') {
                echo "<script>alert('Admin Login successful!'); window.location.href='admin/dashboard.php';</script>";
            } else {
                echo "<script>alert('User Login successful!'); window.location.href='dashboard.php';</script>";
            }
            exit();
        } else {
            error_log("Password verification failed");
        }
    }

    // Show error message if login fails
    echo "<script>alert('Invalid login credentials!'); window.location.href='login.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="./images/css.png" alt="CSS Logo">
            <img src="./images/uc.png" alt="UC Logo">
        </div>
        
        <h2>CSS Sitin Monitoring System</h2>
        
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Login</button>
                <a href="registration.php" class="register-link">Register</a>
            </div>
        </form>
    </div>
</body>
</html>
