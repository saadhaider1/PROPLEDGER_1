<?php
// OAuth Configuration for PROPLEDGER
// Add your OAuth app credentials here

$oauth_config = [
    'google' => [
        'client_id' => '1059043152331-f24i9e3rpgv1c10h1fpel2aqgn5tfev2.apps.googleusercontent.com',
        'client_secret' => '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',
        'redirect_uri' => 'http://localhost:8000/auth/oauth_callback_simple.php?provider=google',
        'scope' => 'openid email profile',
        'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'user_info_url' => 'https://www.googleapis.com/oauth2/v2/userinfo'
    ],
    'linkedin' => [
        'client_id' => 'YOUR_LINKEDIN_CLIENT_ID',
        'client_secret' => 'YOUR_LINKEDIN_CLIENT_SECRET',
        'redirect_uri' => 'http://localhost/PROPLEDGER/auth/oauth_callback.php?provider=linkedin',
        'scope' => 'r_liteprofile r_emailaddress',
        'auth_url' => 'https://www.linkedin.com/oauth/v2/authorization',
        'token_url' => 'https://www.linkedin.com/oauth/v2/accessToken',
        'user_info_url' => 'https://api.linkedin.com/v2/people/~:(id,firstName,lastName,profilePicture(displayImage~:playableStreams))'
    ],
    'facebook' => [
        'client_id' => 'YOUR_FACEBOOK_APP_ID',
        'client_secret' => 'YOUR_FACEBOOK_APP_SECRET',
        'redirect_uri' => 'http://localhost/PROPLEDGER/auth/oauth_callback.php?provider=facebook',
        'scope' => 'email public_profile',
        'auth_url' => 'https://www.facebook.com/v18.0/dialog/oauth',
        'token_url' => 'https://graph.facebook.com/v18.0/oauth/access_token',
        'user_info_url' => 'https://graph.facebook.com/me?fields=id,name,email,picture'
    ]
];

// OAuth Helper Functions
function generateOAuthState($provider) {
    global $pdo;
    
    $state = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Clean up expired states
    $pdo->exec("DELETE FROM oauth_states WHERE expires_at < NOW()");
    
    // Store new state
    $stmt = $pdo->prepare("INSERT INTO oauth_states (state_token, provider, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$state, $provider, $expires_at]);
    
    return $state;
}

function validateOAuthState($state, $provider) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id FROM oauth_states WHERE state_token = ? AND provider = ? AND expires_at > NOW()");
    $stmt->execute([$state, $provider]);
    $result = $stmt->fetch();
    
    if ($result) {
        // Delete used state
        $pdo->prepare("DELETE FROM oauth_states WHERE state_token = ?")->execute([$state]);
        return true;
    }
    
    return false;
}

function getOAuthAuthUrl($provider, $state = null) {
    global $oauth_config;
    
    if (!isset($oauth_config[$provider])) {
        throw new Exception("Unsupported OAuth provider: $provider");
    }
    
    $config = $oauth_config[$provider];
    
    if (!$state) {
        $state = generateOAuthState($provider);
    }
    
    $params = [
        'client_id' => $config['client_id'],
        'redirect_uri' => $config['redirect_uri'],
        'scope' => $config['scope'],
        'response_type' => 'code',
        'state' => $state
    ];
    
    return $config['auth_url'] . '?' . http_build_query($params);
}

// Instructions for setup
if (basename($_SERVER['PHP_SELF']) == 'oauth_config.php') {
    echo "<h1>🔧 OAuth Configuration Setup</h1>";
    echo "<p><strong>To complete OAuth setup, you need to:</strong></p>";
    echo "<ol>";
    echo "<li><strong>Google OAuth:</strong><br>";
    echo "   - Go to <a href='https://console.developers.google.com/' target='_blank'>Google Cloud Console</a><br>";
    echo "   - Create a new project or select existing<br>";
    echo "   - Enable Google+ API<br>";
    echo "   - Create OAuth 2.0 credentials<br>";
    echo "   - Add redirect URI: <code>http://localhost/PROPLEDGER/auth/oauth_callback.php?provider=google</code><br>";
    echo "   - Copy Client ID and Client Secret to this file</li><br>";
    
    echo "<li><strong>LinkedIn OAuth:</strong><br>";
    echo "   - Go to <a href='https://www.linkedin.com/developers/' target='_blank'>LinkedIn Developers</a><br>";
    echo "   - Create a new app<br>";
    echo "   - Add redirect URI: <code>http://localhost/PROPLEDGER/auth/oauth_callback.php?provider=linkedin</code><br>";
    echo "   - Copy Client ID and Client Secret to this file</li><br>";
    
    echo "<li><strong>Facebook OAuth:</strong><br>";
    echo "   - Go to <a href='https://developers.facebook.com/' target='_blank'>Facebook Developers</a><br>";
    echo "   - Create a new app<br>";
    echo "   - Add Facebook Login product<br>";
    echo "   - Add redirect URI: <code>http://localhost/PROPLEDGER/auth/oauth_callback.php?provider=facebook</code><br>";
    echo "   - Copy App ID and App Secret to this file</li>";
    echo "</ol>";
    
    echo "<p><strong>Current Configuration Status:</strong></p>";
    foreach ($oauth_config as $provider => $config) {
        $status = (strpos($config['client_id'], 'YOUR_') === 0) ? '❌ Not Configured' : '✅ Configured';
        echo "<p><strong>" . ucfirst($provider) . ":</strong> $status</p>";
    }
}
?>
