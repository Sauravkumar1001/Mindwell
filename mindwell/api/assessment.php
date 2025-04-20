<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once 'config.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get authorization header
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Verify token and get user ID
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token']);
        exit();
    }
    
    $user = $result->fetch_assoc();
    $user_id = $user['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get assessment history
        $stmt = $conn->prepare("
            SELECT * FROM assessments 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $assessments = [];
        while ($row = $result->fetch_assoc()) {
            $assessments[] = [
                'id' => $row['id'],
                'score' => $row['score'],
                'category' => $row['category'],
                'timestamp' => $row['created_at']
            ];
        }
        
        echo json_encode($assessments);
    } 
    else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Save new assessment
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['score']) || !isset($data['category'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit();
        }
        
        $score = $data['score'];
        $category = $data['category'];
        
        $stmt = $conn->prepare("
            INSERT INTO assessments (user_id, score, category) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("ids", $user_id, $score, $category);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Assessment saved successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save assessment']);
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?> 