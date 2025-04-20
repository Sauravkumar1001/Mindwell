<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'mindwell');
define('DB_USER', 'root');
define('DB_PASS', '');

// JWT configuration
define('JWT_SECRET', 'your_jwt_secret_key_here'); // Change this to a secure random string
define('JWT_EXPIRY', 86400); // 24 hours in seconds

// CORS settings
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection function
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        die("Connection failed. Please try again later.");
    }
}

// Response helper function
function sendResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Validate JWT token
function validateToken($token) {
    try {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $header = base64_decode(strtr($parts[0], '-_', '+/'));
        $payload = base64_decode(strtr($parts[1], '-_', '+/'));
        $signature = base64_decode(strtr($parts[2], '-_', '+/'));

        $headerData = json_decode($header, true);
        $payloadData = json_decode($payload, true);

        // Verify expiration
        if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
            return false;
        }

        // Verify signature
        $data = $parts[0] . '.' . $parts[1];
        $expectedSignature = hash_hmac('sha256', $data, JWT_SECRET, true);
        
        return hash_equals($signature, $expectedSignature);
    } catch(Exception $e) {
        return false;
    }
}

// Get user from token
function getUserFromToken($token) {
    try {
        $parts = explode('.', $token);
        $payload = base64_decode(strtr($parts[1], '-_', '+/'));
        return json_decode($payload, true);
    } catch(Exception $e) {
        return null;
    }
}
?> 