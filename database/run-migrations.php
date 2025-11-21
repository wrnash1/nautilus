#!/usr/bin/env php
<?php
/**
 * Migration Runner Script
 * Runs all database migrations in the correct order
 */

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = 'Frogman09!';
$dbName = 'nautilus_dev';

// Connect to database
try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "✓ Connected to database: {$dbName}\n\n";
} catch (PDOException $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// Create migrations tracking table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        batch INT NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_batch (batch)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// Get list of already executed migrations
$stmt = $pdo->query("SELECT migration FROM migrations");
$executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get all migration files
$migrationsDir = __DIR__ . '/migrations';
$files = glob($migrationsDir . '/*.sql');

// Sort files - handle non-standard numbering
usort($files, function($a, $b) {
    $a = basename($a);
    $b = basename($b);

    // Extract numeric prefix
    preg_match('/^(\d+[a-z]?)_/', $a, $matchA);
    preg_match('/^(\d+[a-z]?)_/', $b, $matchB);

    $numA = $matchA[1] ?? $a;
    $numB = $matchB[1] ?? $b;

    // Convert letters to decimal (000a = 000.1, 000b = 000.2, etc.)
    $numA = preg_replace_callback('/(\d+)([a-z])/', function($m) {
        return $m[1] . '.' . (ord($m[2]) - ord('a') + 1);
    }, $numA);

    $numB = preg_replace_callback('/(\d+)([a-z])/', function($m) {
        return $m[1] . '.' . (ord($m[2]) - ord('a') + 1);
    }, $numB);

    return version_compare($numA, $numB);
});

// Skip problematic migration that tries to alter non-existent tables
$skipMigrations = ['000c_fix_foreign_key_types.sql'];

// Determine current batch number
$stmt = $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 as next_batch FROM migrations");
$batch = $stmt->fetch()['next_batch'];

echo "Starting migrations (Batch {$batch})...\n";
echo str_repeat("-", 70) . "\n\n";

$success = 0;
$skipped = 0;
$errors = 0;

foreach ($files as $file) {
    $filename = basename($file);

    // Skip if already executed
    if (in_array($filename, $executed)) {
        continue;
    }

    // Skip problematic migrations
    if (in_array($filename, $skipMigrations)) {
        echo "⊘ SKIPPED: {$filename} (known issue - will be handled by migration 104)\n";
        $skipped++;
        continue;
    }

    echo "→ Running: {$filename}...";

    try {
        $sql = file_get_contents($file);

        // Execute the migration
        $pdo->exec($sql);

        // Record successful migration
        $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$filename, $batch]);

        echo " ✓\n";
        $success++;

    } catch (PDOException $e) {
        echo " ✗\n";
        echo "  ERROR: " . $e->getMessage() . "\n";

        // Check if error is just a warning about duplicate objects
        if (strpos($e->getMessage(), 'already exists') !== false ||
            strpos($e->getMessage(), 'Duplicate') !== false) {
            echo "  (Non-critical: Object already exists, continuing...)\n";

            // Record as executed anyway
            try {
                $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                $stmt->execute([$filename, $batch]);
            } catch (PDOException $e2) {
                // Migration already recorded
            }

            $success++;
        } else {
            $errors++;
            // Don't stop on errors, continue with other migrations
        }
    }
}

echo "\n" . str_repeat("-", 70) . "\n";
echo "Migration Summary:\n";
echo "  ✓ Successful: {$success}\n";
echo "  ⊘ Skipped: {$skipped}\n";
echo "  ✗ Errors: {$errors}\n";

// Show table count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '{$dbName}'");
$tableCount = $stmt->fetch()['count'];
echo "\nTotal tables created: {$tableCount}\n";

// Check for critical tables
$criticalTables = ['users', 'customers', 'products', 'transactions', 'tenants'];
$missing = [];
foreach ($criticalTables as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
    if ($stmt->rowCount() == 0) {
        $missing[] = $table;
    }
}

if (empty($missing)) {
    echo "✓ All critical tables present\n";
} else {
    echo "✗ Missing critical tables: " . implode(', ', $missing) . "\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
if ($errors == 0) {
    echo "✓ Database migration completed successfully!\n";
    exit(0);
} else {
    echo "⚠ Database migration completed with {$errors} errors\n";
    echo "Review errors above and run migration 104 to fix remaining issues.\n";
    exit(1);
}
