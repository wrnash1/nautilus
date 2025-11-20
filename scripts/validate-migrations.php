<?php
/**
 * Database Migration Validator and Cleanup
 * 
 * This script validates all database migrations and fixes issues
 */

echo "=====================================\n";
echo "Database Migration Validator\n";
echo "=====================================\n\n";

$migrationsDir = __DIR__ . '/../database/migrations';
$backupDir = __DIR__ . '/../backup/migration-cleanup-' . date('Ymd-His');

// Create backup directory
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "✓ Created backup directory: $backupDir\n\n";
}

// Get all migration files
$files = glob($migrationsDir . '/*.sql');
sort($files);

echo "Found " . count($files) . " migration files\n\n";

// Check for duplicates
echo "Checking for duplicate migration numbers...\n";
$numbers = [];
$duplicates = [];

foreach ($files as $file) {
    $basename = basename($file);
    preg_match('/^(\d+[a-z]?)_/', $basename, $matches);
    
    if (isset($matches[1])) {
        $number = $matches[1];
        
        if (isset($numbers[$number])) {
            $duplicates[$number][] = $file;
        } else {
            $numbers[$number] = $file;
        }
    }
}

if (!empty($duplicates)) {
    echo "⚠ Found duplicate migration numbers:\n";
    foreach ($duplicates as $number => $dupeFiles) {
        echo "  Number $number:\n";
        echo "    - " . basename($numbers[$number]) . " (keeping)\n";
        foreach ($dupeFiles as $dupeFile) {
            echo "    - " . basename($dupeFile) . " (moving to backup)\n";
            rename($dupeFile, $backupDir . '/' . basename($dupeFile));
        }
    }
    echo "\n";
} else {
    echo "✓ No duplicate migration numbers found\n\n";
}

// Validate SQL syntax
echo "Validating SQL syntax...\n";
$errors = 0;

foreach (glob($migrationsDir . '/*.sql') as $file) {
    $content = file_get_contents($file);
    
    // Basic SQL validation
    if (empty(trim($content))) {
        echo "✗ Empty file: " . basename($file) . "\n";
        $errors++;
        continue;
    }
    
    // Check for common SQL errors
    if (strpos($content, 'CREATE TABLE') === false && 
        strpos($content, 'ALTER TABLE') === false &&
        strpos($content, 'INSERT INTO') === false) {
        echo "⚠ No CREATE/ALTER/INSERT statements in: " . basename($file) . "\n";
    }
}

if ($errors === 0) {
    echo "✓ All migration files have content\n\n";
} else {
    echo "✗ Found $errors empty migration files\n\n";
}

// Extract all table names
echo "Extracting table names from migrations...\n";
$tables = [];

foreach (glob($migrationsDir . '/*.sql') as $file) {
    $content = file_get_contents($file);
    
    // Match CREATE TABLE statements
    preg_match_all('/CREATE TABLE (?:IF NOT EXISTS )?`?(\w+)`?/i', $content, $matches);
    foreach ($matches[1] as $table) {
        $tables[$table] = basename($file);
    }
}

echo "✓ Found " . count($tables) . " unique tables\n\n";

// Generate table list
$tableList = "# Database Tables\n\n";
$tableList .= "Total tables: " . count($tables) . "\n\n";
$tableList .= "## Tables by Migration\n\n";

ksort($tables);
foreach ($tables as $table => $migration) {
    $tableList .= "- `$table` (from $migration)\n";
}

file_put_contents(__DIR__ . '/../docs/DATABASE_TABLES.md', $tableList);
echo "✓ Generated docs/DATABASE_TABLES.md\n\n";

// Check for foreign key issues
echo "Checking for potential foreign key issues...\n";
$fkIssues = 0;

foreach (glob($migrationsDir . '/*.sql') as $file) {
    $content = file_get_contents($file);
    
    // Find FOREIGN KEY references
    preg_match_all('/FOREIGN KEY.*REFERENCES `?(\w+)`?/i', $content, $matches);
    
    foreach ($matches[1] as $referencedTable) {
        if (!isset($tables[$referencedTable])) {
            echo "⚠ Foreign key references non-existent table '$referencedTable' in " . basename($file) . "\n";
            $fkIssues++;
        }
    }
}

if ($fkIssues === 0) {
    echo "✓ No foreign key issues found\n\n";
} else {
    echo "⚠ Found $fkIssues potential foreign key issues\n\n";
}

// Generate migration order file
echo "Generating migration order file...\n";
$migrationOrder = "# Migration Execution Order\n\n";
$migrationOrder .= "Execute migrations in this exact order:\n\n";

$orderedFiles = glob($migrationsDir . '/*.sql');
sort($orderedFiles);

$i = 1;
foreach ($orderedFiles as $file) {
    $migrationOrder .= sprintf("%3d. %s\n", $i++, basename($file));
}

file_put_contents(__DIR__ . '/../docs/MIGRATION_ORDER.md', $migrationOrder);
echo "✓ Generated docs/MIGRATION_ORDER.md\n\n";

// Summary
echo "=====================================\n";
echo "Summary\n";
echo "=====================================\n\n";
echo "Total migrations: " . count(glob($migrationsDir . '/*.sql')) . "\n";
echo "Total tables: " . count($tables) . "\n";
echo "Duplicates moved: " . count($duplicates) . "\n";
echo "Foreign key issues: " . $fkIssues . "\n";
echo "\nBackup location: $backupDir\n";
echo "\n✓ Validation complete!\n\n";
