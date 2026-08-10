<?php
$servername = "localhost";
$username = "root";
$password = "";

try {
    // Create connection without database
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Drop existing database if exists
    $conn->query("DROP DATABASE IF EXISTS sitin_monitoring");
    echo "Dropped existing database if any...<br>";
    
    // Create new database
    $conn->query("CREATE DATABASE sitin_monitoring");
    echo "Created new database...<br>";
    
    // Select the database
    $conn->select_db("sitin_monitoring");
    echo "Selected database...<br>";
    
    // Read the SQL file
    $sql = file_get_contents('sitin_monitoring.sql');
    
    // Remove the database creation and use statements
    $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
    $sql = preg_replace('/USE.*?;/i', '', $sql);
    
    // Split the SQL file into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Execute each statement
    $success = true;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            if (!$conn->query($statement)) {
                echo "Error executing statement: " . $conn->error . "<br>";
                echo "Statement: " . $statement . "<br><br>";
                $success = false;
            }
        }
    }
    
    if ($success) {
        echo "<br>Database imported successfully!<br>";
        echo "You can now <a href='login.php'>login</a> with:<br>";
        echo "Username: admin<br>";
        echo "Password: password";
    } else {
        echo "<br>There were some errors while importing the database.";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 