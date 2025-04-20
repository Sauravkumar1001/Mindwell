<?php
require_once 'config.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the request
error_log("Contact form submission received");
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get POST data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

// Validate required fields
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    error_log("Missing required fields");
    sendResponse(false, 'All fields are required');
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("Invalid email format: " . $email);
    sendResponse(false, 'Invalid email format');
    exit();
}

try {
    error_log("Attempting database connection");
    $conn = getDBConnection();
    error_log("Database connection successful");
    
    // Insert contact message
    $stmt = $conn->prepare("
        INSERT INTO contact_messages (name, email, subject, message) 
        VALUES (?, ?, ?, ?)
    ");
    
    error_log("Executing SQL statement with values: " . print_r([
        $name,
        $email,
        $subject,
        $message
    ], true));
    
    $stmt->execute([
        $name,
        $email,
        $subject,
        $message
    ]);
    
    // Get the inserted message ID
    $messageId = $conn->lastInsertId();
    error_log("Message inserted successfully with ID: " . $messageId);
    
    // Send success response
    sendResponse(true, 'Message sent successfully', ['message_id' => $messageId]);
    
} catch(PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    sendResponse(false, 'Failed to send message');
}
?> 