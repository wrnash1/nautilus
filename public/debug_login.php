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
    
    echo "--- CHECKING LOGIN FOR ian@nautilus.local ---\n";

    $email = 'ian@nautilus.local';
    $password = 'password123';
    
    $stmt = $conn->prepare("SELECT id, password_hash, is_active FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "User NOT FOUND.\n";
    } else {
        echo "User ID: " . $user['id'] . "\n";
        echo "Active: " . $user['is_active'] . "\n";
        echo "Verify Result: " . (password_verify($password, $user['password_hash']) ? "MATCH" : "FAIL") . "\n";
        
        // Check Role
        $stmt = $conn->prepare("SELECT r.name FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = ?");
        $stmt->execute([$user['id']]);
        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Roles: " . implode(', ', $roles) . "\n";
    }
    
    echo "--- DONE ---\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
