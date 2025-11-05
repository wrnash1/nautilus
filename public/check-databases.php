<?php
/**
 * Check which databases exist
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

echo "<h2>Database Check</h2><pre>";

try {
    // Connect without specifying database
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306),
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "=== Available Databases ===\n";
    $stmt = $pdo->query("SHOW DATABASES LIKE 'nautilus%'");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($databases)) {
        echo "No nautilus databases found!\n";
    } else {
        foreach ($databases as $db) {
            echo "  - $db\n";

            // Check if it has users table
            try {
                $check = $pdo->query("SELECT COUNT(*) as count FROM `$db`.users");
                $userCount = $check->fetch(PDO::FETCH_ASSOC)['count'];
                echo "    Users: $userCount\n";
            } catch (Exception $e) {
                echo "    (No users table)\n";
            }
        }
    }

    echo "\n=== Quick Fix Options ===\n\n";
    echo "Option A: Update .env to use nautilus_dev\n";
    echo "  Run: sudo sed -i 's/DB_DATABASE=nautilus\$/DB_DATABASE=nautilus_dev/' /var/www/html/nautilus/.env\n\n";

    echo "Option B: Copy admin user to 'nautilus' database\n";
    echo "  This will be done below if nautilus database has tables...\n\n";

    // Check if nautilus database has tables
    if (in_array('nautilus', $databases)) {
        echo "Checking 'nautilus' database structure...\n";
        $tables = $pdo->query("SHOW TABLES FROM nautilus")->fetchAll(PDO::FETCH_COLUMN);
        echo "  Tables found: " . count($tables) . "\n";

        if (count($tables) > 0) {
            echo "  Sample tables: " . implode(', ', array_slice($tables, 0, 5)) . "\n\n";

            // Check if it has the same structure as nautilus_dev
            if (in_array('users', $tables) && in_array('roles', $tables)) {
                echo "✓ Database has users and roles tables\n";
                echo "Creating admin user in 'nautilus' database...\n\n";

                // This would be the fix
                echo "Run this command:\n";
                echo "sudo mysql nautilus -e \"INSERT INTO users (role_id, email, password_hash, first_name, last_name, is_active, created_at) SELECT 1, 'admin@nautilus.local', '\$(php -r \\\"echo password_hash('password', PASSWORD_BCRYPT);\\\")' , 'Admin', 'User', 1, NOW() WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@nautilus.local');\"\n";
            }
        }
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>