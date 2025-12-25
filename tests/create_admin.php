<?php
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Auth.php';

use App\Core\Database;

// Explicitly load .env
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

// Ensure DB connection uses port 3306 internally (Docker) but 3307 externally if script is run from host?
// The script runs inside the container or host. 
// If host: DB_PORT=3307. If container: DB_PORT=3306.
// Let's rely on standard Database class connection but override if needed.

try {
    $db = Database::getInstance()->getConnection();
    
    $email = 'admin@nautilus.local';
    $password = 'password';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Update password and ensure active
        echo "Updating existing admin user...\n";
        $stmt = $db->prepare("UPDATE users SET password_hash = ?, is_active = 1 WHERE id = ?");
        $stmt->execute([$hash, $user['id']]);
        $userId = $user['id'];
        echo "Password updated and user activated.\n";
    } else {
        // Create user
        echo "Creating new admin user...\n";
        // Fetch role ID first
        $stmt = $db->prepare("SELECT id FROM roles WHERE name = 'Admin'");
        $stmt->execute();
        $role = $stmt->fetch();
        $roleId = $role['id'] ?? 1; // Fallback

        $stmt = $db->prepare("INSERT INTO users (email, password_hash, role_id, is_active, created_at) VALUES (?, ?, ?, 1, NOW())");
        $stmt->execute([$email, $hash, $roleId]);
        $userId = $db->lastInsertId();
        echo "User created with ID: $userId\n";
    }

    // Ensure user_roles entry exists
    $stmt = $db->prepare("SELECT id FROM roles WHERE name = 'Admin'");
    $stmt->execute();
    $role = $stmt->fetch();
    if ($role) {
        $roleId = $role['id'];
        echo "Ensuring Admin role (ID: $roleId) in user_roles...\n";
        // Check existence
        $stmt = $db->prepare("SELECT * FROM user_roles WHERE user_id = ? AND role_id = ?");
        $stmt->execute([$userId, $roleId]);
        if (!$stmt->fetch()) {
             $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
             $stmt->execute([$userId, $roleId]);
             echo "Role assigned.\n";
        } else {
            echo "Role already assigned.\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
