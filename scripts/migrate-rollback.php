<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$lastBatch = Database::fetchOne("SELECT MAX(batch) as max_batch FROM migrations");

if (!$lastBatch || $lastBatch['max_batch'] === null) {
    echo "No migrations to rollback.\n";
    exit(0);
}

$batchNumber = $lastBatch['max_batch'];

$migrations = Database::fetchAll(
    "SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC",
    [$batchNumber]
);

if (empty($migrations)) {
    echo "No migrations found in batch $batchNumber.\n";
    exit(0);
}

echo "Rolling back batch $batchNumber...\n";

foreach ($migrations as $migration) {
    $filename = $migration['migration'];
    
    Database::query("DELETE FROM migrations WHERE migration = ?", [$filename]);
    
    echo "✓ Rolled back: $filename\n";
}

echo "\nBatch $batchNumber rolled back successfully.\n";
echo "Warning: This only removed migration tracking. Manual database cleanup may be needed.\n";
