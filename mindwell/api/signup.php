<?php
require_once 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Ensure request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method');
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Log received data for debugging
error_log('Received data: ' . print_r($data, true));

// Validate input
if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
    sendResponse(false, 'Missing required fields');
}

$name = trim($data['name']);
$email = trim($data['email']);
$password = $data['password'];

// Additional validation
if (empty($name)) {
    sendResponse(false, 'Name is required');
}

if (empty($email)) {
    sendResponse(false, 'Email is required');
}

if (empty($password)) {
    sendResponse(false, 'Password is required');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'Invalid email format');
}

// Validate password strength
if (strlen($password) < 8) {
    sendResponse(false, 'Password must be at least 8 characters long');
}

try {
    $conn = getDBConnection();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        sendResponse(false, 'Email already registered');
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hashedPassword]);
    
    // Get the newly created user ID
    $userId = $conn->lastInsertId();
    
    // Generate JWT token
    $token = generateJWT($userId, $email);
    
    // Store session in database
    $expires = date('Y-m-d H:i:s', strtotime('+1 day'));
    $stmt = $conn->prepare("INSERT INTO user_sessions (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $token, $expires]);
    
    // Set cookie with token
    setcookie('auth_token', $token, time() + 86400, "/", "", true, true);
    
    sendResponse(true, 'Registration successful', [
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email
        ],
        'token' => $token
    ]);
    
} catch(PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    sendResponse(false, 'Database error: ' . $e->getMessage());
}

// Function to generate JWT token
function generateJWT($userId, $email) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'user_id' => $userId,
        'email' => $email,
        'iat' => time(),
        'exp' => time() + (86400 * 30) // 30 days
    ]);
    
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}
?> 