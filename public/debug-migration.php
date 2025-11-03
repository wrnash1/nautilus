<?php
/**
 * Debug Migration Status
 * Shows which migration is failing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<h2>Migration Status</h2>\n";

    // Check if migrations table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'migrations'");
    if (!$stmt->fetch()) {
        echo "<p>Migrations table does not exist yet.</p>\n";
        exit;
    }

    // Get all migrations from database
    $stmt = $pdo->query("SELECT * FROM migrations ORDER BY id");
    $executedMigrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Executed Migrations: " . count($executedMigrations) . "</h3>\n";
    echo "<pre>\n";
    foreach ($executedMigrations as $migration) {
        echo sprintf("%3d: %s (batch %d)\n",
            $migration['id'],
            $migration['migration'],
            $migration['batch']
        );
    }
    echo "</pre>\n";

    // Get all migration files
    $migrationsDir = __DIR__ . '/../database/migrations';
    $files = glob($migrationsDir . '/*.sql');
    sort($files);

    $executedNames = array_column($executedMigrations, 'migration');
    $pendingMigrations = [];

    foreach ($files as $file) {
        $filename = basename($file);
        if (!in_array($filename, $executedNames)) {
            $pendingMigrations[] = $filename;
        }
    }

    echo "<h3>Pending Migrations: " . count($pendingMigrations) . "</h3>\n";
    echo "<pre>\n";
    foreach ($pendingMigrations as $migration) {
        echo "- $migration\n";
    }
    echo "</pre>\n";

    // If there are pending migrations, try to run the first one and show the error
    if (count($pendingMigrations) > 0) {
        $nextMigration = $pendingMigrations[0];
        $nextMigrationPath = $migrationsDir . '/' . $nextMigration;

        echo "<h3>Testing Next Migration: $nextMigration</h3>\n";

        $sql = file_get_contents($nextMigrationPath);

        // Try to execute with mysqli
        $mysqli = new mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD'],
            $_ENV['DB_DATABASE'],
            $_ENV['DB_PORT'] ?? 3306
        );

        $mysqli->set_charset("utf8mb4");

        if (!$mysqli->multi_query($sql)) {
            echo "<pre style='color:red;'>";
            echo "ERROR: " . $mysqli->error . "\n";
            echo "ERROR NO: " . $mysqli->errno . "\n";
            echo "</pre>";
        } else {
            // Clear all result sets
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());

            if ($mysqli->error) {
                echo "<pre style='color:red;'>";
                echo "ERROR AFTER EXECUTION: " . $mysqli->error . "\n";
                echo "ERROR NO: " . $mysqli->errno . "\n";
                echo "</pre>";
            } else {
                echo "<pre style='color:green;'>";
                echo "Migration would execute successfully!\n";
                echo "</pre>";

                // Rollback - don't actually record this migration
                echo "<p><em>Note: Migration was tested but not recorded.</em></p>\n";
            }
        }

        $mysqli->close();
    }

} catch (Exception $e) {
    echo "<pre style='color:red;'>";
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "</pre>";
}
?>
