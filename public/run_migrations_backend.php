<?php
/**
 * Backend migration processor - streams progress updates
 */
session_start();
header("Content-Type: text/plain");
header("X-Accel-Buffering: no"); // Disable nginx buffering
ob_implicit_flush(true);

// Check if this is a quick install from streamlined installer
$isQuickInstall = isset($_SESSION['install_data']);

if ($isQuickInstall) {
    $config = $_SESSION['install_data'];
} else {
    $config = $_SESSION["db_config"] ?? [
        "host" => getenv("DB_HOST") ?: "database",
        "port" => getenv("DB_PORT") ?: "3306",
        "database" => getenv("DB_DATABASE") ?: "nautilus",
        "username" => getenv("DB_USERNAME") ?: "root",
        "password" => getenv("DB_PASSWORD") ?: "Frogman09!"
    ];
}

$files = glob("/var/www/html/database/migrations/*.sql");
sort($files);

$total = count($files);
echo "TOTAL:$total\n";
flush();

$processed = 0;

foreach ($files as $file) {
    $filename = basename($file);

    // Support both old installer format and streamlined installer format
    $host = $config["host"] ?? $config["db_host"] ?? "database";
    $port = $config["port"] ?? $config["db_port"] ?? "3306";
    $database = $config["database"] ?? $config["db_name"] ?? "nautilus";
    $username = $config["username"] ?? $config["db_user"] ?? "nautilus";
    $password = $config["password"] ?? $config["db_pass"] ?? "nautilus123";

    $cmd = sprintf(
        "mariadb -h%s -P%s -u%s -p%s %s < %s 2>&1",
        escapeshellarg($host),
        escapeshellarg($port),
        escapeshellarg($username),
        escapeshellarg($password),
        escapeshellarg($database),
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

// If quick install, create .env file and admin account
if ($isQuickInstall) {
    $rootDir = dirname(__DIR__);

    // Create .env file
    $envContent = "APP_NAME=\"Nautilus Dive Shop\"\n";
    $envContent .= "APP_ENV=development\n";
    $envContent .= "APP_DEBUG=true\n";
    $envContent .= "APP_URL=http://localhost:8080\n";
    $envContent .= "APP_TIMEZONE=America/New_York\n\n";
    $envContent .= "DB_HOST={$config['db_host']}\n";
    $envContent .= "DB_PORT={$config['db_port']}\n";
    $envContent .= "DB_DATABASE={$config['db_name']}\n";
    $envContent .= "DB_USERNAME={$config['db_user']}\n";
    $envContent .= "DB_PASSWORD={$config['db_pass']}\n";

    file_put_contents($rootDir . '/.env', $envContent);

    // Create admin account
    try {
        $pdo = new PDO(
            "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']}",
            $config['db_user'],
            $config['db_pass']
        );

        // Update tenant
        $pdo->exec("UPDATE tenants SET name = " . $pdo->quote($config['company']) . " WHERE id = 1");

        // Update admin user
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password_hash = ? WHERE id = 1");
        $stmt->execute([
            $config['username'],
            $config['email'],
            $config['password']
        ]);

        // Mark as installed
        file_put_contents($rootDir . '/.installed', date('Y-m-d H:i:s'));

    } catch (PDOException $e) {
        echo "ERROR:Failed to create admin: " . $e->getMessage() . "\n";
    }
}
