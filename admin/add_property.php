<?php
// admin/add_property.php
// CORS headers
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (preg_match('/^http:\/\/localhost(:\d+)?$/', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: http://localhost:3000');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

session_start();

// Auth Check
$isAuthenticated = false;
$ownerId = 1; // Default fallback

// 1. Check Standalone Session
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $isAuthenticated = true;
} 
// 2. Check Main Login Cookie
elseif (isset($_COOKIE['propledger_session'])) {
    $token = $_COOKIE['propledger_session'];
    $stmt = $pdo->prepare("
        SELECT u.id, u.user_type 
        FROM user_sessions s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.session_token = ? AND s.expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user && $user['user_type'] === 'admin') {
        $isAuthenticated = true;
        $ownerId = $user['id'];
    }
}

if (!$isAuthenticated) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Handle File Upload
$uploadDir = '../images/properties/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$imageUrl = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $fileName;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $imageUrl = '/images/properties/' . $fileName;
    }
}

// Get POST data
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$location = $_POST['location'] ?? '';
$price = $_POST['price'] ?? 0;
$tokenPrice = $_POST['token_price'] ?? 0;
$totalTokens = $_POST['total_tokens'] ?? 0;
$propertyType = $_POST['property_type'] ?? 'standard'; // standard, investment, crowdfunding
$availableTokens = $totalTokens; // Initially all available

// Owner ID is set during auth check 

try {
    $stmt = $pdo->prepare("INSERT INTO properties (title, description, location, price, token_price, total_tokens, available_tokens, property_type, owner_id, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $location, $price, $tokenPrice, $totalTokens, $availableTokens, $propertyType, $ownerId, $imageUrl]);

    echo json_encode(['success' => true, 'message' => 'Property added successfully', 'id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
