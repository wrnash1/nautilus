<?php
/**
 * Show migration file content
 */

$file = $_GET['file'] ?? '037_create_layaway_system.sql';
$migrationPath = __DIR__ . '/../database/migrations/' . basename($file);

header('Content-Type: text/plain');

if (!file_exists($migrationPath)) {
    die("File not found: $file");
}

echo "FILE: $file\n";
echo "PATH: $migrationPath\n";
echo str_repeat("=", 80) . "\n\n";

// Show lines 240-300 (where the VIEW is)
$lines = file($migrationPath);
$start = 240;
$end = 300;

for ($i = $start; $i < $end && $i < count($lines); $i++) {
    printf("%3d: %s", $i + 1, $lines[$i]);
}
?>
