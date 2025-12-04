<?php
// Get messages received by agents (from users)
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Get all messages where receiver_type = 'agent' (messages sent by users to agents)
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
            u.full_name as sender_name,
            u.email as sender_email,
            'User Message' as message_type
        FROM manager_messages m
        LEFT JOIN users u ON m.user_id = u.id
        WHERE m.receiver_type = 'agent' AND m.sender_type = 'user'
        ORDER BY m.created_at DESC
    ");
    
    $stmt->execute();
    $messages = $stmt->fetchAll();
    
    // Count unread messages
    $unread_count = 0;
    foreach ($messages as $msg) {
        if ($msg['status'] === 'unread') {
            $unread_count++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'total_count' => count($messages),
        'unread_count' => $unread_count,
        'message_type' => 'user_messages'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve messages: ' . $e->getMessage()
    ]);
}
?>
