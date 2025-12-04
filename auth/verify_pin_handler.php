<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Disable error display to prevent breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $pin = $input['pin'] ?? '';

    if (empty($email) || empty($pin)) {
        throw new Exception('Email and PIN are required');
    }

    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()");
    $stmt->execute([$email, $pin]);
    
    if ($stmt->fetch()) {
        echo json_encode(['valid' => true, 'message' => 'PIN verified']);
    } else {
        http_response_code(400);
        echo json_encode(['valid' => false, 'message' => 'Invalid or expired PIN']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
