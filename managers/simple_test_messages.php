<?php
// Simple test without authentication to check basic functionality
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Test database connection
    $stmt = $pdo->query("SELECT 1");
    
    // Check if manager_messages table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'manager_messages'");
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        echo json_encode([
            'success' => false,
            'message' => 'manager_messages table does not exist',
            'debug' => 'Need to create the table first'
        ]);
        exit;
    }
    
    // Get all messages (no authentication check)
    $stmt = $pdo->query("
        SELECT 
            m.id, 
            m.user_id,
            m.manager_name, 
            m.subject, 
            m.message, 
            m.priority, 
            m.status, 
            m.created_at,
            u.full_name as sender_name,
            u.email as sender_email
        FROM manager_messages m
        LEFT JOIN users u ON m.user_id = u.id
        ORDER BY m.created_at DESC
        LIMIT 10
    ");
    
    $messages = $stmt->fetchAll();
    
    // Get table structure
    $stmt = $pdo->query("DESCRIBE manager_messages");
    $table_structure = $stmt->fetchAll();
    
    // Get all users
    $stmt = $pdo->query("SELECT id, full_name, email, user_type FROM users LIMIT 5");
    $users = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'message' => 'Database test successful',
        'data' => [
            'total_messages' => count($messages),
            'messages' => $messages,
            'table_structure' => $table_structure,
            'sample_users' => $users
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
