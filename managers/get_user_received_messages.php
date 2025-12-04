<?php
// Get messages received by users (from agents)
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Get all messages where receiver_type = 'user' (messages sent by agents to users)
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
            'Agent Reply' as message_type
        FROM manager_messages m
        WHERE m.receiver_type = 'user' AND m.sender_type = 'agent'
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
        'message_type' => 'agent_replies'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve messages: ' . $e->getMessage()
    ]);
}
?>
