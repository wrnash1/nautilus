<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

Database::query("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        batch INT NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$executed = Database::fetchAll("SELECT migration FROM migrations") ?? [];
$executedFiles = array_column($executed, 'migration');

$lastBatch = Database::fetchOne("SELECT MAX(batch) as max_batch FROM migrations");
$currentBatch = ($lastBatch['max_batch'] ?? 0) + 1;

$migrationsDir = __DIR__ . '/../database/migrations';
$files = glob($migrationsDir . '/*.sql');
sort($files);

$newMigrations = 0;

foreach ($files as $file) {
    $filename = basename($file);
    
    if (in_array($filename, $executedFiles)) {
        echo "✓ Skipped: $filename (already executed)\n";
        continue;
    }
    
    try {
        echo "Running: $filename...";
        
        $sql = file_get_contents($file);
        
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($stmt) => !empty($stmt) && !preg_match('/^\s*--/', $stmt)
        );
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                Database::query($statement);
            }
        }
        
        Database::query(
            "INSERT INTO migrations (migration, batch) VALUES (?, ?)",
            [$filename, $currentBatch]
        );
        
        echo " ✓ Success\n";
        $newMigrations++;
        
    } catch (Exception $e) {
        echo " ✗ Failed\n";
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if ($newMigrations === 0) {
    echo "\nNo new migrations to run.\n";
} else {
    echo "\nSuccessfully executed $newMigrations migration(s).\n";
}
