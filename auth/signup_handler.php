<?php
session_start();
require_once '../config/database.php';

// CORS headers for Next.js - Allow any localhost port
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (preg_match('/^http:\/\/localhost(:\d+)?$/', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check if this is an OAuth signup (provider is present)
    $isOAuth = isset($input['provider']) && !empty($input['provider']);
    
    // Validate required fields based on user type and auth method
    if ($input['userType'] === 'agent') {
        $required_fields = ['fullName', 'email', 'userType'];
        // For agents, require additional fields only if not OAuth
        if (!$isOAuth) {
            $required_fields = array_merge($required_fields, ['phone', 'licenseNumber', 'experience', 'specialization', 'city', 'password']);
        }
    } else {
        // For investors, require only these fields
        $required_fields = ['fullName', 'email', 'userType'];
        if (!$isOAuth) {
            $required_fields = array_merge($required_fields, ['phone', 'country', 'password', 'terms']);
        }
    }
    
    foreach ($required_fields as $field) {
        if (empty($input[$field]) && $field !== 'terms') {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Validate password length (only for non-OAuth users)
    if (!$isOAuth && strlen($input['password']) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }
    
    // Check if terms are accepted (only for non-agent non-OAuth users)
    if ($input['userType'] !== 'agent' && !$isOAuth && !$input['terms']) {
        throw new Exception('You must accept the terms and conditions');
    }
    
    // For agents, check agent agreement (skip for OAuth)
    if ($input['userType'] === 'agent' && !$isOAuth && !isset($input['agreeAgent'])) {
        throw new Exception('Agent agreement field is missing');
    }
    if ($input['userType'] === 'agent' && !$isOAuth && !$input['agreeAgent']) {
        throw new Exception('You must agree to the agent guidelines');
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    if ($stmt->fetch()) {
        // User already exists, return success for OAuth flow
        if ($isOAuth) {
            echo json_encode([
                'success' => true,
                'message' => 'User already registered',
                'redirect' => 'html/dashboard.html'
            ]);
            exit;
        }
        throw new Exception('Email already registered');
    }
    
    // Hash password (for OAuth users, use a random secure password)
    $password_to_hash = $isOAuth ? bin2hex(random_bytes(32)) : $input['password'];
    $password_hash = password_hash($password_to_hash, PASSWORD_DEFAULT);
    
    // Insert new user
    if ($input['userType'] === 'agent') {
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, email, phone, country, user_type, password_hash, newsletter_subscribed) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $input['fullName'],
            $input['email'],
            $input['phone'] ?? '',
            $input['city'] ?? '', // Use city as country for agents
            $input['userType'],
            $password_hash,
            false // Agents don't subscribe to newsletter by default
        ]);
        
        $user_id = $pdo->lastInsertId();
        
        // Insert agent-specific data (only if non-OAuth)
        if (!$isOAuth) {
            $stmt = $pdo->prepare("
                INSERT INTO agents (user_id, license_number, experience, specialization, city, agency, phone, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')
            ");
            
            $stmt->execute([
                $user_id,
                $input['licenseNumber'],
                $input['experience'],
                $input['specialization'],
                $input['city'],
                $input['agency'] ?? null,
                $input['phone']
            ]);
        }
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, email, phone, country, user_type, password_hash, newsletter_subscribed) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $newsletter = isset($input['newsletter']) ? $input['newsletter'] : false;
        
        $stmt->execute([
            $input['fullName'],
            $input['email'],
            $input['phone'] ?? '',
            $input['country'] ?? 'Pakistan',
            $input['userType'],
            $password_hash,
            $newsletter
        ]);
        
        $user_id = $pdo->lastInsertId();
    }
    
    // Create session
    $session_token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $session_token, $expires_at]);
    
    // Set session cookie
    setcookie('propledger_session', $session_token, strtotime('+30 days'), '/', '', false, true);
    
    // Get user info for response
    $stmt = $pdo->prepare("SELECT full_name, email, user_type FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully!',
        'user_id' => $user_id,
        'user' => [
            'id' => $user_id,
            'name' => $user_info['full_name'],
            'email' => $user_info['email'],
            'type' => $user_info['user_type']
        ],
        'redirect' => 'html/dashboard.html'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
