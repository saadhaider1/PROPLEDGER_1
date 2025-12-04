<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config/database.php';

echo "<h1>🔍 Messaging System Status</h1>";
echo "<style>body { font-family: Arial; padding: 20px; background: #f5f5f5; } .card { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); } table { border-collapse: collapse; width: 100%; margin: 15px 0; } th, td { border: 1px solid #ddd; padding: 10px; text-align: left; } th { background: #333; color: white; } .success { color: green; } .error { color: red; } .warning { color: orange; }</style>";

// 1. Check session
echo "<div class='card'>";
echo "<h2>1. Current Session Status</h2>";
if (isset($_SESSION['user_id'])) {
    echo "<p class='success'>✅ Session active - User ID: {$_SESSION['user_id']}</p>";
    
    if (isset($_SESSION['full_name'])) echo "<p>Name: {$_SESSION['full_name']}</p>";
    if (isset($_SESSION['email'])) echo "<p>Email: {$_SESSION['email']}</p>";
    if (isset($_SESSION['user_type'])) echo "<p>Type: {$_SESSION['user_type']}</p>";
    
    echo "<details><summary>View all session data</summary><pre>" . print_r($_SESSION, true) . "</pre></details>";
} else {
    echo "<p class='error'>❌ No active session. Please <a href='http://localhost:3000/login'>login</a> first.</p>";
}
echo "</div>";

//2. Check messages in database
echo "<div class='card'>";
echo "<h2>2. Messages in Database</h2>";
try {
    $messages = $pdo->query("
        SELECT 
            m.*,
            u.full_name as sender_name,
            u.email as sender_email
        FROM manager_messages m
        LEFT JOIN users u ON m.user_id = u.id
        ORDER BY m.created_at DESC
        LIMIT 20
    ")->fetchAll();
    
    if (count($messages) > 0) {
        echo "<p class='success'>✅ Found " . count($messages) . " messages</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>From</th><th>To Agent</th><th>Agent ID</th><th>Subject</th><th>Status</th><th>Created</th></tr>";
        foreach ($messages as $m) {
            echo "<tr>";
            echo "<td>{$m['id']}</td>";
            echo "<td>{$m['sender_name']}</td>";
            echo "<td>{$m['manager_name']}</td>";
            echo "<td>" . ($m['agent_id'] ?? '<span class="warning">NULL</span>') . "</td>";
            echo "<td>{$m['subject']}</td>";
            echo "<td>{$m['status']}</td>";
            echo "<td>" . date('M j, g:i A', strtotime($m['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠ No messages found. <a href='http://localhost/PROPLEDGER/php/fix_messaging_system.php'>Create test messages</a></p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 3. Test API endpoint directly
if (isset($_SESSION['user_id'])) {
    echo "<div class='card'>";
    echo "<h2>3. Test get_agent_messages_fixed.php</h2>";
    echo "<iframe src='http://localhost/PROPLEDGER/managers/get_agent_messages_fixed.php' style='width:100%; height:300px; border:1px solid #ddd; border-radius:4px;'></iframe>";
    echo "</div>";
}

echo "<div class='card'>";
echo "<h2>Quick Actions</h2>";
echo "<ul>";
echo "<li><a href='http://localhost:3000/login'>Go to Login Page</a></li>";
echo "<li><a href='http://localhost:3000/agent-dashboard'>Go to Agent Dashboard</a></li>";
echo "<li><a href='http://localhost/PROPLEDGER/php/fix_messaging_system.php'>Run Fix Messaging System</a></li>";
echo "<li><a href='http://localhost/PROPLEDGER/auth/check_session.php'>Check PHP Session</a></li>";
echo "</ul>";
echo "</div>";
?>
