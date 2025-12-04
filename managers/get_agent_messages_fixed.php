<?php
session_start();
require_once '../config/database.php';

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
    // Check if user is authenticated via $_SESSION (set by sync_session.php)
    $user = null;
    if (isset($_SESSION['user_id']) && isset($_SESSION['full_name'])) {
        $user = [
            'id' => $_SESSION['user_id'],
            'full_name' => $_SESSION['full_name'],
            'email' => $_SESSION['email'] ?? '',
            'user_type' => $_SESSION['user_type'] ?? $_SESSION['type'] ?? 'agent'
        ];
    }
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required - No PHP session found']);
        exit;
    }
    
    // Check if user is an agent - be more flexible with the check
    $is_agent = false;
    if (isset($user['user_type']) && $user['user_type'] === 'agent') {
        $is_agent = true;
    } elseif (isset($user['type']) && $user['type'] === 'agent') {
        $is_agent = true;
    } elseif (isset($user['account_type']) && $user['account_type'] === 'agent') {
        $is_agent = true;
    }
    
    if (!$is_agent) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied: Agent access required']);
        exit;
    }
    
    // Get the agent's name - try multiple fields
    $agent_name = null;
    if (isset($user['full_name']) && !empty($user['full_name'])) {
        $agent_name = $user['full_name'];
    } elseif (isset($user['name']) && !empty($user['name'])) {
        $agent_name = $user['name'];
    }
    
    if (!$agent_name) {
        echo json_encode([
            'success' => false, 
            'message' => 'Agent name not found in session',
            'debug' => $user
        ]);
        exit;
    }
    
    // Get agent's messages with more flexible matching
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
    
    // Try exact match and partial match
    $stmt->execute([$agent_name, "%$agent_name%"]);
    $messages = $stmt->fetchAll();
    
    // If no messages found, try alternative approach - get messages for this agent's user_id
    if (empty($messages)) {
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
            WHERE m.receiver_type = 'agent' AND (
                m.agent_id = ? OR 
                m.manager_name = ? OR
                EXISTS (
                    SELECT 1 FROM agents a 
                    WHERE a.user_id = ? AND m.manager_name = (
                        SELECT u2.full_name FROM users u2 WHERE u2.id = a.user_id
                    )
                )
            )
            ORDER BY m.created_at DESC
        ");
        
        $stmt->execute([$user['id'], $agent_name, $user['id']]);
        $messages = $stmt->fetchAll();
    }
    
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
        'agent_name' => $agent_name,
        'user_id' => $user['id']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve messages: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
