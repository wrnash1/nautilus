<?php
/**
 * Backend migration processor - streams progress updates
 */
session_start();
header("Content-Type: text/plain");
header("X-Accel-Buffering: no"); // Disable nginx buffering
ob_implicit_flush(true);

$config = $_SESSION["db_config"] ?? [
    "host" => getenv("DB_HOST") ?: "database",
    "port" => getenv("DB_PORT") ?: "3306", 
    "database" => getenv("DB_DATABASE") ?: "nautilus",
    "username" => getenv("DB_USERNAME") ?: "root",
    "password" => getenv("DB_PASSWORD") ?: "Frogman09!"
];

$files = glob("/var/www/html/database/migrations/*.sql");
sort($files);

$total = count($files);
echo "TOTAL:$total\n";
flush();

$processed = 0;

foreach ($files as $file) {
    $filename = basename($file);
    
    $cmd = sprintf(
        "mariadb -h%s -P%s -u%s -p%s %s < %s 2>&1",
        escapeshellarg($config["host"]),
        escapeshellarg($config["port"]),
        escapeshellarg($config["username"]),
        escapeshellarg($config["password"]),
        escapeshellarg($config["database"]),
        escapeshellarg($file)
    );
    
    exec($cmd, $output, $return_code);
    
    $processed++;
    echo "PROGRESS:$processed\n";
    flush();
    
    usleep(50000); // Small delay so user can see progress
}

echo "COMPLETE\n";
$_SESSION["db_installed"] = true;
