<?php
// Create manager_messages table separately
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to the propledger_db database
    $pdo = new PDO("mysql:host=$host;dbname=propledger_db", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create manager_messages table
    $sql = "CREATE TABLE IF NOT EXISTS manager_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        agent_id INT NULL,
        manager_name VARCHAR(255) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        priority ENUM('normal', 'high', 'urgent') DEFAULT 'normal',
        status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
        sender_type ENUM('user', 'agent') DEFAULT 'user',
        receiver_type ENUM('user', 'agent') DEFAULT 'agent',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        replied_at TIMESTAMP NULL,
        reply_message TEXT NULL,
        is_notification BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "<h2>✅ Success!</h2>";
    echo "<p>Table 'manager_messages' created successfully!</p>";
    echo "<p><a href='managers.html'>Go to Portfolio Managers</a></p>";
    echo "<p><a href='index.html'>Go to Homepage</a></p>";
    
} catch(PDOException $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
