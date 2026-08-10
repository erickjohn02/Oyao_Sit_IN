<?php
require_once 'includes/db_connect.php';

// Read the SQL file
$sql = file_get_contents('update_database.sql');

// Split the SQL file into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

// Execute each statement
$success = true;
foreach ($statements as $statement) {
    if (!empty($statement)) {
        if (!$conn->query($statement)) {
            echo "Error executing statement: " . $conn->error . "\n";
            echo "Statement: " . $statement . "\n\n";
            $success = false;
        }
    }
}

if ($success) {
    echo "Database updated successfully!";
} else {
    echo "There were some errors while updating the database.";
}
?> 