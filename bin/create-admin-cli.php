#!/usr/bin/env php
<?php
/**
 * Create Admin User - CLI Version
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';

if (!file_exists(__DIR__ . '/.env')) {
    die('ERROR: .env file not found.');
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "========================================\n";
echo "Nautilus Admin User Creation\n";
echo "========================================\n\n";

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Check if admin already exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE email = 'admin@nautilus.local'");
    $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

    if ($exists) {
        echo "⚠ Admin user already exists!\n\n";
        echo "Email: admin@nautilus.local\n";
        echo "Password: password\n\n";
        echo "Login at: http://localhost/nautilus/public/store/login\n\n";
        exit(0);
    }

    // Get admin role ID
    $stmt = $pdo->query("SELECT id FROM roles WHERE name = 'admin' LIMIT 1");
    $adminRole = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$adminRole) {
        die("ERROR: Admin role not found. Run: php seed-roles-simple.php\n");
    }

    // Create admin user
    $passwordHash = password_hash('password', PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO users (role_id, email, password_hash, first_name, last_name, is_active, created_at)
        VALUES (?, ?, ?, ?, ?, 1, NOW())
    ");

    $stmt->execute([
        $adminRole['id'],
        'admin@nautilus.local',
        $passwordHash,
        'Admin',
        'User'
    ]);

    echo "✓ Admin user created successfully!\n\n";
    echo "===========================================\n";
    echo "Login Credentials:\n";
    echo "===========================================\n";
    echo "URL:      http://localhost/nautilus/public/store/login\n";
    echo "Email:    admin@nautilus.local\n";
    echo "Password: password\n";
    echo "===========================================\n\n";
    echo "⚠ IMPORTANT: Change this password after your first login!\n\n";

} catch (PDOException $e) {
    die('ERROR: ' . $e->getMessage() . "\n");
}
