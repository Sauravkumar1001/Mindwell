<?php
require_once 'config.php';

try {
    $conn = getDBConnection();
    echo "Database connection successful!";
    
    // Test if tables exist
    $tables = ['users', 'user_sessions', 'password_resets'];
    foreach ($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<br>Table '$table' exists!";
        } else {
            echo "<br>Table '$table' does not exist!";
        }
    }
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?> 