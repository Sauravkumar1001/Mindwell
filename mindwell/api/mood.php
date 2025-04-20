<?php
require_once 'config.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get authorization header
$headers = getallheaders();
$auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (!$auth_header || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
    sendResponse(false, 'Authorization token required');
}

$token = $matches[1];
if (!validateToken($token)) {
    sendResponse(false, 'Invalid or expired token');
}

$user = getUserFromToken($token);
if (!$user || !isset($user['user_id'])) {
    sendResponse(false, 'Invalid user token');
}

$user_id = $user['user_id'];

// Handle different HTTP methods
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get mood entries
        try {
            $conn = getDBConnection();
            
            // Get date range from query parameters
            $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
            $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
            
            $stmt = $conn->prepare("
                SELECT mood_score, notes, created_at 
                FROM mood_entries 
                WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$user_id, $start_date, $end_date]);
            
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendResponse(true, 'Mood entries retrieved successfully', $entries);
            
        } catch(PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            sendResponse(false, 'Failed to retrieve mood entries');
        }
        break;

    case 'POST':
        // Add new mood entry
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['mood_score']) || !is_numeric($data['mood_score'])) {
            sendResponse(false, 'Mood score is required and must be numeric');
        }
        
        $mood_score = intval($data['mood_score']);
        if ($mood_score < 1 || $mood_score > 10) {
            sendResponse(false, 'Mood score must be between 1 and 10');
        }
        
        $notes = isset($data['notes']) ? $data['notes'] : '';
        
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("
                INSERT INTO mood_entries (user_id, mood_score, notes) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$user_id, $mood_score, $notes]);
            
            // Log the activity
            $stmt = $conn->prepare("
                INSERT INTO user_activities (user_id, activity_type, description) 
                VALUES (?, 'mood_entry', ?)
            ");
            $stmt->execute([$user_id, "Recorded mood score: $mood_score"]);
            
            sendResponse(true, 'Mood entry added successfully');
            
        } catch(PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            sendResponse(false, 'Failed to add mood entry');
        }
        break;

    default:
        sendResponse(false, 'Method not allowed', null, 405);
        break;
}
?> 