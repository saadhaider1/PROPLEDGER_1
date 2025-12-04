<?php
// Quick setup script to ensure messaging system works
require_once '../config/database.php';

echo "<h2>🚀 Quick Messaging System Setup</h2>";

try {
    // Create manager_messages table if it doesn't exist
    $createTable = "CREATE TABLE IF NOT EXISTS manager_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        agent_id INT NULL,
        manager_name VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        priority ENUM('normal', 'high', 'urgent') DEFAULT 'normal',
        status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
        sender_type ENUM('user', 'agent') DEFAULT 'user',
        receiver_type ENUM('user', 'agent') DEFAULT 'agent',
        reply_message TEXT NULL,
        replied_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_manager_name (manager_name),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($createTable);
    echo "<p>✅ manager_messages table created/verified</p>";
    
    // Check if we have any users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    
    if ($userCount == 0) {
        echo "<p>⚠️ No users found. Creating test users...</p>";
        
        // Create test user
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, user_type) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Test User', 'testuser@propledger.com', password_hash('testpass', PASSWORD_DEFAULT), 'investor']);
        $userId = $pdo->lastInsertId();
        echo "<p>✅ Created test user (ID: $userId)</p>";
        
        // Create test agent
        $stmt->execute(['Test Agent', 'testagent@propledger.com', password_hash('testpass', PASSWORD_DEFAULT), 'agent']);
        $agentId = $pdo->lastInsertId();
        echo "<p>✅ Created test agent (ID: $agentId)</p>";
        
        // Create agents table entry for the test agent
        $createAgentsTable = "CREATE TABLE IF NOT EXISTS agents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            license_number VARCHAR(100) NULL,
            experience VARCHAR(50) NULL,
            specialization VARCHAR(100) NULL,
            city VARCHAR(100) NULL,
            agency VARCHAR(255) NULL,
            phone VARCHAR(20) NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            commission_rate DECIMAL(5,2) DEFAULT 2.50,
            total_sales DECIMAL(15,2) DEFAULT 0.00,
            rating DECIMAL(3,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createAgentsTable);
        
        $stmt = $pdo->prepare("INSERT INTO agents (user_id, license_number, experience, specialization, city, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$agentId, 'TEST-001', '5+ years', 'residential', 'Karachi', 'approved']);
        echo "<p>✅ Created agent profile</p>";
        
    } else {
        echo "<p>✅ Found $userCount users in database</p>";
    }
    
    // Check if we have any messages
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM manager_messages");
    $messageCount = $stmt->fetch()['count'];
    
    if ($messageCount == 0) {
        echo "<p>⚠️ No messages found. Creating test messages...</p>";
        
        // Get a test user and agent
        $stmt = $pdo->query("SELECT id FROM users WHERE user_type = 'investor' LIMIT 1");
        $testUser = $stmt->fetch();
        
        $stmt = $pdo->query("SELECT u.id, u.full_name FROM users u WHERE u.user_type = 'agent' LIMIT 1");
        $testAgent = $stmt->fetch();
        
        if ($testUser && $testAgent) {
            // Create test messages
            $stmt = $pdo->prepare("INSERT INTO manager_messages (user_id, manager_name, subject, message, priority, status) VALUES (?, ?, ?, ?, ?, ?)");
            
            $messages = [
                ['Test Message 1', 'Hello, I am interested in property investment. Can you help me?', 'normal', 'unread'],
                ['Property Inquiry', 'I would like to know more about blockchain real estate investments.', 'high', 'unread'],
                ['Investment Question', 'What is the minimum investment amount for PROP tokens?', 'normal', 'read']
            ];
            
            foreach ($messages as $msg) {
                $stmt->execute([$testUser['id'], $testAgent['full_name'], $msg[0], $msg[1], $msg[2], $msg[3]]);
            }
            
            echo "<p>✅ Created " . count($messages) . " test messages</p>";
        }
    } else {
        echo "<p>✅ Found $messageCount messages in database</p>";
    }
    
    // Show current status
    echo "<h3>📊 Current Status:</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'agent'");
    $agentCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'investor'");
    $investorCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM manager_messages");
    $totalMessages = $stmt->fetch()['count'];
    
    echo "<ul>";
    echo "<li>Agents: $agentCount</li>";
    echo "<li>Investors: $investorCount</li>";
    echo "<li>Messages: $totalMessages</li>";
    echo "</ul>";
    
    echo "<h3>🔗 Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='../html/agent-dashboard.html'>Test Agent Dashboard</a></li>";
    echo "<li><a href='../html/dashboard.html'>Test User Dashboard</a></li>";
    echo "<li><a href='../managers/simple_test_messages.php'>Test Messages API</a></li>";
    echo "</ol>";
    
    echo "<p><strong>Test Login Credentials:</strong></p>";
    echo "<p>Agent: testagent@propledger.com / testpass</p>";
    echo "<p>User: testuser@propledger.com / testpass</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
