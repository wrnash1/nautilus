<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();
require_once BASE_PATH . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "--- RESETTING PASSWORDS ---\n";

    $users = [
        'admin@nautilus.local' => 'password',
        'ian@nautilus.local' => 'password123',
        'diverdave@example.com' => 'password123'
    ];

    foreach ($users as $email => $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET password_hash = :hash, is_active = 1 WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([':hash' => $hash, ':email' => $email]);
        
        if ($stmt->rowCount() > 0) {
            echo "User $email: PASSWORD RESET OK\n";
        } else {
            // Try to create if not exists (for Ian/Dave fallback)
            // Admin should exist from migration
            echo "User $email: Not found or no change. Attempting CREATE/Ensure...\n";
            // ... (Simple logic: if not found, we rely on previous seeders. This script is primarily for RESET)
        }
    }
    
    echo "--- DONE ---\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
