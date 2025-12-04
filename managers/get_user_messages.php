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
    
    // Get user's messages (both sent and received)
    $stmt = $pdo->prepare("
        SELECT 
            m.id, 
            m.user_id,
            m.agent_id,
            m.manager_name, 
            m.subject, 
            m.message, 
            m.priority, 
            m.status, 
            m.sender_type,
            m.receiver_type,
            m.created_at, 
            m.replied_at, 
            m.reply_message,
            CASE 
                WHEN m.sender_type = 'user' THEN 'sent'
                WHEN m.sender_type = 'agent' THEN 'received'
                ELSE 'unknown'
            END as message_direction
        FROM manager_messages m
        WHERE m.user_id = ?
        ORDER BY m.created_at DESC
    ");
    
    $stmt->execute([$user['id']]);
    $messages = $stmt->fetchAll();
    
    // Count unread messages received by this user
    $unread_count = 0;
    foreach ($messages as $msg) {
        if ($msg['sender_type'] === 'agent' && $msg['status'] === 'unread') {
            $unread_count++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'total_count' => count($messages),
        'unread_count' => $unread_count
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve messages: ' . $e->getMessage()
    ]);
}
?>
