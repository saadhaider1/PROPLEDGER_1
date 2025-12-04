<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/plain');

echo "--- Debugging Admin Login ---\n";

$email = 'admin@propledger.com';
$password = 'psd12345';

// 1. Check User
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "ERROR: Admin user '$email' NOT FOUND in database.\n";
} else {
    echo "SUCCESS: Admin user found.\n";
    echo "ID: " . $user['id'] . "\n";
    echo "User Type: " . $user['user_type'] . "\n";
    echo "Is Active: " . $user['is_active'] . "\n";
    
    // 2. Verify Password
    if (password_verify($password, $user['password_hash'])) {
        echo "SUCCESS: Password '$password' matches the hash in DB.\n";
    } else {
        echo "ERROR: Password '$password' DOES NOT match the hash.\n";
        echo "Stored Hash: " . $user['password_hash'] . "\n";
        echo "New Hash for '$password': " . password_hash($password, PASSWORD_DEFAULT) . "\n";
        
        // Fix it automatically
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $update->execute([$newHash, $user['id']]);
        echo "FIXED: Password hash has been updated to match '$password'. Try logging in again.\n";
    }
}
?>
