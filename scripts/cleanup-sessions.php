<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$sessionDir = __DIR__ . '/../storage/sessions';

if (!is_dir($sessionDir)) {
    echo "Session directory not found.\n";
    exit(0);
}

$files = glob("$sessionDir/sess_*");
$cutoffTime = time() - (24 * 60 * 60);
$deleted = 0;

foreach ($files as $file) {
    if (filemtime($file) < $cutoffTime) {
        unlink($file);
        $deleted++;
    }
}

echo "✓ Cleaned up $deleted expired session file(s).\n";
