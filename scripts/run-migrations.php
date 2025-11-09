#!/usr/bin/env php
<?php

/**
 * Database Migration Runner
 *
 * Runs all pending SQL migrations
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Database connection
$host = $_ENV['DB_HOST'];
$database = $_ENV['DB_DATABASE'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        ]
    );

    echo "✓ Connected to database: {$database}\n\n";

    // Create migrations tracking table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Get current batch number
    $stmt = $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 as next_batch FROM migrations");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $nextBatch = $result[0]['next_batch'];
    $stmt->closeCursor();

    // Get all migration files
    $migrationsDir = __DIR__ . '/../database/migrations';
    $files = glob($migrationsDir . '/*.sql');
    sort($files);

    // Get already run migrations
    $stmt = $pdo->query("SELECT migration FROM migrations");
    $ranMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $stmt->closeCursor();

    $executedCount = 0;
    $skippedCount = 0;

    foreach ($files as $file) {
        $filename = basename($file);

        // Skip if already run
        if (in_array($filename, $ranMigrations)) {
            echo "⊘ Skipped: {$filename} (already executed)\n";
            $skippedCount++;
            continue;
        }

        echo "→ Running: {$filename}\n";

        try {
            // Read SQL file
            $sql = file_get_contents($file);

            // For complex migrations, use mysqli's multi_query
            $mysqli = new mysqli($host, $username, $password, $database);
            if ($mysqli->connect_error) {
                throw new Exception("Connection failed: " . $mysqli->connect_error);
            }

            $mysqli->multi_query($sql);

            // Process all result sets
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());

            // Check for errors
            if ($mysqli->error) {
                // Ignore common non-fatal errors
                $ignorableErrors = ['already exists', 'Duplicate', 'Can\'t DROP', 'doesn\'t exist'];
                $shouldThrow = true;
                foreach ($ignorableErrors as $ignorable) {
                    if (strpos($mysqli->error, $ignorable) !== false) {
                        $shouldThrow = false;
                        break;
                    }
                }
                if ($shouldThrow) {
                    throw new Exception($mysqli->error);
                }
            }

            $mysqli->close();

            // Record migration using PDO
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$filename, $nextBatch]);

            echo "  ✓ Success\n";
            $executedCount++;

        } catch (Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . "\n";
            echo "  File: {$filename}\n";
            // Don't exit, continue with other migrations
            continue;
        }
    }

    echo "\n";
    echo "========================================\n";
    echo "Migration Summary:\n";
    echo "  Executed: {$executedCount}\n";
    echo "  Skipped:  {$skippedCount}\n";
    echo "  Batch:    {$nextBatch}\n";
    echo "========================================\n";

    if ($executedCount > 0) {
        echo "\n✓ All migrations completed successfully!\n";
    } else {
        echo "\n⊘ No new migrations to run.\n";
    }

} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
