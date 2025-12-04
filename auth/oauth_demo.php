<?php
session_start();
require_once '../config/database.php';

// DEMO OAuth Login - Works without Google OAuth setup
// This creates a demo account to test the OAuth flow

try {
    // Get provider from URL
    $provider = $_GET['provider'] ?? 'google';
    
    // Create a demo user based on session or generate new
    $demo_email = 'demo_' . $provider . '_' . time() . '@propledger.com';
    $demo_name = 'Demo User (' . ucfirst($provider) . ')';
    
    // Check if demo user already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email LIKE ?");
    $stmt->execute(['demo_' . $provider . '%@propledger.com']);
    $existing_user = $stmt->fetch();
    
    if ($existing_user) {
        $user_id = $existing_user['id'];
        $user_data = $existing_user;
    } else {
        // Create new demo user
        $stmt = $pdo->prepare("
            INSERT INTO users (
                full_name, email, phone, country, user_type, 
                oauth_provider, oauth_id, profile_picture_url, 
                email_verified, newsletter_subscribed, password_hash
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE, FALSE, ?)
        ");
        
        $demo_oauth_id = 'demo_' . $provider . '_' . bin2hex(random_bytes(8));
        $demo_password = password_hash('demo123', PASSWORD_DEFAULT);
        
        $stmt->execute([
            $demo_name,
            $demo_email,
            '+92-300-0000000',
            'Pakistan',
            'investor',
            $provider,
            $demo_oauth_id,
            'https://ui-avatars.com/api/?name=' . urlencode($demo_name) . '&background=0D8ABC&color=fff',
            $demo_password
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
    $redirect_url = 'http://localhost:8000/dashboard.html?oauth_success=1&provider=' . urlencode($provider) . '&demo=1';
    header("Location: $redirect_url");
    exit;
    
} catch (Exception $e) {
    // Redirect to login with error
    $error_message = urlencode($e->getMessage());
    header("Location: http://localhost:8000/login.html?oauth_error=$error_message");
    exit;
}
?>
