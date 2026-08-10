<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
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
    
    echo "Connected to MySQL successfully.<br>";
    
    // Read SQL file
    $sql = file_get_contents(__DIR__ . '/database_setup.sql');
    if ($sql === false) {
        throw new Exception("Failed to read SQL file");
    }
    
    echo "SQL file read successfully.<br>";
    
    // Split SQL file into individual queries
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    // Execute each query
    foreach ($queries as $query) {
        if (!empty($query)) {
            echo "Executing query: " . substr($query, 0, 50) . "...<br>";
            if (!$conn->query($query)) {
                throw new Exception("Error executing query: " . $conn->error . "\nQuery: " . $query);
            }
        }
    }
    
    echo "<br>Database setup completed successfully!<br>";
    
    // Verify database creation
    $result = $conn->query("SHOW DATABASES LIKE 'ccs_lab_management'");
    if ($result->num_rows > 0) {
        echo "Database 'ccs_lab_management' exists.<br>";
        
        // Verify tables
        $conn->select_db('ccs_lab_management');
        $tables = ['users', 'admins', 'sit_in_records', 'reservations', 'feedback', 'labs'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                echo "Table '$table' exists.<br>";
            } else {
                echo "Warning: Table '$table' was not created.<br>";
            }
        }
    } else {
        echo "Warning: Database 'ccs_lab_management' was not created.<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 