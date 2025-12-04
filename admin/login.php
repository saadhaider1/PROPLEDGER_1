<?php
// admin/login.php
// CORS headers for Next.js - Allow any localhost port
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (preg_match('/^http:\/\/localhost(:\d+)?$/', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: http://localhost:3000');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

// Debug logging (temporary)
error_log("Login attempt - Username: " . $username . " Password: " . $password);

// Hardcoded credentials as requested
// Username: admin (or admin 123), Password: psd12345
if (($username === 'admin' || $username === 'admin 123') && $password === 'psd12345') {
    session_start();
    $_SESSION['admin_logged_in'] = true;
    
    // Set a cookie for cross-origin requests
    setcookie(
        'admin_session',
        'logged_in',
        [
            'expires' => time() + (30 * 24 * 60 * 60), // 30 days
            'path' => '/',
            'domain' => '',  // Empty for current domain
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
    
    echo json_encode(['success' => true, 'message' => 'Login successful']);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid credentials', 'debug' => ['username' => $username, 'password' => $password]]);
}
?>
