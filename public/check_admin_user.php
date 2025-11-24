<?php
define('BASE_PATH', dirname(__DIR__));
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Database;

try {
    $user = Database::fetchOne("SELECT * FROM users WHERE email = 'admin@nautilus.local'");
    if ($user) {
        echo "User found: " . $user['email'] . "\n";
        
        // Check password hash
        $password = 'password';
        if (password_verify($password, $user['password'])) {
            echo "Password 'password' is VALID.\n";
        } else {
            echo "Password 'password' is INVALID.\n";
            // Update password to 'password'
            $hash = password_hash($password, PASSWORD_DEFAULT);
            Database::execute("UPDATE users SET password = ? WHERE id = ?", [$hash, $user['id']]);
            echo "Password reset to 'password'.\n";
        }
    } else {
        echo "User 'admin@nautilus.local' NOT FOUND.\n";
        // Create user
        $hash = password_hash('password', PASSWORD_DEFAULT);
        Database::execute(
            "INSERT INTO users (first_name, last_name, email, password, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())",
            ['Admin', 'User', 'admin@nautilus.local', $hash, 'admin', 1]
        );
        echo "User created with password 'password'.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
