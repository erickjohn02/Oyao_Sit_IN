<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug information
    error_log("Registration attempt - POST data received");
    
    $idno = $_POST['idno'];
    $lastname = $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $course = $_POST['course'];
    $yearlevel = $_POST['yearlevel'];
    $address = $_POST['address'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Debug information
    error_log("Registration data - Username: " . $username . ", Email: " . $email);
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    error_log("Password hashed successfully");

    // Check if username, ID number, or email already exists in users table
    $checkUser = "SELECT * FROM users WHERE username = ? OR idno = ? OR email = ?";
    $stmt = $conn->prepare($checkUser);
    $stmt->bind_param("sss", $username, $idno, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        error_log("Registration failed - Username, IDNO, or Email already taken");
        echo "<script>alert('Username, IDNO, or Email already taken!'); window.location.href='registration.php';</script>";
        exit();
    }
    $stmt->close();

    // Insert into users table
    $sql = "INSERT INTO users (idno, lastname, firstname, middlename, course, yearlevel, username, email, address, password, profile_pic) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'default.png')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", $idno, $lastname, $firstname, $middlename, $course, $yearlevel, $username, $email, $address, $hashed_password);

    if ($stmt->execute()) {
        error_log("Registration successful for user: " . $username);
        echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
    } else {
        error_log("Registration failed - Database error: " . $stmt->error);
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="styleRegister.css">
</head>
<body>
    <div class="register-container">
        <div class="logo-container">
            <img src="./images/css.png" alt="CSS Logo">
            <img src="./images/uc.png" alt="UC Logo">
        </div>
        <h2>Sign up</h2>
        <form action="registration.php" method="post" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="idno">IDNO:</label>
                <input type="text" id="idno" name="idno" required>
            </div>
            <div class="form-group">
                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" required>
            </div>
            <div class="form-group">
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" required>
            </div>
            <div class="form-group">
                <label for="middlename">Middle Name:</label>
                <input type="text" id="middlename" name="middlename">
            </div>
            <div class="form-group">
                <label for="course">Course:</label>
                <select id="course" name="course" required>
                    <option value="BSCS">BSCS</option>
                    <option value="BSIT">BSIT</option>
                    <option value="BSCRIM">BSCRIM</option>
                    <option value="BSCPE">BSCPE</option>
                </select>
            </div>
            <div class="form-group">
                <label for="yearlevel">Year Level:</label>
                <select id="yearlevel" name="yearlevel" required>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address">
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <input type="submit" value="Register">
            </div>
            <!-- Back button below register button -->
            <button class="back-button" type="button" onclick="window.location.href='login.php'">Back</button>
        </form>
    </div>

    <script>
    function validateForm() {
        var password = document.getElementById('password').value;
        if (password.length < 6) {
            alert('Password must be at least 6 characters long');
            return false;
        }
        return true;
    }
    </script>
</body>
</html>
