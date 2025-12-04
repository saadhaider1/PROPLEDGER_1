<?php
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['caller_id']) || !isset($data['receiver_id']) || !isset($data['room_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Check if there's already an active call for this pair
    $stmt = $pdo->prepare("SELECT id FROM video_calls WHERE caller_id = ? AND receiver_id = ? AND status IN ('calling', 'active')");
    $stmt->execute([$data['caller_id'], $data['receiver_id']]);
    
    if ($stmt->rowCount() > 0) {
        // Update existing call
        $stmt = $pdo->prepare("UPDATE video_calls SET room_id = ?, status = 'calling', updated_at = CURRENT_TIMESTAMP WHERE caller_id = ? AND receiver_id = ? AND status IN ('calling', 'active')");
        $stmt->execute([$data['room_id'], $data['caller_id'], $data['receiver_id']]);
    } else {
        // Create new call
        $stmt = $pdo->prepare("INSERT INTO video_calls (caller_id, receiver_id, room_id, status) VALUES (?, ?, ?, 'calling')");
        $stmt->execute([$data['caller_id'], $data['receiver_id'], $data['room_id']]);
    }

    echo json_encode(['success' => true, 'message' => 'Call initiated']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
