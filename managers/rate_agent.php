<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['agent_id']) || !isset($input['user_id']) || !isset($input['rating'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$agent_id = (int)$input['agent_id'];
$user_id = (int)$input['user_id'];
$rating = (int)$input['rating'];

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit;
}

// Additional security: Check if the user trying to rate is an agent
try {
    $userCheckStmt = $pdo->prepare("SELECT user_type, type, account_type FROM users WHERE id = ?");
    $userCheckStmt->execute([$user_id]);
    $userData = $userCheckStmt->fetch();
    
    if (!$userData) {
        echo json_encode(['success' => false, 'message' => 'Invalid user']);
        exit;
    }
    
    // Prevent agents from rating other agents - multiple field checks
    if ($userData['user_type'] === 'agent' || 
        $userData['type'] === 'agent' || 
        $userData['account_type'] === 'agent') {
        echo json_encode(['success' => false, 'message' => 'Access denied: Agents cannot rate other agents']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error during user verification']);
    exit;
}

try {
    // Check if user already rated this agent
    $checkStmt = $pdo->prepare("SELECT id FROM agent_ratings WHERE agent_id = ? AND user_id = ?");
    $checkStmt->execute([$agent_id, $user_id]);
    
    if ($checkStmt->fetch()) {
        // Update existing rating
        $updateStmt = $pdo->prepare("UPDATE agent_ratings SET rating = ?, created_at = NOW() WHERE agent_id = ? AND user_id = ?");
        $updateStmt->execute([$rating, $agent_id, $user_id]);
    } else {
        // Insert new rating
        $insertStmt = $pdo->prepare("INSERT INTO agent_ratings (agent_id, user_id, rating, created_at) VALUES (?, ?, ?, NOW())");
        $insertStmt->execute([$agent_id, $user_id, $rating]);
    }
    
    // Calculate new average rating
    $avgStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM agent_ratings WHERE agent_id = ?");
    $avgStmt->execute([$agent_id]);
    $avgResult = $avgStmt->fetch();
    
    $newAvgRating = round($avgResult['avg_rating'], 1);
    
    // Update agent's rating in agents table
    $updateAgentStmt = $pdo->prepare("UPDATE agents SET rating = ? WHERE user_id = ?");
    $updateAgentStmt->execute([$newAvgRating, $agent_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Rating submitted successfully',
        'new_rating' => $newAvgRating,
        'total_ratings' => $avgResult['total_ratings']
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
