<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "<h1>Admin Reset Tool</h1>";

// Load Env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    echo "<p>Dotenv loaded.</p>";
} catch (Exception $e) {
    echo "<p>Dotenv Error: " . $e->getMessage() . "</p>";
}

$email = 'admin@example.com';
$password = 'password';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Database class logic uses $_ENV or $_SERVER, which should work in web
    $db = Database::getInstance()->getConnection();
    echo "<p>DB Connected.</p>";
    
    // List All Users
    echo "<h2>Existing Users</h2><ul>";
    $stmt = $db->query("SELECT id, username, email FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) {
        echo "<li>ID: {$u['id']} - Username: {$u['username']} - Email: {$u['email']}</li>";
    }
    echo "</ul>";

    // Try to find Admin by username OR email
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, 'Admin']);
    $user = $stmt->fetch();

    if ($user) {
        // Update found user
        $db->prepare("UPDATE users SET password_hash = ?, is_active = 1, email = ? WHERE id = ?")->execute([$hash, $email, $user['id']]);
        echo "<p>Success: Updated existing Admin user ID: " . $user['id'] . " (Set email to $email)</p>";
        $userId = $user['id'];
    } else {
        // Create new
        $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, is_active, created_at) VALUES (?, ?, ?, 1, NOW())");
        $stmt->execute(['Admin', $email, $hash]);
        $userId = $db->lastInsertId();
        echo "<p>Success: Created new user ID: " . $userId . "</p>";
    }

    // Assign Admin Role
    $stmt = $db->prepare("SELECT id FROM roles WHERE name IN ('Admin', 'Administrator') LIMIT 1");
    $stmt->execute();
    $role = $stmt->fetch();

    if ($role) {
        $stmt = $db->prepare("SELECT * FROM user_roles WHERE user_id = ? AND role_id = ?");
        $stmt->execute([$userId, $role['id']]);
        if (!$stmt->fetch()) {
            $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$userId, $role['id']]);
            echo "<p>Success: Assigned Admin role ID: " . $role['id'] . "</p>";
        } else {
            echo "<p>User already has Admin role.</p>";
        }
    } else {
        echo "<p>Warning: Admin role not found!</p>";
    }
    
    // Verify permissions
    $stmt = $db->prepare("SELECT id FROM permissions WHERE permission_code = 'pos.create'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        echo "<p style='color:red'>Critical Warning: 'pos.create' permission missing from DB!</p>";
    } else {
        echo "<p>Permissions check passed.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
