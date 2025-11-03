<?php
/**
 * Manual Admin User Creation Script
 * Use this if simple-install.php fails but database migrations are complete
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load dependencies
require __DIR__ . '/../vendor/autoload.php';

if (!file_exists(__DIR__ . '/../.env')) {
    die('ERROR: .env file not found.');
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('ERROR: Database connection failed: ' . $e->getMessage());
}

// Check if admin already exists
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE email = 'admin@nautilus.local'");
$exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

if ($exists) {
    echo '<h1>Admin User Already Exists</h1>';
    echo '<p>The admin user already exists in the database.</p>';
    echo '<p><strong>Email:</strong> admin@nautilus.local</p>';
    echo '<p><strong>Password:</strong> password</p>';
    echo '<p><a href="/store/login">Go to Login</a></p>';
    exit;
}

// Get admin role ID
$stmt = $pdo->query("SELECT id FROM roles WHERE name = 'admin' LIMIT 1");
$adminRole = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adminRole) {
    die('ERROR: Admin role not found in database. Please ensure initial data has been seeded.');
}

// Create admin user
$passwordHash = password_hash('password', PASSWORD_BCRYPT);

$stmt = $pdo->prepare("
    INSERT INTO users (role_id, email, password_hash, first_name, last_name, is_active, created_at)
    VALUES (?, ?, ?, ?, ?, 1, NOW())
");

try {
    $stmt->execute([
        $adminRole['id'],
        'admin@nautilus.local',
        $passwordHash,
        'Admin',
        'User'
    ]);

    echo '<h1>✓ Admin User Created Successfully!</h1>';
    echo '<div style="background:#d4edda;border:1px solid #c3e6cb;padding:20px;margin:20px 0;border-radius:5px;">';
    echo '<h2>Login Credentials</h2>';
    echo '<p><strong>URL:</strong> <a href="/store/login">https://pangolin.local/store/login</a></p>';
    echo '<p><strong>Email:</strong> admin@nautilus.local</p>';
    echo '<p><strong>Password:</strong> password</p>';
    echo '</div>';
    echo '<p><strong>Important:</strong> Change this password after your first login!</p>';
    echo '<hr>';
    echo '<p><small>You can now delete this file: /public/create-admin.php</small></p>';

} catch (PDOException $e) {
    echo '<h1>Error Creating Admin User</h1>';
    echo '<p style="color:red;">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>
