<?php
session_start();
require_once '../config/database.php';
require_once '../auth/check_session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Check if user is authenticated
    $user = checkUserSession();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
    
    // Get user's unread messages (notifications)
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            manager_name, 
            subject, 
            message, 
            priority, 
            sender_type,
            created_at
        FROM manager_messages 
        WHERE user_id = ? AND receiver_type = 'user' AND status = 'unread'
        ORDER BY created_at DESC
        LIMIT 10
    ");
    
    $stmt->execute([$user['id']]);
    $notifications = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'count' => count($notifications)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve notifications: ' . $e->getMessage()
    ]);
}
?>
