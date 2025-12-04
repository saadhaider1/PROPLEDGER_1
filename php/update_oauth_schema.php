<?php
// Update database schema to support OAuth authentication
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to the propledger_db database
    $pdo = new PDO("mysql:host=$host;dbname=propledger_db", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Updating Database Schema for OAuth...</h2>";
    
    // Add OAuth columns to users table
    $alterQueries = [
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS oauth_provider VARCHAR(50) NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS oauth_id VARCHAR(255) NULL", 
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture_url VARCHAR(500) NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified BOOLEAN DEFAULT FALSE",
        "ALTER TABLE users MODIFY COLUMN password_hash VARCHAR(255) NULL" // Make password optional for OAuth users
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
    
    // Create OAuth states table for security
    $oauthStateTable = "CREATE TABLE IF NOT EXISTS oauth_states (
        id INT AUTO_INCREMENT PRIMARY KEY,
        state_token VARCHAR(255) NOT NULL UNIQUE,
        provider VARCHAR(50) NOT NULL,
        redirect_url VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        INDEX idx_state_token (state_token),
        INDEX idx_expires (expires_at)
    )";
    
    try {
        $pdo->exec($oauthStateTable);
        echo "<p>✅ Created oauth_states table</p>";
    } catch (PDOException $e) {
        echo "<p>ℹ️ OAuth states table already exists</p>";
    }
    
    echo "<h3>✅ Database Schema Updated for OAuth!</h3>";
    echo "<p>The database now supports both traditional and OAuth authentication.</p>";
    echo "<p><strong>New Features Added:</strong></p>";
    echo "<ul>";
    echo "<li>OAuth provider tracking (Google, LinkedIn, Facebook)</li>";
    echo "<li>OAuth user ID storage</li>";
    echo "<li>Profile picture URL support</li>";
    echo "<li>Email verification status</li>";
    echo "<li>Optional password for OAuth users</li>";
    echo "<li>OAuth state management for security</li>";
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<h2>❌ Database Connection Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
