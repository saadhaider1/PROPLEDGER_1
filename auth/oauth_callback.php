<?php
session_start();
require_once '../config/database.php';
require_once '../config/oauth_config.php';

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
    
    // Validate state for security
    if (!validateOAuthState($state, $provider)) {
        throw new Exception('Invalid or expired OAuth state');
    }
    
    // Get OAuth configuration
    global $oauth_config;
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
    
    // Get user information
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
        throw new Exception('Failed to get user information from OAuth provider');
    }
    
    $user_info = json_decode($user_response, true);
    
    // Normalize user data based on provider
    $normalized_user = normalizeOAuthUserData($provider, $user_info);
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR (oauth_provider = ? AND oauth_id = ?)");
    $stmt->execute([$normalized_user['email'], $provider, $normalized_user['oauth_id']]);
    $existing_user = $stmt->fetch();
    
    if ($existing_user) {
        // Update existing user with OAuth info
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
            $normalized_user['oauth_id'],
            $normalized_user['picture'],
            $existing_user['id']
        ]);
        
        $user_id = $existing_user['id'];
        $user_data = $existing_user;
    } else {
        // Create new user
        $stmt = $pdo->prepare("
            INSERT INTO users (
                full_name, email, phone, country, user_type, 
                oauth_provider, oauth_id, profile_picture_url, 
                email_verified, newsletter_subscribed
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE, FALSE)
        ");
        
        $stmt->execute([
            $normalized_user['name'],
            $normalized_user['email'],
            '', // Phone will be collected later if needed
            'Pakistan', // Default country
            'investor', // Default user type
            $provider,
            $normalized_user['oauth_id'],
            $normalized_user['picture']
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
    $redirect_url = '../html/dashboard.html?oauth_success=1&provider=' . urlencode($provider);
    header("Location: $redirect_url");
    exit;
    
} catch (Exception $e) {
    // Redirect to login with error
    $error_message = urlencode($e->getMessage());
    header("Location: ../html/login.html?oauth_error=$error_message");
    exit;
}

function normalizeOAuthUserData($provider, $user_info) {
    switch ($provider) {
        case 'google':
            return [
                'oauth_id' => $user_info['id'],
                'name' => $user_info['name'],
                'email' => $user_info['email'],
                'picture' => $user_info['picture'] ?? null
            ];
            
        case 'linkedin':
            $first_name = $user_info['firstName']['localized']['en_US'] ?? '';
            $last_name = $user_info['lastName']['localized']['en_US'] ?? '';
            $picture = null;
            
            if (isset($user_info['profilePicture']['displayImage~']['elements'][0]['identifiers'][0]['identifier'])) {
                $picture = $user_info['profilePicture']['displayImage~']['elements'][0]['identifiers'][0]['identifier'];
            }
            
            return [
                'oauth_id' => $user_info['id'],
                'name' => trim("$first_name $last_name"),
                'email' => $user_info['emailAddress'] ?? '', // Need separate API call for email
                'picture' => $picture
            ];
            
        case 'facebook':
            return [
                'oauth_id' => $user_info['id'],
                'name' => $user_info['name'],
                'email' => $user_info['email'],
                'picture' => $user_info['picture']['data']['url'] ?? null
            ];
            
        default:
            throw new Exception("Unsupported provider for data normalization: $provider");
    }
}
?>
