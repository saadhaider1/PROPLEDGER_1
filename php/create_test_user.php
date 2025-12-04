<?php
// Create a test user for login testing
require_once 'config/database.php';

header('Content-Type: text/plain');

echo "=== Creating Test User ===\n\n";

try {
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
    $stmt->execute(['test@propledger.com']);
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo "⚠ User already exists:\n";
        echo "  Email: test@propledger.com\n";
        echo "  ID: " . $existing['id'] . "\n\n";
        echo "You can login with:\n";
        echo "  Email: test@propledger.com\n";
        echo "  Password: password123\n";
        exit;
    }
    
    // Create test user
    $password_hash = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, phone, country, user_type, password_hash, is_active, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        'Test User',
        'test@propledger.com',
        '+92-300-1234567',
        'Pakistan',
        'investor',
        $password_hash,
        1
    ]);
    
    $userId = $pdo->lastInsertId();
    
    echo "✓ Test user created successfully!\n\n";
    echo "Login Credentials:\n";
    echo "  Email: test@propledger.com\n";
    echo "  Password: password123\n";
    echo "  User ID: $userId\n";
    echo "  Type: Investor\n\n";
    
    // Create test agent
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['agent@propledger.com']);
    $existingAgent = $stmt->fetch();
    
    if (!$existingAgent) {
        $agent_password_hash = password_hash('agent123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, email, phone, country, user_type, password_hash, is_active, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            'Test Agent',
            'agent@propledger.com',
            '+92-300-7654321',
            'Pakistan',
            'agent',
            $agent_password_hash,
            1
        ]);
        
        $agentId = $pdo->lastInsertId();
        
        echo "✓ Test agent created successfully!\n\n";
        echo "Agent Login Credentials:\n";
        echo "  Email: agent@propledger.com\n";
        echo "  Password: agent123\n";
        echo "  User ID: $agentId\n";
        echo "  Type: Agent\n\n";
        
        // Create agent profile if agents table exists
        try {
            $stmt = $pdo->prepare("
                INSERT INTO agents (user_id, license_number, experience, specialization, city, phone, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $agentId,
                'LIC-' . str_pad($agentId, 6, '0', STR_PAD_LEFT),
                '5+ years',
                'Residential',
                'Islamabad',
                '+92-300-7654321',
                'approved'
            ]);
            
            echo "✓ Agent profile created in agents table\n";
        } catch (Exception $e) {
            echo "⚠ Could not create agent profile (agents table may not exist): " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Setup Complete ===\n";
    echo "\nYou can now login at: http://localhost/PROPLEDGER/html/login.html\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nMake sure:\n";
    echo "1. XAMPP MySQL is running\n";
    echo "2. Database 'propledger_db' exists\n";
    echo "3. Run php/setup_database.php first if needed\n";
}
?>
