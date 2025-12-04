<?php
echo "Starting database setup...\n";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=propledger_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection established.\n";
    
    // First, update users table to support agent user type
    try {
        $alterSql = "ALTER TABLE users MODIFY COLUMN user_type ENUM('investor', 'agent', 'admin') NOT NULL DEFAULT 'investor'";
        $pdo->exec($alterSql);
        echo "Users table updated to support agent user type!\n";
    } catch (PDOException $e) {
        echo "Users table user_type column already supports agent type or error: " . $e->getMessage() . "\n";
    }
    
    // Drop existing agents table if it exists (disable foreign key checks first)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $dropSql = "DROP TABLE IF EXISTS agents";
    $pdo->exec($dropSql);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Dropped existing agents table if it existed.\n";
    
    // Create agents table
    $sql = "CREATE TABLE agents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        license_number VARCHAR(50) NOT NULL UNIQUE,
        experience VARCHAR(20) NOT NULL,
        specialization VARCHAR(50) NOT NULL,
        city VARCHAR(50) NOT NULL,
        agency VARCHAR(100),
        phone VARCHAR(20) NOT NULL,
        status ENUM('pending', 'approved', 'suspended') DEFAULT 'pending',
        commission_rate DECIMAL(5,2) DEFAULT 0.00,
        rating DECIMAL(3,2) DEFAULT 0.00,
        total_sales DECIMAL(15,2) DEFAULT 0.00,
        online_status ENUM('online', 'offline') DEFAULT 'offline',
        last_active TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "Agents table created successfully!\n";
    
    // Verify table structure
    $result = $pdo->query("DESCRIBE agents");
    echo "Agents table structure:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    echo "Database setup completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
