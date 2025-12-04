<?php
// Fix Messaging System - Add agent_id column and create test data
require_once '../config/database.php';

echo "\u003ch1\u003eFixing Messaging System\u003c/h1\u003e";

try {
    // 1. Check if agent_id column exists, if not add it
    echo "\u003ch2\u003e1. Checking manager_messages table schema...\u003c/h2\u003e";
    
    $checkColumn = $pdo->query("SHOW COLUMNS FROM manager_messages LIKE 'agent_id'");
    
    if ($checkColumn->rowCount() == 0) {
        echo "\u003cp\u003e✅ Adding agent_id column to manager_messages table...\u003c/p\u003e";
        $pdo->exec("
            ALTER TABLE manager_messages 
            ADD COLUMN agent_id INT DEFAULT NULL AFTER user_id,
            ADD INDEX idx_agent_id (agent_id)
        ");
        echo "\u003cp style='color: green;'\u003e✓ agent_id column added successfully!\u003c/p\u003e";
    } else {
        echo "\u003cp style='color: blue;'\u003e✓ agent_id column already exists.\u003c/p\u003e";
    }
    
    // 2. Get all users
    echo "\u003cbr\u003e\u003ch2\u003e2. Checking users and agents...\u003c/h2\u003e";
    
    $users = $pdo->query("SELECT * FROM users ORDER BY id")->fetchAll();
    echo "\u003cp\u003eFound " . count($users) . " users:\u003c/p\u003e";
    echo "\u003cul\u003e";
    foreach ($users as $user) {
        echo "\u003cli\u003eID: {$user['id']} | Name: {$user['full_name']} | Email: {$user['email']} | Type: {$user['user_type']}\u003c/li\u003e";
    }
    echo "\u003c/ul\u003e";
    
    // 3. Get all agents
    $agents = $pdo->query("SELECT a.*, u.full_name, u.email FROM agents a JOIN users u ON a.user_id = u.id")->fetchAll();
    echo "\u003cp\u003eFound " . count($agents) . " agents:\u003c/p\u003e";
    echo "\u003cul\u003e";
    foreach ($agents as $agent) {
        echo "\u003cli\u003eAgent ID: {$agent['user_id']} | Name: {$agent['full_name']} | Email: {$agent['email']}\u003c/li\u003e";
    }
    echo "\u003c/ul\u003e";
    
    // 4. Create test messages if none exist
    echo "\u003cbr\u003e\u003ch2\u003e3. Checking existing messages...\u003c/h2\u003e";
    
    $messageCount = $pdo->query("SELECT COUNT(*) as count FROM manager_messages")->fetch();
    echo "\u003cp\u003eFound {$messageCount['count']} existing messages.\u003c/p\u003e";
    
    if ($messageCount['count'] == 0 && count($agents) > 0 && count($users) > 0) {
        echo "\u003cp\u003e✅ Creating test messages...\u003c/p\u003e";
        
        // Find an investor (non-agent user)
        $investor = null;
        foreach ($users as $user) {
            if ($user['user_type'] !== 'agent') {
                $investor = $user;
                break;
            }
        }
        
        if ($investor && count($agents) > 0) {
            $agent = $agents[0];
            
            // Create 3 test messages
            $testMessages = [
                [
                    'subject' => 'Property Investment Inquiry',
                    'message' => 'Hello! I am interested in investing in residential properties in Islamabad. Could you guide me on available options?',
                    'priority' => 'normal'
                ],
                [
                    'subject' => 'Portfolio Consultation Request',
                    'message' => 'I would like to schedule a meeting to discuss my real estate investment portfolio. When would be a good time?',
                    'priority' => 'high'
                ],
                [
                    'subject' => 'Urgent: Property Evaluation Needed',
                    'message' => 'I need an urgent evaluation for a property I am considering purchasing. Please respond as soon as possible.',
                    'priority' => 'urgent'
                ]
            ];
            
            foreach ($testMessages as $msg) {
                $stmt = $pdo->prepare("
                    INSERT INTO manager_messages 
                    (user_id, agent_id, manager_name, subject, message, priority, sender_type, receiver_type, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, 'user', 'agent', 'unread', NOW())
                ");
                
                $stmt->execute([
                    $investor['id'],
                    $agent['user_id'],
                    $agent['full_name'],
                    $msg['subject'],
                    $msg['message'],
                    $msg['priority']
                ]);
            }
            
            echo "\u003cp style='color: green;'\u003e✓ Created 3 test messages from {$investor['full_name']} to agent {$agent['full_name']}!\u003c/p\u003e";
        } else {
            echo "\u003cp style='color: orange;'\u003e⚠ Could not create test messages: No suitable investor or agent found.\u003c/p\u003e";
        }
    } else if (count($agents) == 0) {
        echo "\u003cp style='color: red;'\u003e❌ No agents found. Please create an agent account first.\u003c/p\u003e";
    }
    
    // 5. Display all messages
    echo "\u003cbr\u003e\u003ch2\u003e4. Current messages in database:\u003c/h2\u003e";
    
    $messages = $pdo->query("
        SELECT 
            m.*,
            u.full_name as sender_name,
            u.email as sender_email
        FROM manager_messages m
        LEFT JOIN users u ON m.user_id = u.id
        ORDER BY m.created_at DESC
    ")->fetchAll();
    
    if (count($messages) > 0) {
        echo "\u003ctable border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'\u003e";
        echo "\u003ctr style='background: #333; color: white;'\u003e
                \u003cth\u003eID\u003c/th\u003e
                \u003cth\u003eFrom (User)\u003c/th\u003e
                \u003cth\u003eTo (Agent)\u003c/th\u003e
                \u003cth\u003eSubject\u003c/th\u003e
                \u003cth\u003ePriority\u003c/th\u003e
                \u003cth\u003eStatus\u003c/th\u003e
                \u003cth\u003eCreated\u003c/th\u003e
              \u003c/tr\u003e";
        
        foreach ($messages as $msg) {
            $priorityColor = $msg['priority'] == 'urgent' ? 'red' : ($msg['priority'] == 'high' ? 'orange' : 'black');
            $statusColor = $msg['status'] == 'unread' ? 'blue' : ($msg['status'] == 'replied' ? 'green' : 'gray');
            
            echo "\u003ctr\u003e
                    \u003ctd\u003e{$msg['id']}\u003c/td\u003e
                    \u003ctd\u003e{$msg['sender_name']} ({$msg['sender_email']})\u003c/td\u003e
                    \u003ctd\u003e{$msg['manager_name']}\u003c/td\u003e
                    \u003ctd\u003e{$msg['subject']}\u003c/td\u003e
                    \u003ctd style='color: $priorityColor; font-weight: bold;'\u003e{$msg['priority']}\u003c/td\u003e
                    \u003ctd style='color: $statusColor; font-weight: bold;'\u003e{$msg['status']}\u003c/td\u003e
                    \u003ctd\u003e{$msg['created_at']}\u003c/td\u003e
                  \u003c/tr\u003e";
        }
        echo "\u003c/table\u003e";
    } else {
        echo "\u003cp\u003eNo messages found.\u003c/p\u003e";
    }
    
    echo "\u003cbr\u003e\u003ch2 style='color: green;'\u003e✅ Messaging System Check Complete!\u003c/h2\u003e";
    echo "\u003cp\u003e\u003cstrong\u003eNext Steps:\u003c/strong\u003e\u003c/p\u003e";
    echo "\u003col\u003e";
    echo "\u003cli\u003eGo to \u003ca href='http://localhost:3000/login'\u003ehttp://localhost:3000/login\u003c/a\u003e\u003c/li\u003e";
    echo "\u003cli\u003eLogin as an agent (agent@propledger.com / password)\u003c/li\u003e";
    echo "\u003cli\u003eGo to agent dashboard to see messages\u003c/li\u003e";
    echo "\u003cli\u003eOr login as investor and go to \u003ca href='http://localhost:3000/managers'\u003eManagers page\u003c/a\u003e to send messages\u003c/li\u003e";
    echo "\u003c/ol\u003e";
    
} catch (Exception $e) {
    echo "\u003cp style='color: red;'\u003e❌ Error: " . $e->getMessage() . "\u003c/p\u003e";
    echo "\u003cpre\u003e" . $e->getTraceAsString() . "\u003c/pre\u003e";
}
?>
