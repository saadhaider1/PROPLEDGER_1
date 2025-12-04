<?php
session_start();
require_once '../config/database.php';

try {
    // Get provider from URL
    $provider = $_GET['provider'] ?? null;
    $code = $_GET['code'] ?? null;
    $state = $_GET['state'] ?? null;
    $error = $_GET['error'] ?? null;
    
    // Handle OAuth errors
    if ($error) {
        throw new Exception("OAuth error: $error");
    }
    
    // Validate required parameters
    if (!$provider || !$code || !$state) {
        throw new Exception('Missing required OAuth parameters');
    }
    
    // Validate state using session (instead of database)
    if (!isset($_SESSION['oauth_state']) || $_SESSION['oauth_state'] !== $state) {
        throw new Exception('Invalid or expired OAuth state');
    }
    
    if (!isset($_SESSION['oauth_provider']) || $_SESSION['oauth_provider'] !== $provider) {
        throw new Exception('OAuth provider mismatch');
    }
    
    // Clear session state
    unset($_SESSION['oauth_state']);
    unset($_SESSION['oauth_provider']);
    
    // OAuth configurations for token exchange
    $oauth_config = [
        'google' => [
            'client_id' => '1059043152331-f24i9e3rpgv1c10h1fpel2aqgn5tfev2.apps.googleusercontent.com',
            'client_secret' => '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',
            'redirect_uri' => 'http://localhost:8000/auth/oauth_callback_simple.php',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'user_info_url' => 'https://www.googleapis.com/oauth2/v2/userinfo'
        ]
    ];
    
    // Get OAuth configuration
    if (!isset($oauth_config[$provider])) {
        throw new Exception("Unsupported OAuth provider: $provider");
    }
    
    $config = $oauth_config[$provider];
    
    // Exchange code for access token
    $token_data = [
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => $config['redirect_uri']
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['token_url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    
    $token_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception('Failed to exchange OAuth code for token');
    }
    
    $token_info = json_decode($token_response, true);
    if (!isset($token_info['access_token'])) {
        throw new Exception('No access token received from OAuth provider');
    }
    
    // Get user information from Google
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['user_info_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token_info['access_token'],
        'Accept: application/json'
    ]);
    
    $user_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception('Failed to get user information from Google');
    }
    
    $user_info = json_decode($user_response, true);
    
    // Use real Google user data
    $google_user_data = [
        'oauth_id' => $user_info['id'],
        'name' => $user_info['name'],
        'email' => $user_info['email'],
        'picture' => $user_info['picture'] ?? null
    ];
    
    // For demo purposes, create a test user account
    $demo_user_data = [
        'oauth_id' => 'demo_' . $provider . '_' . time(),
        'name' => 'Demo User (' . ucfirst($provider) . ')',
        'email' => 'demo@' . $provider . '.com',
        'picture' => null
    ];
    
    // Check if Google user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR (oauth_provider = ? AND oauth_id = ?)");
    $stmt->execute([$google_user_data['email'], $provider, $google_user_data['oauth_id']]);
    $existing_user = $stmt->fetch();
    
    if ($existing_user) {
        // Update existing user with Google OAuth info
        $stmt = $pdo->prepare("
            UPDATE users SET 
                oauth_provider = ?, 
                oauth_id = ?, 
                profile_picture_url = ?,
                email_verified = TRUE,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([
            $provider,
            $google_user_data['oauth_id'],
            $google_user_data['picture'],
            $existing_user['id']
        ]);
        
        $user_id = $existing_user['id'];
        $user_data = $existing_user;
    } else {
        // Create new user from Google data
        $stmt = $pdo->prepare("
            INSERT INTO users (
                full_name, email, phone, country, user_type, 
                oauth_provider, oauth_id, profile_picture_url, 
                email_verified, newsletter_subscribed
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE, FALSE)
        ");
        
        $stmt->execute([
            $google_user_data['name'],
            $google_user_data['email'],
            '', // Phone will be collected later if needed
            'Pakistan', // Default country
            'investor', // Default user type
            $provider,
            $google_user_data['oauth_id'],
            $google_user_data['picture']
        ]);
        
        $user_id = $pdo->lastInsertId();
        
        // Get the created user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch();
    }
    
    // Create session
    $session_token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    // Clean up old sessions
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ? AND expires_at < NOW()");
    $stmt->execute([$user_id]);
    
    // Insert new session
    $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $session_token, $expires_at]);
    
    // Set session cookie
    setcookie('propledger_session', $session_token, strtotime('+30 days'), '/', '', false, true);
    
    // Redirect to dashboard with success
    $redirect_url = '/dashboard.html?oauth_success=1&provider=' . urlencode($provider);
    header("Location: $redirect_url");
    exit;
    
} catch (Exception $e) {
    // Redirect to login with error
    $error_message = urlencode($e->getMessage());
    header("Location: /login.html?oauth_error=$error_message");
    exit;
}
?>
