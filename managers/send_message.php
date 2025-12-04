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
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required_fields = ['manager', 'subject', 'message'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Validate message length
    if (strlen($input['message']) > 2000) {
        throw new Exception('Message is too long. Maximum 2000 characters allowed.');
    }
    
    if (strlen($input['subject']) > 200) {
        throw new Exception('Subject is too long. Maximum 200 characters allowed.');
    }
    
    // Set default priority if not provided
    $priority = isset($input['priority']) ? $input['priority'] : 'normal';
    if (!in_array($priority, ['normal', 'high', 'urgent'])) {
        $priority = 'normal';
    }

    // Optional agent_id (Next.js frontend can send this for precise routing)
    $agent_id = isset($input['agent_id']) && is_numeric($input['agent_id'])
        ? (int)$input['agent_id']
        : null;
    
    // Insert message into database
    $stmt = $pdo->prepare("
        INSERT INTO manager_messages (user_id, agent_id, manager_name, subject, message, priority, sender_type, receiver_type, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, 'user', 'agent', NOW())
    ");
    
    $stmt->execute([
        $user['id'],
        $agent_id,
        $input['manager'],
        $input['subject'],
        $input['message'],
        $priority
    ]);
    
    $message_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully!',
        'message_id' => $message_id,
        'manager' => $input['manager'],
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
