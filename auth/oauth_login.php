<?php
session_start();
require_once '../config/database.php';
require_once '../config/oauth_config.php';

// CORS headers for Next.js
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// OAuth login doesn't require POST - it redirects via GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['provider'])) {
        throw new Exception('OAuth provider is required');
    }
    
    $provider = $input['provider'];
    
    // Validate provider
    if (!in_array($provider, ['google', 'linkedin', 'facebook'])) {
        throw new Exception('Unsupported OAuth provider');
    }
    
    // Generate state for security
    $state = generateOAuthState($provider);
    
    // Get OAuth authorization URL
    $auth_url = getOAuthAuthUrl($provider, $state);
    
    echo json_encode([
        'success' => true,
        'auth_url' => $auth_url,
        'provider' => $provider,
        'state' => $state
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
