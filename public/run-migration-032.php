<?php
// Temporary migration runner for migration 032
// DELETE THIS FILE AFTER RUNNING

require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = App\Core\Database::getInstance();

    // Read and execute the migration
    $sql = file_get_contents(__DIR__ . '/../database/migrations/032_add_certification_agency_branding.sql');

    // Split by semicolons and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->exec($statement);
        }
    }

    // Record in migrations table
    $db->exec("INSERT INTO migrations (filename, status, executed_at) VALUES ('032_add_certification_agency_branding.sql', 'success', NOW())");

    echo "Migration 032 executed successfully!\n";
    echo "You can now delete this file: /home/wrnash1/Developer/nautilus/public/run-migration-032.php\n";

} catch (Exception $e) {
    echo "Error executing migration: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
