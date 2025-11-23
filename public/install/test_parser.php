<?php
$schemaFile = '/home/wrnash1/development/nautilus/database/migrations/000_CORE_SCHEMA.sql';
$sql = file_get_contents($schemaFile);

// Original logic (simulated failure)
$original_statements = array_filter(
    array_map('trim', explode(';', $sql)),
    fn($stmt) => !empty($stmt) && !preg_match('/^--/', $stmt)
);

// New logic
$sql_clean = preg_replace('/--.*$/m', '', $sql);
$sql_clean = preg_replace('/\/\*.*?\*\//s', '', $sql_clean);

$new_statements = array_filter(
    array_map('trim', explode(';', $sql_clean)),
    fn($stmt) => !empty($stmt)
);

echo "Original Parser Count: " . count($original_statements) . "\n";
echo "New Parser Count: " . count($new_statements) . "\n";

// Check if specific tables are found in new statements
$tables = ['users', 'tenants', 'roles'];
foreach ($tables as $table) {
    $found = false;
    foreach ($new_statements as $stmt) {
        if (stripos($stmt, "CREATE TABLE `$table`") !== false) {
            $found = true;
            break;
        }
    }
    echo "Table '$table' found: " . ($found ? 'YES' : 'NO') . "\n";
}
