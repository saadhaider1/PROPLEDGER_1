<?php
require_once '../config/database.php';

// CORS headers for Next.js - Allow any localhost port
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (preg_match('/^http:\/\/localhost(:\d+)?$/', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: http://localhost:3000');
}
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // $pdo is already available from database.php
    
    // Get all approved agents with their user information
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.user_id,
            u.full_name,
            u.email,
            a.license_number,
            a.experience,
            a.specialization,
            a.city,
            a.agency,
            a.phone,
            a.status,
            a.commission_rate,
            a.total_sales,
            a.rating,
            a.created_at
        FROM agents a
        JOIN users u ON a.user_id = u.id
        WHERE a.status IN ('approved', 'pending')
        ORDER BY a.status DESC, a.created_at DESC
    ");
    
    $stmt->execute();
    $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'agents' => $agents,
        'count' => count($agents)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
