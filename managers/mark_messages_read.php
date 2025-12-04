<?php
session_start();
require_once '../config/database.php';
require_once '../auth/check_session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['mark_all']) && $input['mark_all']) {
        // Mark all messages as read for this agent
        if ($user['user_type'] === 'agent' || $user['type'] === 'agent' || $user['account_type'] === 'agent') {
            $stmt = $pdo->prepare("
                UPDATE manager_messages 
                SET status = 'read' 
                WHERE receiver_type = 'agent' AND manager_name = ? AND status = 'unread'
            ");
            $stmt->execute([$user['full_name']]);
        } else {
            // Mark all messages as read for this user
            $stmt = $pdo->prepare("
                UPDATE manager_messages 
                SET status = 'read' 
                WHERE user_id = ? AND receiver_type = 'user' AND status = 'unread'
            ");
            $stmt->execute([$user['id']]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'All messages marked as read'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to mark messages as read: ' . $e->getMessage()
    ]);
}
?>
