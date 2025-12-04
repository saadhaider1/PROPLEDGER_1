<?php
session_start();
require_once '../config/database.php';

// CORS headers for Next.js - Allow any localhost port
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
// Allow any localhost port (3000, 3001, 3002, etc.)
if (preg_match('/^http:\/\/localhost(:\d+)?$/', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    // Fallback for development
    header('Access-Control-Allow-Origin: http://localhost:3000');
}
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON response
ini_set('log_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Check database connection
    if (!isset($pdo)) {
        throw new Exception('Database connection failed. Please check your configuration.');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($input['email']) || empty($input['password'])) {
        throw new Exception('Email and password are required');
    }
    
    // Find user by email
    $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, user_type, is_active FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Invalid email or password. Please check your credentials or sign up for a new account.');
    }
    
    if (!$user['is_active']) {
        throw new Exception('Account is deactivated. Please contact support.');
    }
    
    // Verify password
    if (!password_verify($input['password'], $user['password_hash'])) {
        throw new Exception('Invalid email or password. Please check your credentials.');
    }
    
    // Check if login type matches user type (if provided)
    if (isset($input['loginType'])) {
        // Allow admin to login via 'user' type or specific 'admin' type if we add it later
        // For now, if user is admin, we skip this check or handle it
        if ($user['user_type'] === 'admin') {
            // Admin can login from anywhere
        } elseif ($input['loginType'] === 'agent' && $user['user_type'] !== 'agent') {
            throw new Exception('This account is not registered as an agent. Please use User Login.');
        } elseif ($input['loginType'] === 'user' && $user['user_type'] === 'agent') {
            throw new Exception('This is an agent account. Please use Agent Login.');
        }
    }
    
    // Create new session
    $session_token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    // Clean up old sessions for this user
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ? AND expires_at < NOW()");
    $stmt->execute([$user['id']]);
    
    // Insert new session
    $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $session_token, $expires_at]);
    
    // Set session cookie
    $remember = isset($input['remember']) && $input['remember'];
    $cookie_duration = $remember ? time() + (30 * 24 * 60 * 60) : 0; // 30 days or session
    
    // Set cookie with proper settings for cross-origin
    setcookie(
        'propledger_session',
        $session_token,
        [
            'expires' => $cookie_duration,
            'path' => '/',
            'domain' => 'localhost', // Allow cookie on all localhost ports
            'secure' => false, // Set to true in production with HTTPS
            'httponly' => true, // Prevent JavaScript access
            'samesite' => 'Lax' // Allow cross-origin with navigation
        ]
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'user' => [
            'id' => $user['id'],
            'name' => $user['full_name'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'type' => $user['user_type'],
            'user_type' => $user['user_type']
        ],
        'redirect' => match ($user['user_type']) {
            'agent' => '/agent-dashboard',
            'admin' => '/admin/dashboard',
            default => '/dashboard'
        }
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
