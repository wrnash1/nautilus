<?php
/**
 * Diagnose Migration Failures
 * This script checks which tables were created and which foreign keys are missing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? 3306;
$database = $_ENV['DB_DATABASE'] ?? 'nautilus';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== MIGRATION DIAGNOSIS ===\n\n";

    // Check critical base tables from migration 000 and 001
    echo "--- Base Tables (should exist) ---\n";
    $baseTables = ['tenants', 'roles', 'permissions', 'role_permissions', 'users'];
    foreach ($baseTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->fetch() !== false;
        echo ($exists ? '✓' : '✗') . " $table\n";

        if ($exists) {
            // Check row count
            $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM `$table`");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
            echo "  → $count rows\n";

            // Check structure
            $stmt = $pdo->query("DESCRIBE `$table`");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "  → Columns: " . implode(', ', $columns) . "\n";
        }
    }

    // Check foreign keys on role_permissions
    echo "\n--- Foreign Keys on role_permissions ---\n";
    $stmt = $pdo->query("
        SELECT
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '$database'
          AND TABLE_NAME = 'role_permissions'
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($fks)) {
        echo "✗ No foreign keys found\n";
    } else {
        foreach ($fks as $fk) {
            echo "✓ {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
        }
    }

    // Check if users table has proper foreign keys
    echo "\n--- Foreign Keys on users ---\n";
    $stmt = $pdo->query("
        SELECT
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '$database'
          AND TABLE_NAME = 'users'
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($fks)) {
        echo "✗ No foreign keys found on users table\n";
    } else {
        foreach ($fks as $fk) {
            echo "✓ {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
        }
    }

    // Try to manually create role_permissions if it doesn't exist
    echo "\n--- Attempting Manual Fix ---\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'role_permissions'");
    if (!$stmt->fetch()) {
        echo "role_permissions table missing, attempting to create...\n";
        try {
            $pdo->exec("
                CREATE TABLE `role_permissions` (
                  `role_id` INT UNSIGNED NOT NULL,
                  `permission_id` INT UNSIGNED NOT NULL,
                  PRIMARY KEY (`role_id`, `permission_id`),
                  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
                  FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            echo "✓ Successfully created role_permissions table\n";
        } catch (PDOException $e) {
            echo "✗ Failed to create role_permissions: " . $e->getMessage() . "\n";

            // Check if referenced tables exist and have correct structure
            echo "\n--- Checking Referenced Tables ---\n";

            $stmt = $pdo->query("DESCRIBE roles");
            $rolesColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "roles table structure:\n";
            foreach ($rolesColumns as $col) {
                echo "  - {$col['Field']} {$col['Type']} {$col['Key']}\n";
            }

            $stmt = $pdo->query("DESCRIBE permissions");
            $permsColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "\npermissions table structure:\n";
            foreach ($permsColumns as $col) {
                echo "  - {$col['Field']} {$col['Type']} {$col['Key']}\n";
            }
        }
    } else {
        echo "✓ role_permissions table exists\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
