<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Get session token from cookie
    $session_token = $_COOKIE['propledger_session'] ?? null;
    
    if ($session_token) {
        // Delete session from database
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE session_token = ?");
        $stmt->execute([$session_token]);
        
        // Clear session cookie
        setcookie('propledger_session', '', time() - 3600, '/', '', false, true);
    }
    
    // Destroy PHP session
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully',
        'redirect' => 'html/index.html'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Logout failed'
    ]);
}
?>
