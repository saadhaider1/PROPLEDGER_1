<?php
require_once 'config/database.php';

try {
    // 1. Update Check Constraint
    // Note: Modifying check constraints in MySQL can be tricky depending on version. 
    // We'll try to drop and re-add. If it fails, we might need to just alter the column definition.
    // However, for safety in this environment, let's just try to insert. If it fails due to constraint, we know we need to fix it.
    // Actually, let's just run the ALTER command.
    
    // Check if 'admin' is already allowed (hard to check directly in SQL without complex queries)
    // We'll just try to ALTER.
    try {
        $pdo->exec("ALTER TABLE users DROP CHECK users_chk_1");
    } catch (Exception $e) {
        // Ignore if check doesn't exist or name is different (MariaDB vs MySQL naming)
    }
    
    // Re-add constraint with admin
    $pdo->exec("ALTER TABLE users ADD CONSTRAINT users_chk_1 CHECK (user_type IN ('investor', 'property_owner', 'agent', 'developer', 'admin'))");
    
    echo "Schema updated successfully.<br>";

    // 2. Insert Admin User
    $email = 'admin@propledger.com';
    $password = 'psd12345';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, country, user_type, password_hash, is_active) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE password_hash = ?");
    $stmt->execute(['System Admin', $email, '+0000000000', 'System', 'admin', $hash, 1, $hash]);
    
    echo "Admin user created/updated successfully.<br>";
    echo "Email: $email<br>";
    echo "Password: $password<br>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
