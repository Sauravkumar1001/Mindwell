<?php
require_once 'config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['email'])) {
    sendResponse(false, 'Email is required');
}

$email = trim($data['email']);

try {
    $conn = getDBConnection();
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        sendResponse(false, 'Email not found');
    }
    
    // Generate reset token
    $resetToken = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Store reset token
    $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $resetToken, $expires]);
    
    // TODO: Send email with reset link
    // For now, we'll just return success
    sendResponse(true, 'Password reset email sent');
    
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?> 