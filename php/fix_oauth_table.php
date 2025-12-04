<?php
// Fix OAuth states table creation
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to the propledger_db database
    $pdo = new PDO("mysql:host=$host;dbname=propledger_db", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Fixing OAuth States Table...</h2>";
    
    // Drop existing table if it exists (to recreate properly)
    try {
        $pdo->exec("DROP TABLE IF EXISTS oauth_states");
        echo "<p>✅ Dropped existing oauth_states table (if any)</p>";
    } catch (Exception $e) {
        echo "<p>ℹ️ No existing oauth_states table to drop</p>";
    }
    
    // Create oauth_states table with proper structure
    $createTable = "CREATE TABLE oauth_states (
        id INT AUTO_INCREMENT PRIMARY KEY,
        state_token VARCHAR(255) NOT NULL UNIQUE,
        provider VARCHAR(50) NOT NULL,
        redirect_url VARCHAR(500) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        INDEX idx_state_token (state_token),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($createTable);
    echo "<p>✅ Created oauth_states table successfully</p>";
    
    // Verify table structure
    $stmt = $pdo->query("DESCRIBE oauth_states");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test inserting a state token
    $testState = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $stmt = $pdo->prepare("INSERT INTO oauth_states (state_token, provider, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$testState, 'google', $expiresAt]);
    
    echo "<p>✅ Test state token inserted successfully</p>";
    
    // Clean up test token
    $pdo->prepare("DELETE FROM oauth_states WHERE state_token = ?")->execute([$testState]);
    echo "<p>✅ Test token cleaned up</p>";
    
    echo "<h3>🎉 OAuth States Table Fixed!</h3>";
    echo "<p>The oauth_states table is now properly created and ready for OAuth authentication.</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li><a href='../html/login.html'>Test OAuth Login</a></li>";
    echo "<li><a href='../html/signup.html'>Test OAuth Signup</a></li>";
    echo "<li><a href='../html/debug_oauth.html'>Run OAuth Debug Tool</a></li>";
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<h2>❌ Database Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Possible solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP MySQL is running</li>";
    echo "<li>Check if propledger_db database exists</li>";
    echo "<li>Verify database connection settings in config/database.php</li>";
    echo "</ul>";
}
?>
