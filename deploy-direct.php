<?php
/**
 * Direct Deployment Script - No routing required
 * Run from command line: php deploy-direct.php
 * Or access via browser (temporarily move to public folder)
 */

// Define base path
define('BASE_PATH', __DIR__);

// Load environment
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Load Database class
require __DIR__ . '/app/Core/Database.php';

echo "========================================\n";
echo "  Nautilus Database Deployment\n";
echo "========================================\n\n";

try {
    $db = App\Core\Database::getInstance();
    echo "✓ Database connection established\n\n";

    // Run migrations
    echo "=== MIGRATIONS ===\n";
    $migrations = [
        '039_create_customer_enhanced_tables.sql',
        '040_create_cash_drawer_system.sql',
        '041_add_customer_certifications.sql'
    ];

    $migrationDir = __DIR__ . '/database/migrations';
    $migrationsRun = 0;
    $migrationsSkipped = 0;

    foreach ($migrations as $migrationFile) {
        echo "Checking: {$migrationFile}... ";

        // Check if already run
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM migrations WHERE filename = ? AND status = 'completed'");
        $stmt->execute([$migrationFile]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            echo "✓ Already completed\n";
            $migrationsSkipped++;
            continue;
        }

        // Find migration file
        $files = glob($migrationDir . '/*' . $migrationFile);
        if (empty($files)) {
            echo "✗ File not found!\n";
            continue;
        }

        $fullPath = $files[0];

        try {
            // Execute migration
            $sql = file_get_contents($fullPath);
            $db->exec($sql);

            // Record migration
            $stmt = $db->prepare("
                INSERT INTO migrations (filename, status, executed_at)
                VALUES (?, 'completed', NOW())
                ON DUPLICATE KEY UPDATE status = 'completed', executed_at = NOW()
            ");
            $stmt->execute([$migrationFile]);

            echo "✓ Completed!\n";
            $migrationsRun++;

        } catch (Exception $e) {
            echo "✗ Failed: " . $e->getMessage() . "\n";

            // Record failure
            $stmt = $db->prepare("
                INSERT INTO migrations (filename, status, error_message, executed_at)
                VALUES (?, 'failed', ?, NOW())
                ON DUPLICATE KEY UPDATE status = 'failed', error_message = ?, executed_at = NOW()
            ");
            $stmt->execute([$migrationFile, $e->getMessage(), $e->getMessage()]);
        }
    }

    echo "\nMigrations run: {$migrationsRun}, Skipped: {$migrationsSkipped}\n\n";

    // Seed certification agencies
    echo "=== SEEDERS ===\n";
    echo "Checking certification agencies... ";
    $stmt = $db->query("SELECT COUNT(*) as count FROM certification_agencies");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] == 0) {
        echo "\nSeeding certification agencies... ";
        try {
            $seederFile = __DIR__ . '/database/seeders/certification_agencies.sql';
            if (file_exists($seederFile)) {
                $sql = file_get_contents($seederFile);
                $db->exec($sql);
                echo "✓ Done\n";
            } else {
                echo "✗ File not found\n";
            }
        } catch (Exception $e) {
            echo "✗ Failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✓ Already seeded ({$result['count']} agencies)\n";
    }

    echo "Checking cash drawers... ";
    $stmt = $db->query("SELECT COUNT(*) as count FROM cash_drawers");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] == 0) {
        echo "\nSeeding cash drawers and tags... ";
        try {
            $seederFile = __DIR__ . '/database/seeders/cash_drawers.sql';
            if (file_exists($seederFile)) {
                $sql = file_get_contents($seederFile);
                $db->exec($sql);
                echo "✓ Done\n";
            } else {
                echo "✗ File not found\n";
            }
        } catch (Exception $e) {
            echo "✗ Failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✓ Already seeded ({$result['count']} drawers)\n";
    }

    // Statistics
    echo "\n=== DATABASE SUMMARY ===\n";
    $stats = [
        'Migrations Completed' => $db->query("SELECT COUNT(*) FROM migrations WHERE status = 'completed'")->fetchColumn(),
        'Certification Agencies' => $db->query("SELECT COUNT(*) FROM certification_agencies")->fetchColumn(),
        'Certifications' => $db->query("SELECT COUNT(*) FROM certifications")->fetchColumn(),
        'Customer Tags' => $db->query("SELECT COUNT(*) FROM customer_tags")->fetchColumn(),
        'Cash Drawers' => $db->query("SELECT COUNT(*) FROM cash_drawers")->fetchColumn(),
        'Customers' => $db->query("SELECT COUNT(*) FROM customers")->fetchColumn(),
        'Products' => $db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    ];

    foreach ($stats as $label => $count) {
        echo sprintf("%-25s: %d\n", $label, $count);
    }

    echo "\n========================================\n";
    echo "  ✓ DEPLOYMENT COMPLETE!\n";
    echo "========================================\n\n";

    echo "Next steps:\n";
    echo "- Visit https://pangolin.local/store/dashboard\n";
    echo "- Check Cash Drawer at /store/cash-drawer\n";
    echo "- Manage Customer Tags at /store/customers/tags\n\n";

} catch (Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
