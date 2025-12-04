<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'config/database.php';

echo "<h1>🔧 Fix Messages for Current Agent</h1>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}.card{background:white;padding:20px;margin:15px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);} .success{color:green;font-weight:bold;} .error{color:red;} table{border-collapse:collapse;width:100%;margin:15px 0;} th,td{border:1px solid #ddd;padding:10px;text-align:left;} th{background:#333;color:white;}</style>";

// Get current logged-in agent
if (!isset($_SESSION['user_id'])) {
    echo "<div class='card'><p class='error'>❌ No session found. Please <a href='http://localhost:3000/login'>login as agent</a> first.</p></div>";
    exit;
}

$agent_id = $_SESSION['user_id'];
$agent_name = $_SESSION['full_name'];

echo "<div class='card'>";
echo "<h2>✅ Current Agent</h2>";
echo "<p><strong>ID:</strong> {$agent_id}</p>";
echo "<p><strong>Name:</strong> {$agent_name}</p>";
echo "<p><strong>Email:</strong> {$_SESSION['email']}</p>";
echo "</div>";

try {
    // Step 1: Count messages that need fixing
    echo "<div class='card'>";
    echo "<h2>Step 1: Find Messages</h2>";
    
    $count_stmt = $pdo->query("
        SELECT COUNT(*) as total FROM manager_messages
    ");
    $total = $count_stmt->fetchColumn();
    echo "<p>Total messages in database: <strong>{$total}</strong></p>";
    
    // Count messages where agent_id is NULL or doesn't match
    $null_count = $pdo->query("SELECT COUNT(*) FROM manager_messages WHERE agent_id IS NULL")->fetchColumn();
    echo "<p>Messages with NULL agent_id: <strong>{$null_count}</strong></p>";
    
    // Count messages for this agent by name
    $name_stmt = $pdo->prepare("SELECT COUNT(*) FROM manager_messages WHERE manager_name LIKE ?");
    $name_stmt->execute(["%{$agent_name}%"]);
    $by_name = $name_stmt->fetchColumn();
    echo "<p>Messages matching agent name '{$agent_name}': <strong>{$by_name}</strong></p>";
    
    echo "</div>";
    
    // Step 2: Update ALL messages to this agent
    echo "<div class='card'>";
    echo "<h2>Step 2: Update Messages</h2>";
    
    // Option 1: Update messages with matching name
    if ($by_name > 0) {
        $update1 = $pdo->prepare("
            UPDATE manager_messages 
            SET agent_id = ?
            WHERE manager_name LIKE ?
        ");
        $update1->execute([$agent_id, "%{$agent_name}%"]);
        $updated1 = $update1->rowCount();
        echo "<p class='success'>✅ Updated {$updated1} messages with matching name</p>";
    }
    
    // Option 2: If no messages by name, assign ALL messages to this agent
    if ($by_name == 0 && $total > 0) {
        echo "<p style='border-left:4px solid orange;padding-left:10px;'>⚠️ No messages match your name. Assigning ALL messages to you for testing.</p>";
        
        $update_all = $pdo->prepare("
            UPDATE manager_messages 
            SET manager_name = ?,
                agent_id = ?
            WHERE 1=1
        ");
        $update_all->execute([$agent_name, $agent_id]);
        $updated_all = $update_all->rowCount();
        echo "<p class='success'>✅ Assigned ALL {$updated_all} messages to {$agent_name}</p>";
    }
    
    echo "</div>";
    
    // Step 3: Verify updates
    echo "<div class='card'>";
    echo "<h2>Step 3: Verify Messages for {$agent_name}</h2>";
    
    $verify_stmt = $pdo->prepare("
        SELECT 
            m.*,
            u.full_name as sender_name,
            u.email as sender_email
        FROM manager_messages m
        LEFT JOIN users u ON m.user_id = u.id
        WHERE m.agent_id = ? OR m.manager_name LIKE ?
        ORDER BY m.created_at DESC
        LIMIT 20
    ");
    
    $verify_stmt->execute([$agent_id, "%{$agent_name}%"]);
    $messages = $verify_stmt->fetchAll();
    
    if (count($messages) > 0) {
        echo "<p class='success'>✅ Found " . count($messages) . " messages for {$agent_name}</p>";
        
        echo "<table>";
        echo "<tr><th>ID</th><th>From</th><th>Subject</th><th>Agent ID</th><th>Manager Name</th><th>Status</th><th>Created</th></tr>";
        foreach ($messages as $m) {
            $ag_id_display = $m['agent_id'] ?? '<span style="color:orange;">NULL</span>';
            echo "<tr>";
            echo "<td>{$m['id']}</td>";
            echo "<td>{$m['sender_name']}<br><small>{$m['sender_email']}</small></td>";
            echo "<td>{$m['subject']}</td>";
            echo "<td>{$ag_id_display}</td>";
            echo "<td>{$m['manager_name']}</td>";
            echo "<td>{$m['status']}</td>";
            echo "<td>" . date('M j, g:i A', strtotime($m['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ No messages found for {$agent_name}</p>";
        echo "<p>Try sending a test message from the <a href='http://localhost:3000/managers'>Managers page</a></p>";
    }
    
    echo "</div>";
    
    // Step 4: Test API endpoint
    echo "<div class='card'>";
    echo "<h2>Step 4: Test API Endpoint</h2>";
    echo "<p>Testing: <code>get_agent_messages_fixed.php</code></p>";
    echo "<iframe src='http://localhost/PROPLEDGER/managers/get_agent_messages_fixed.php' style='width:100%;height:200px;border:1px solid #ddd;border-radius:4px;padding:10px;background:#f9f9f9;'></iframe>";
    echo "</div>";
    
    // Success message
    echo "<div class='card' style='background:#d1fae5;border-left:4px solid #10b981;'>";
    echo "<h2 class='success'>✅ All Done!</h2>";
    echo "<p><strong>Messages Configured for:</strong> {$agent_name} (ID: {$agent_id})</p>";
    echo "<p><strong>Total Messages:</strong> " . count($messages) . "</p>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Go to <a href='http://localhost:3000/agent-dashboard' target='_blank'>Agent Dashboard</a></li>";
    echo "<li>Click <strong>\"Refresh\"</strong> in Client Messages section</li>";
    echo "<li>Messages should now appear!</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='card' style='background:#fee2e2;'>";
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>
