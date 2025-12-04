<?php
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

function checkUserSession() {
    global $pdo;
    
    $session_token = $_COOKIE['propledger_session'] ?? null;
    
    if (!$session_token) {
        return null;
    }
    
    try {
        // Check if session exists and is valid
        $stmt = $pdo->prepare("
            SELECT u.id, u.full_name, u.email, u.user_type, u.is_active 
            FROM users u 
            JOIN user_sessions s ON u.id = s.user_id 
            WHERE s.session_token = ? AND s.expires_at > NOW() AND u.is_active = 1
        ");
        $stmt->execute([$session_token]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Update session expiry
            $stmt = $pdo->prepare("UPDATE user_sessions SET expires_at = ? WHERE session_token = ?");
            $new_expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
            $stmt->execute([$new_expiry, $session_token]);
            
            return $user;
        }
        
        // Clean up expired session
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE session_token = ?");
        $stmt->execute([$session_token]);
        
        return null;
        
    } catch (Exception $e) {
        return null;
    }
}

// API endpoint for checking session
// Only output JSON if this file is being accessed directly (not included)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && basename($_SERVER['SCRIPT_FILENAME']) === 'check_session.php') {
    header('Content-Type: application/json');
    
    $user = checkUserSession();
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Not authenticated'
        ]);
    }
}
?>
