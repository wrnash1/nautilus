<?php
// public/reset_admin.php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\Encryption;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configuration
$email = $_GET['email'] ?? 'admin@nautilus.local';
$password = $_GET['password'] ?? 'password123';
$firstName = 'Admin';
$lastName = 'User';

echo "<h1>Admin Reset Tool</h1>";

try {
    // 1. Check if user exists
    $user = Database::fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    if ($user) {
        // Update existing
        Database::query("UPDATE users SET password_hash = ? WHERE id = ?", [$passwordHash, $user['id']]);
        echo "Updated password_hash for existing user: $email<br>";
        $userId = $user['id'];
    } else {
        // Create new
        Database::query(
            "INSERT INTO users (email, password_hash, first_name, last_name, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())", 
            [$email, $passwordHash, $firstName, $lastName]
        );
        $userId = Database::lastInsertId();
        echo "Created new user: $email<br>";
    }
    
    // 2. Ensure Role is Admin (ID 1 usually)
    // Check if role exists
    $role = Database::fetchOne("SELECT id FROM roles WHERE name = 'Admin'");
    if (!$role) {
        // Create Admin role if missing
        Database::query("INSERT INTO roles (name, description, created_at) VALUES ('Admin', 'Super Administrator', NOW())");
        $roleId = Database::lastInsertId();
        echo "Created Admin role (ID: $roleId)<br>";
    } else {
        $roleId = $role['id'];
    }
    
    // Assign role
    $userRole = Database::fetchOne("SELECT * FROM user_roles WHERE user_id = ? AND role_id = ?", [$userId, $roleId]);
    if (!$userRole) {
        Database::query("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)", [$userId, $roleId]);
        echo "Assigned Admin role to user.<br>";
    } else {
        echo "User already has Admin role.<br>";
    }

    // 3. Grant Permissions
    $permissions = ['customers.view', 'customers.edit', 'customers.delete'];
    foreach ($permissions as $code) {
        // Check/Insert Permission
        // Assuming 'name' matches the permission code/slug
        $perm = Database::fetchOne("SELECT id FROM permissions WHERE name = ?", [$code]);
        if (!$perm) {
            $parts = explode('.', $code);
            $module = $parts[0];
            $displayName = ucfirst($module) . ' ' . ucfirst($parts[1] ?? 'Access');
            
            Database::query(
                "INSERT INTO permissions (name, display_name, module, description, created_at) VALUES (?, ?, ?, ?, NOW())", 
                [$code, $displayName, $module, 'Auto-generated permission']
            );
            $permId = Database::lastInsertId();
            echo "Created permission: $code<br>";
        } else {
            $permId = $perm['id'];
        }

        // Assign to Role
        $rolePerm = Database::fetchOne("SELECT * FROM role_permissions WHERE role_id = ? AND permission_id = ?", [$roleId, $permId]);
        if (!$rolePerm) {
            Database::query("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)", [$roleId, $permId]);
            echo "Granted $code to Admin.<br>";
        }
    }
    
    echo "<h3>Success! Login with:</h3>";
    echo "Email: <strong>$email</strong><br>";
    echo "Password: <strong>$password</strong><br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
