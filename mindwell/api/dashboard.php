<?php
require_once 'config.php';

// Get the authorization header
$headers = getallheaders();
$token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

if (!$token) {
    sendResponse(false, 'No token provided');
    exit;
}

try {
    // Validate token and get user ID
    $decoded = validateToken($token);
    $userId = $decoded->user_id;
    
    // Get database connection
    $conn = getDBConnection();
    
    // Get mood data for the last 7 days
    $stmt = $conn->prepare("
        SELECT DATE(created_at) as date, AVG(mood_score) as score
        FROM mood_entries
        WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute([$userId]);
    $moodData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format mood data for chart
    $moodChartData = [
        'labels' => array_map(function($entry) { return date('M d', strtotime($entry['date'])); }, $moodData),
        'scores' => array_map(function($entry) { return (float)$entry['score']; }, $moodData)
    ];
    
    // Get recent activities
    $stmt = $conn->prepare("
        (SELECT 'mood' as type, created_at, CONCAT('Recorded mood score: ', mood_score) as description
         FROM mood_entries WHERE user_id = ?)
        UNION
        (SELECT 'journal' as type, created_at, CONCAT('Wrote journal entry: ', LEFT(content, 50), '...') as description
         FROM journal_entries WHERE user_id = ?)
        UNION
        (SELECT 'assessment' as type, created_at, 'Completed mental health assessment' as description
         FROM assessment_results WHERE user_id = ?)
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$userId, $userId, $userId]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $stats = [
        'moodAverage' => 0,
        'journalEntries' => 0,
        'completedAssessments' => 0
    ];
    
    // Get mood average
    $stmt = $conn->prepare("
        SELECT AVG(mood_score) as average
        FROM mood_entries
        WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['moodAverage'] = $result['average'] ? round($result['average'], 1) : 0;
    
    // Get journal entries count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM journal_entries
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['journalEntries'] = $result['count'];
    
    // Get completed assessments count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM assessment_results
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['completedAssessments'] = $result['count'];
    
    // Send response
    sendResponse(true, 'Dashboard data retrieved successfully', [
        'moodData' => $moodChartData,
        'activities' => $activities,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    sendResponse(false, 'Failed to load dashboard data');
}
?> 