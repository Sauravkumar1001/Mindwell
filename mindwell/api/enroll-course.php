<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get POST data
$rawData = file_get_contents('php://input');
error_log('Raw POST data: ' . $rawData);

$data = json_decode($rawData, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON decode error: ' . json_last_error_msg());
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data received'
    ]);
    exit();
}

error_log('Decoded data: ' . print_r($data, true));

// Validate required fields
if (!isset($data['course_id']) || !isset($data['email'])) {
    error_log('Missing required fields: ' . print_r($data, true));
    echo json_encode([
        'success' => false,
        'message' => 'Course ID and email are required'
    ]);
    exit();
}

// Validate email format
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    error_log('Invalid email format: ' . $data['email']);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit();
}

try {
    // Get database connection
    $conn = getDBConnection();
    if (!$conn) {
        error_log('Failed to connect to database');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
        exit();
    }
    error_log('Database connection successful');

    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'course_enrollments'");
    if ($tableCheck->rowCount() == 0) {
        error_log('Table course_enrollments does not exist');
        // Create table if it doesn't exist
        $createTable = $conn->exec("CREATE TABLE IF NOT EXISTS course_enrollments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            course_id INT,
            course_title VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('active', 'completed', 'cancelled') DEFAULT 'active'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        error_log('Table creation result: ' . ($createTable === false ? 'failed' : 'success'));
    }

    // Check if user is already enrolled
    $stmt = $conn->prepare("SELECT id FROM course_enrollments WHERE email = ? AND course_id = ?");
    if (!$stmt) {
        error_log('Prepare statement failed: ' . print_r($conn->errorInfo(), true));
        echo json_encode([
            'success' => false,
            'message' => 'Database error'
        ]);
        exit();
    }
    
    $stmt->execute([$data['email'], $data['course_id']]);
    if ($stmt->rowCount() > 0) {
        error_log('User already enrolled: ' . $data['email'] . ' in course ' . $data['course_id']);
        echo json_encode([
            'success' => false,
            'message' => 'You are already enrolled in this course'
        ]);
        exit();
    }
    
    // Insert enrollment
    $stmt = $conn->prepare("INSERT INTO course_enrollments (course_id, course_title, email, user_id) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        error_log('Prepare statement failed: ' . print_r($conn->errorInfo(), true));
        echo json_encode([
            'success' => false,
            'message' => 'Database error'
        ]);
        exit();
    }
    
    $result = $stmt->execute([
        $data['course_id'],
        $data['course_title'],
        $data['email'],
        $data['user_id'] ?? null
    ]);
    
    if (!$result) {
        error_log('Execute failed: ' . print_r($stmt->errorInfo(), true));
        echo json_encode([
            'success' => false,
            'message' => 'Failed to enroll in course'
        ]);
        exit();
    }
    
    // Get the inserted enrollment ID
    $enrollmentId = $conn->lastInsertId();
    error_log('Successfully enrolled user. Enrollment ID: ' . $enrollmentId);
    
    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Course enrollment successful!',
        'data' => ['enrollment_id' => $enrollmentId]
    ]);
    exit();
} catch(PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to enroll in course'
    ]);
    exit();
}
?> 