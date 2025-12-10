#!/usr/bin/env php
<?php
/**
 * Seed Roles and Permissions
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';

if (!file_exists(__DIR__ . '/.env')) {
    die('ERROR: .env file not found.');
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Seeding roles and permissions...\n\n";

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/database/seeders/roles_and_permissions.sql');

    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }

        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            // Ignore duplicate entry errors
            if ($e->getCode() != 23000) {
                echo "Warning: " . $e->getMessage() . "\n";
            }
        }
    }

    // Display results
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
    $roleCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM permissions");
    $permCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM role_permissions");
    $rpCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo "✓ Roles: $roleCount\n";
    echo "✓ Permissions: $permCount\n";
    echo "✓ Role-Permission mappings: $rpCount\n";
    echo "\nSeeding complete!\n";

} catch (PDOException $e) {
    die('ERROR: ' . $e->getMessage() . "\n");
}
