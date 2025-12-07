<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$backupDir = __DIR__ . '/../storage/backups';
$date = date('Y-m-d_His');
$filename = "nautilus_backup_{$date}.sql.gz";
$filepath = "$backupDir/$filename";

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$host = $_ENV['DB_HOST'];
$database = $_ENV['DB_DATABASE'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];

$command = sprintf(
    'mysqldump -h%s -u%s -p%s %s | gzip > %s',
    escapeshellarg($host),
    escapeshellarg($username),
    escapeshellarg($password),
    escapeshellarg($database),
    escapeshellarg($filepath)
);

exec($command, $output, $returnCode);

if ($returnCode === 0) {
    $size = filesize($filepath);
    $sizeFormatted = round($size / 1024 / 1024, 2);
    echo "✓ Backup created successfully: $filename ($sizeFormatted MB)\n";
    
    $files = glob("$backupDir/nautilus_backup_*.sql.gz");
    $cutoffDate = strtotime('-30 days');
    
    foreach ($files as $file) {
        if (filemtime($file) < $cutoffDate) {
            unlink($file);
            echo "✓ Deleted old backup: " . basename($file) . "\n";
        }
    }
    
} else {
    echo "✗ Backup failed\n";
    exit(1);
}
