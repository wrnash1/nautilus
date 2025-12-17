<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Override for CLI if needed to match what I think is correct
$_ENV['DB_HOST'] = '127.0.0.1'; 
// If port 3306 is mapped, this works.

$email = 'admin@nautilus.local';
$password = 'password123';

try {
    $user = Database::fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
    if (!$user) {
        echo "User not found.\n";
        exit;
    }
    
    echo "User found: " . $user['id'] . "\n";
    echo "Hash: " . $user['password'] . "\n"; // Check column name - reset_admin used 'password' in UPDATE but 'password_hash' in INSERT? 
    // Wait, check reset_admin.php again!
    
    if (password_verify($password, $user['password'])) {
        echo "Password Match!\n";
    } else {
        echo "Password Mismatch.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
