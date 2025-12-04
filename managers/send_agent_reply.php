<?php
session_start();
require_once '../config/database.php';
require_once '../auth/check_session.php';

// CORS headers for Next.js - Allow any localhost port
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (preg_match('/^http:\/\/localhost(:\d+)?$/', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: http://localhost:3000');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
    
    // Check if user is an agent
    if ($user['user_type'] !== 'agent' && $user['type'] !== 'agent' && $user['account_type'] !== 'agent') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied: Agent access required']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($input['message_id']) || empty($input['reply_message'])) {
        throw new Exception('Message ID and reply message are required');
    }
    
    if (empty($input['user_id'])) {
        throw new Exception('User ID is required');
    }
    
    // Validate message length
    if (strlen($input['reply_message']) > 2000) {
        throw new Exception('Reply message is too long. Maximum 2000 characters allowed.');
    }
    
    // First, update the original message as replied
    $stmt = $pdo->prepare("
        UPDATE manager_messages 
        SET status = 'replied', replied_at = NOW(), reply_message = ?
        WHERE id = ? AND receiver_type = 'agent'
    ");
    
    $stmt->execute([$input['reply_message'], $input['message_id']]);
    
    // Create a new message as agent's reply to user
    $stmt = $pdo->prepare("
        INSERT INTO manager_messages (
            user_id, 
            agent_id, 
            manager_name, 
            subject, 
            message, 
            priority, 
            sender_type, 
            receiver_type,
            status,
            created_at
        ) VALUES (?, ?, ?, ?, ?, 'normal', 'agent', 'user', 'unread', NOW())
    ");
    
    // Get original message details for context
    $original_stmt = $pdo->prepare("SELECT subject, user_id FROM manager_messages WHERE id = ?");
    $original_stmt->execute([$input['message_id']]);
    $original_msg = $original_stmt->fetch();
    
    $reply_subject = 'Re: ' . $original_msg['subject'];
    
    $stmt->execute([
        $input['user_id'],
        $user['id'],
        $user['full_name'],
        $reply_subject,
        $input['reply_message']
    ]);
    
    $reply_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Reply sent successfully!',
        'reply_id' => $reply_id,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
