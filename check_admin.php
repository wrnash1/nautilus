<?php
require_once __DIR__ . '/app/Core/Database.php';

$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_PORT'] = '3306';
$_ENV['DB_DATABASE'] = 'nautilus';
$_ENV['DB_USERNAME'] = 'root';
$_ENV['DB_PASSWORD'] = 'Frogman09!';

use App\Core\Database;

try {
    $user = Database::fetchOne("SELECT id, email, password_hash FROM users WHERE email='admin@nautilus.local'");
    echo "User fetch result:\n";
    var_dump($user);
    
    // Verify password
    if ($user) {
        $check = password_verify('admin123', $user['password_hash']);
        echo "Password verify 'admin123': " . ($check ? 'TRUE' : 'FALSE') . "\n";
        
        // Try re-hashing
        $newHash = password_hash('admin123', PASSWORD_DEFAULT);
    echo "Generated new hash: $newHash\n";
    if (password_verify('admin123', $newHash)) {
        echo "Immediate verification SUCCESS.\n";
    } else {
        echo "Immediate verification FAILED.\n";
    }

    // The previous line with $updateStmt was a syntax error and is removed.
    // The echo below was also part of the syntax error and is removed.
    
    // Update password if verify failed
    if (!$check) {
            Database::query("UPDATE users SET password_hash = ? WHERE id = ?", [$newHash, $user['id']]);
            echo "Updated password.\n";
        }
    } else {
        echo "User not found!\n";
        // Check if any users exist
        $count = Database::fetchOne("SELECT COUNT(*) as c FROM users")['c'];
        echo "Total users: $count\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
