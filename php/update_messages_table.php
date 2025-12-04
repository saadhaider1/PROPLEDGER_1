<?php
// Update manager_messages table to add missing columns
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to the propledger_db database
    $pdo = new PDO("mysql:host=$host;dbname=propledger_db", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Updating Messages Table Schema...</h2>";
    
    // Add missing columns to existing table
    $alterQueries = [
        "ALTER TABLE manager_messages ADD COLUMN IF NOT EXISTS agent_id INT NULL",
        "ALTER TABLE manager_messages ADD COLUMN IF NOT EXISTS sender_type ENUM('user', 'agent') DEFAULT 'user'",
        "ALTER TABLE manager_messages ADD COLUMN IF NOT EXISTS receiver_type ENUM('user', 'agent') DEFAULT 'agent'",
        "ALTER TABLE manager_messages ADD COLUMN IF NOT EXISTS is_notification BOOLEAN DEFAULT FALSE"
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $pdo->exec($query);
            echo "<p>✅ Executed: " . htmlspecialchars($query) . "</p>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<p>ℹ️ Column already exists: " . htmlspecialchars($query) . "</p>";
            } else {
                echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
    
    echo "<h3>✅ Database Update Complete!</h3>";
    echo "<p>The manager_messages table has been updated with the required columns.</p>";
    echo "<p><a href='../html/managers.html'>Test Messaging System</a></p>";
    echo "<p><a href='../html/agent-dashboard.html'>Check Agent Dashboard</a></p>";
    echo "<p><a href='../html/dashboard.html'>Check User Dashboard</a></p>";
    
} catch(PDOException $e) {
    echo "<h2>❌ Database Connection Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
