<?php
ini_set('display_errors', 1);
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$logFile = __DIR__ . '/reset_debug.txt';
function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, $msg . "\n", FILE_APPEND);
}

logMsg("Starting Reset Script");

// Load Env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    // Force localhost for CLI/Agent execution if needed
    $_ENV['DB_HOST'] = '127.0.0.1';
    $_ENV['DB_USERNAME'] = 'root'; // Use root to avoid permission issues from localhost
    $_ENV['DB_PASSWORD'] = 'Frogman09!';
    $_ENV['DB_DATABASE'] = 'nautilus'; // Ensure DB name is set
    logMsg("Dotenv loaded. Forced DB_HOST=127.0.0.1 and Root Creds");
} catch (Exception $e) {
    logMsg("Dotenv Error: " . $e->getMessage());
}

$email = 'admin@example.com';
$password = 'password';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $db = Database::getInstance()->getConnection();
    logMsg("DB Connected");
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $db->prepare("UPDATE users SET password_hash = ?, is_active = 1 WHERE id = ?")->execute([$hash, $user['id']]);
        logMsg("Password updated and set active for existing user ID: " . $user['id']);
        $userId = $user['id'];
    } else {
        $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, is_active, created_at) VALUES (?, ?, ?, 1, NOW())");
        $stmt->execute(['Admin', $email, $hash]);
        $userId = $db->lastInsertId();
        logMsg("Created new user ID: " . $userId);
    }

    // Assign Admin Role (assuming id 1 or name 'Admin')
    $stmt = $db->prepare("SELECT id FROM roles WHERE name IN ('Admin', 'Administrator') LIMIT 1");
    $stmt->execute();
    $role = $stmt->fetch();

    if ($role) {
        $stmt = $db->prepare("SELECT * FROM user_roles WHERE user_id = ? AND role_id = ?");
        $stmt->execute([$userId, $role['id']]);
        if (!$stmt->fetch()) {
            $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$userId, $role['id']]);
            logMsg("Assigned Admin role ID: " . $role['id']);
        } else {
            logMsg("User already has Admin role.");
        }
    } else {
        logMsg("WARNING: Admin role not found!");
        // Create Admin role if missing
        $db->exec("INSERT INTO roles (role_name, name, description, created_at) VALUES ('Admin', 'Admin', 'Administrator', NOW())");
        $roleId = $db->lastInsertId();
        $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$userId, $roleId]);
        logMsg("Created and assigned Admin role.");
    }
    
    // Check Permissions (optional but good)
    // Assuming 000_CORE_SCHEMA seeded permissions but let's check one
    $stmt = $db->prepare("SELECT id FROM permissions WHERE permission_code = 'pos.create'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        logMsg("Warning: pos.create permission missing!");
    }

} catch (Exception $e) {
    logMsg("Error: " . $e->getMessage());
}
logMsg("Done");
