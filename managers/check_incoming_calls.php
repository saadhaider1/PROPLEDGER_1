<?php
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit;
}

try {
    // Check for incoming calls (status = 'calling')
    // Join with users table to get caller details
    $stmt = $pdo->prepare("
        SELECT vc.*, u.full_name as caller_name, u.profile_picture_url 
        FROM video_calls vc 
        JOIN users u ON vc.caller_id = u.id 
        WHERE vc.receiver_id = ? AND vc.status = 'calling' 
        ORDER BY vc.created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$_GET['user_id']]);
    $call = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($call) {
        echo json_encode(['success' => true, 'call' => $call]);
    } else {
        echo json_encode(['success' => true, 'call' => null]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
