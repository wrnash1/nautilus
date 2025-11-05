<?php
/**
 * Fix cash_drawer_sessions table - add missing status column
 * Run once: https://nautilus.local/fix-cash-drawer-table.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

echo "<h2>Fixing cash_drawer_sessions Table</h2><pre>";

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Check current columns
    echo "Current table structure:\n";
    $stmt = $pdo->query("DESCRIBE cash_drawer_sessions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $hasStatus = false;
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
        if ($col['Field'] === 'status') {
            $hasStatus = true;
        }
    }

    if ($hasStatus) {
        echo "\n✓ Status column already exists!\n";
    } else {
        echo "\n⚠ Status column is missing. Adding it now...\n\n";

        // Add status column (after register_id since drawer_id doesn't exist)
        $pdo->exec("
            ALTER TABLE cash_drawer_sessions
            ADD COLUMN status ENUM('open', 'closed', 'balanced', 'over', 'short')
            DEFAULT 'closed'
            AFTER register_id
        ");

        // Update existing rows based on whether they're closed
        $pdo->exec("
            UPDATE cash_drawer_sessions
            SET status = CASE
                WHEN closed_at IS NULL THEN 'open'
                WHEN variance > 0 THEN 'over'
                WHEN variance < 0 THEN 'short'
                WHEN variance = 0 THEN 'balanced'
                ELSE 'closed'
            END
        ");

        echo "✓ Status column added successfully!\n\n";

        // Verify
        $stmt = $pdo->query("DESCRIBE cash_drawer_sessions");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "Updated table structure:\n";
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }
    }

    echo "\n✓ Table fixed! Try accessing the dashboard again.\n";
    echo "\n<a href='/store'>Go to Dashboard</a>\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>