<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    if (!in_array($provider, ['google'])) {
        throw new Exception('Unsupported OAuth provider');
    }
    
    // Simple state generation without database (for testing)
    $state = bin2hex(random_bytes(32));
    
    // Store state in session instead of database (temporary fix)
    $_SESSION['oauth_state'] = $state;
    $_SESSION['oauth_provider'] = $provider;
    
    // OAuth configurations
    $oauth_config = [
        'google' => [
            'client_id' => '1059043152331-f24i9e3rpgv1c10h1fpel2aqgn5tfev2.apps.googleusercontent.com',
            'redirect_uri' => 'http://localhost:8000/auth/oauth_callback_simple.php',
            'scope' => 'openid email profile',
            'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth'
        ]
    ];
    
    if (!isset($oauth_config[$provider])) {
        throw new Exception("Unsupported OAuth provider: $provider");
    }
    
    $config = $oauth_config[$provider];
    
    // Build OAuth URL
    $params = [
        'client_id' => $config['client_id'],
        'redirect_uri' => $config['redirect_uri'],
        'scope' => $config['scope'],
        'response_type' => 'code',
        'state' => $state
    ];
    
    $auth_url = $config['auth_url'] . '?' . http_build_query($params);
    
    echo json_encode([
        'success' => true,
        'auth_url' => $auth_url,
        'provider' => $provider,
        'state' => $state,
        'message' => 'OAuth URL generated successfully (using session-based state)'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
