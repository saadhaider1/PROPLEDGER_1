<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit;
}

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Get specific agent data by user ID
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
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
            a.online_status,
            a.last_active,
            a.created_at
        FROM agents a
        JOIN users u ON a.user_id = u.id
        WHERE u.id = ?
    ");
    
    $stmt->execute([$_GET['user_id']]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($agent) {
        echo json_encode([
            'success' => true,
            'agent' => $agent
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Agent not found'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
