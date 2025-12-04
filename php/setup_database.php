<?php
// Database setup script for PROPLEDGER
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS propledger_db");
    echo "Database 'propledger_db' created successfully<br>";
    
    // Use the database
    $pdo->exec("USE propledger_db");
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        phone VARCHAR(20) NOT NULL,
        country VARCHAR(100) NOT NULL,
        user_type ENUM('investor', 'property_owner', 'agent', 'developer') NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        newsletter_subscribed BOOLEAN DEFAULT FALSE,
        wallet_address VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE
    )";
    
    $pdo->exec($sql);
    echo "Table 'users' created successfully<br>";
    
    // Create sessions table for login management
    $sql = "CREATE TABLE IF NOT EXISTS user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        session_token VARCHAR(255) NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "Table 'user_sessions' created successfully<br>";
    
    // Create properties table for future use
    $sql = "CREATE TABLE IF NOT EXISTS properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        location VARCHAR(255) NOT NULL,
        price DECIMAL(15,2) NOT NULL,
        token_price DECIMAL(10,2) NOT NULL,
        total_tokens INT NOT NULL,
        available_tokens INT NOT NULL,
        property_type VARCHAR(100) NOT NULL,
        owner_id INT NOT NULL,
        image_url VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (owner_id) REFERENCES users(id)
    )";
    
    $pdo->exec($sql);
    echo "Table 'properties' created successfully<br>";
    
    // Create manager_messages table
    $sql = "CREATE TABLE IF NOT EXISTS manager_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        manager_name VARCHAR(255) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        priority ENUM('normal', 'high', 'urgent') DEFAULT 'normal',
        status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        replied_at TIMESTAMP NULL,
        reply_message TEXT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "Table 'manager_messages' created successfully<br>";
    
    echo "<br><strong>Database setup completed successfully!</strong><br>";
    echo "<a href='index.html'>Go to PROPLEDGER Homepage</a>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
