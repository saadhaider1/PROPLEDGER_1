<?php
require_once 'config/database.php';

echo "Creating agent_ratings table...\n";

try {
    // Create agent_ratings table
    $sql = "CREATE TABLE IF NOT EXISTS agent_ratings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        agent_id INT NOT NULL,
        user_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_agent_rating (agent_id, user_id)
    )";
    
    $pdo->exec($sql);
    echo "✅ agent_ratings table created successfully!\n";
    
    // Check if table exists and show structure
    $result = $pdo->query("DESCRIBE agent_ratings");
    echo "\nTable structure:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']}: {$row['Type']}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error creating table: " . $e->getMessage() . "\n";
}
?>
