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
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
    
    // Check if user is an agent
    if ($user['user_type'] !== 'agent' && $user['type'] !== 'agent' && $user['account_type'] !== 'agent') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied: Agent access required']);
        exit;
    }
    
    // Get agent's full name - try multiple fields for compatibility
    $agent_name = $user['full_name'] ?? $user['name'] ?? '';
    
    // Get agent's messages (messages sent to this agent by name)
    // Use LIKE for more flexible matching
    $stmt = $pdo->prepare("
        SELECT 
            m.id, 
            m.user_id,
            m.manager_name, 
            m.subject, 
            m.message, 
            m.priority, 
            m.status, 
            m.created_at, 
            m.replied_at, 
            m.reply_message,
            u.full_name as sender_name,
            u.email as sender_email
        FROM manager_messages m
        LEFT JOIN users u ON m.user_id = u.id
        WHERE m.manager_name = ? OR m.manager_name LIKE ?
        ORDER BY m.created_at DESC
    ");
    
    $like_pattern = '%' . $agent_name . '%';
    $stmt->execute([$agent_name, $like_pattern]);
    $messages = $stmt->fetchAll();
    
    // Debug info
    $debug_info = [
        'agent_name_used' => $agent_name,
        'user_data' => $user,
        'query_executed' => true,
        'messages_found' => count($messages)
    ];
    
    // Count unread messages for this agent
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
        'debug' => $debug_info
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve messages: ' . $e->getMessage()
    ]);
}
?>
