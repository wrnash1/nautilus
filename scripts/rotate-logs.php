<?php
$logDir = __DIR__ . '/../storage/logs';

if (!is_dir($logDir)) {
    echo "Log directory not found.\n";
    exit(0);
}

$files = glob("$logDir/*.log");
$cutoffDate = strtotime('-90 days');
$archived = 0;
$deleted = 0;

foreach ($files as $file) {
    $age = time() - filemtime($file);
    
    if ($age > (30 * 24 * 60 * 60) && $age < (90 * 24 * 60 * 60)) {
        $gzFile = $file . '.gz';
        if (!file_exists($gzFile)) {
            $data = file_get_contents($file);
            file_put_contents($gzFile, gzcompress($data, 9));
            unlink($file);
            $archived++;
        }
    }
    elseif ($age > (90 * 24 * 60 * 60)) {
        unlink($file);
        $deleted++;
    }
}

echo "✓ Archived $archived log file(s), deleted $deleted old log file(s).\n";
