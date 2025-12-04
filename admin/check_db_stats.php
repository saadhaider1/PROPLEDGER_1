<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/plain');

echo "=== Database Stats Check ===\n\n";

try {
    // Count Users
    $stmtUser = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmtUser->fetch()['count'];
    echo "Total Users in DB: $userCount\n";
    
    // Count Agents
    $stmtAgent = $pdo->query("SELECT COUNT(*) as count FROM agents");
    $agentCount = $stmtAgent->fetch()['count'];
    echo "Total Agents in DB: $agentCount\n";
    
    // Show some sample users
    echo "\n--- Sample Users ---\n";
    $users = $pdo->query("SELECT id, email, user_type FROM users LIMIT 5")->fetchAll();
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Email: {$user['email']}, Type: {$user['user_type']}\n";
    }
    
    // Check agents table structure
    echo "\n--- Agents Table Structure ---\n";
    $columns = $pdo->query("DESCRIBE agents")->fetchAll();
    foreach ($columns as $col) {
        echo "{$col['Field']} ({$col['Type']})\n";
    }
    
    // Show some sample agents
    echo "\n--- Sample Agents ---\n";
    $agents = $pdo->query("SELECT * FROM agents LIMIT 5")->fetchAll();
    foreach ($agents as $agent) {
        echo "Agent: " . print_r($agent, true) . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString();
}
?>
