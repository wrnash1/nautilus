<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Database Connection
try {
    $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_DATABASE'];
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database Connected Successfully.<br>\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check Users
echo "<h3>Users</h3>";
$stmt = $pdo->query("SELECT id, username, email, password_hash, tenant_id FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    echo "No users found.<br>\n";
} else {
    echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Email</th><th>Hash</th><th>Tenant ID</th><th>Password Verify ('password')</th></tr>";
    foreach ($users as $user) {
        $verify = password_verify('password', $user['password_hash']) ? 'TRUE' : 'FALSE';
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>" . substr($user['password_hash'], 0, 10) . "...</td>";
        echo "<td>{$user['tenant_id']}</td>";
        echo "<td>{$verify}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check Roles
echo "<h3>Roles</h3>";
$stmt = $pdo->query("SELECT * FROM roles");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($roles, true) . "</pre>";


// Check User Roles
echo "<h3>User Roles</h3>";
$stmt = $pdo->query("SELECT ur.user_id, u.email, r.role_name FROM user_roles ur JOIN users u ON ur.user_id = u.id JOIN roles r ON ur.role_id = r.id");
$userRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($userRoles, true) . "</pre>";
