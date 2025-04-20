<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Test database connection
    $conn = getDBConnection();
    echo "Database connection successful!<br>";
    
    // Test table existence
    $stmt = $conn->query("SHOW TABLES LIKE 'contact_messages'");
    if ($stmt->rowCount() > 0) {
        echo "Table 'contact_messages' exists!<br>";
        
        // Test table structure
        $stmt = $conn->query("DESCRIBE contact_messages");
        echo "Table structure:<br>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Field: " . $row['Field'] . ", Type: " . $row['Type'] . "<br>";
        }
        
        // Test insert
        $testData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message'
        ];
        
        $stmt = $conn->prepare("
            INSERT INTO contact_messages (name, email, subject, message) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $testData['name'],
            $testData['email'],
            $testData['subject'],
            $testData['message']
        ]);
        
        echo "Test data inserted successfully!<br>";
        
        // Show all records
        $stmt = $conn->query("SELECT * FROM contact_messages");
        echo "Current records in table:<br>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID: " . $row['id'] . ", Name: " . $row['name'] . ", Email: " . $row['email'] . "<br>";
        }
    } else {
        echo "Table 'contact_messages' does not exist!<br>";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 