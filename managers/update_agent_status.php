<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['user_id']) || !isset($input['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$user_id = (int)$input['user_id'];
$status = $input['status'];

// Validate status
if (!in_array($status, ['online', 'offline'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    // Check if user is an agent
    $userCheckStmt = $pdo->prepare("SELECT user_type, type FROM users WHERE id = ?");
    $userCheckStmt->execute([$user_id]);
    $userData = $userCheckStmt->fetch();
    
    if (!$userData) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    if ($userData['user_type'] !== 'agent' && $userData['type'] !== 'agent') {
        echo json_encode(['success' => false, 'message' => 'User is not an agent']);
        exit;
    }
    
    // Update agent online status and last_active timestamp
    $updateStmt = $pdo->prepare("
        UPDATE agents 
        SET online_status = ?, last_active = NOW() 
        WHERE user_id = ?
    ");
    $updateStmt->execute([$status, $user_id]);
    
    // If no agent record exists, create one
    if ($updateStmt->rowCount() === 0) {
        // Get user details for agent creation
        $userStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
        $userStmt->execute([$user_id]);
        $userDetails = $userStmt->fetch();
        
        if ($userDetails) {
            $insertStmt = $pdo->prepare("
                INSERT INTO agents (user_id, full_name, email, online_status, last_active, status, created_at) 
                VALUES (?, ?, ?, ?, NOW(), 'pending', NOW())
            ");
            $insertStmt->execute([
                $user_id, 
                $userDetails['full_name'], 
                $userDetails['email'], 
                $status
            ]);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully',
        'status' => $status,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
