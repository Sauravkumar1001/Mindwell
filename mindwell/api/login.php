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

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['email']) || !isset($data['password'])) {
    sendResponse(false, 'Missing required fields');
}

$email = trim($data['email']);
$password = $data['password'];
$remember = isset($data['remember']) ? $data['remember'] : false;

try {
    $conn = getDBConnection();
    
    // Get user by email
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($password, $user['password'])) {
        sendResponse(false, 'Invalid email or password');
    }
    
    // Generate JWT token
    $token = generateJWT($user['id'], $user['email']);
    
    // Store session in database
    $expires = $remember ? date('Y-m-d H:i:s', strtotime('+30 days')) : date('Y-m-d H:i:s', strtotime('+1 day'));
    $stmt = $conn->prepare("INSERT INTO user_sessions (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $token, $expires]);
    
    // Set cookie with token
    $cookieExpires = $remember ? time() + (86400 * 30) : 0; // 30 days or session
    setcookie('auth_token', $token, $cookieExpires, "/", "", true, true);
    
    sendResponse(true, 'Login successful', [
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ],
        'token' => $token,
        'redirect' => 'dashboard.html'
    ]);
    
} catch(PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    sendResponse(false, 'Database error: ' . $e->getMessage());
}

// Helper function to generate JWT token
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