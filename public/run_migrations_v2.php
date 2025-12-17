<?php
/**
 * Backend migration processor - streams progress updates
 */
session_start();
set_time_limit(0);
ignore_user_abort(true);
ini_set('memory_limit', '512M');
header("Content-Type: text/plain");
header("X-Accel-Buffering: no"); // Disable nginx buffering
ob_implicit_flush(true);

// Check if this is a quick install from streamlined installer
$debugMsg = "Backend started. Session ID: " . session_id() . "\n";
$debugMsg .= "Session Data: " . print_r($_SESSION, true) . "\n";
file_put_contents('/tmp/debug_install.log', $debugMsg, FILE_APPEND);
file_put_contents('/var/www/html/storage/logs/install_debug.log', $debugMsg, FILE_APPEND);

$isQuickInstall = false; // isset($_SESSION['install_data']);
if ($isQuickInstall) {
    $config = $_SESSION['install_data'];
} else {
    // $config = $_SESSION["db_config"] ?? [
    $config = [
        "db_host" => "nautilus-db",
        "db_port" => getenv("DB_PORT") ?: "3306",
        "db_name" => getenv("DB_DATABASE") ?: "nautilus",
        "db_user" => getenv("DB_USERNAME") ?: "root",
        "db_pass" => getenv("DB_PASSWORD") ?: "Frogman09!"
    ];
}

// Normalize config keys if coming from session with old format
if (!isset($config['db_host']) && isset($config['host'])) {
    $config['db_host'] = $config['host'];
    $config['db_port'] = $config['port'];
    $config['db_name'] = $config['database'];
    $config['db_user'] = $config['username'];
    $config['db_pass'] = $config['password'];
}

// Override with root for migration runner privileges
$config['db_host'] = 'nautilus-db'; // Or 127.0.0.1 if local
$config['db_user'] = 'root';
$config['db_pass'] = 'Frogman09!';
$config['db_name'] = 'nautilus';

$targetFile = $_GET['file'] ?? null;
if ($targetFile) {
    // Security: sanitize filename to prevent directory traversal
    $targetFile = basename($targetFile);
    $files = glob("/var/www/html/database/migrations/" . $targetFile);
} else {
    $files = glob("/var/www/html/database/migrations/*.sql");
}

$globError = error_get_last();
file_put_contents('/var/www/html/storage/logs/install_debug.log', "Glob result count: " . count($files) . "\n", FILE_APPEND);
if ($files === false) {
    file_put_contents('/var/www/html/storage/logs/install_debug.log', "Glob failed! Error: " . print_r($globError, true) . "\n", FILE_APPEND);
}
if ($files) {
    sort($files);
} else {
    $files = [];
}

$total = count($files);
echo "TOTAL:$total\n";
flush();

$processed = 0;

foreach ($files as $file) {
    $filename = basename($file);
    // Use MySQLi for migrations to avoid PDO "unbuffered query" issues with multi-statement SQL
    $mysqli = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name'], $config['db_port']);
    
    if ($mysqli->connect_error) {
         $error = "Connection failed: " . $mysqli->connect_error;
         file_put_contents('/var/www/html/storage/logs/install_debug.log', $error . "\n", FILE_APPEND);
         echo "ERROR:$error\n";
         exit;
    }

    // Generate friendly name
    $friendlyName = ucwords(str_replace(['_', '.sql'], [' ', ''], substr($filename, 4)));
    if (strpos($filename, 'pos') !== false) $friendlyName = 'Point of Sale System';
    if (strpos($filename, 'audit') !== false) $friendlyName = 'Audit Trail System';
    if (strpos($filename, 'customer') !== false) $friendlyName = 'Customer Profiles';
    if (strpos($filename, 'inventory') !== false) $friendlyName = 'Inventory System';
    if (strpos($filename, 'ecommerce') !== false) $friendlyName = 'E-Commerce & AI';
    if (strpos($filename, 'courses') !== false) $friendlyName = 'Course Management';
    if (strpos($filename, 'core') !== false) $friendlyName = 'Core Database Schema';

    echo "START:$friendlyName\n";
    flush();

    $sql = trim(file_get_contents($file));
    if (empty($sql)) {
        file_put_contents('/var/www/html/storage/logs/install_debug.log', "Skipping empty file: $filename\n", FILE_APPEND);
        continue;
    }
    file_put_contents('/var/www/html/storage/logs/install_debug.log', "Processing: $filename ($friendlyName)\n", FILE_APPEND);
    
    // Enable exception reporting for mysqli
    $mysqli_driver = new mysqli_driver();
    $mysqli_driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

    try {
        if ($mysqli->multi_query($sql)) {
            do {
                // consume results to clear stack
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());
            
            $processed++;
            echo "PROGRESS:$processed\n";
            flush();
            file_put_contents('/var/www/html/storage/logs/install_debug.log', "Executed: $filename\n", FILE_APPEND);
        } else {
             throw new Exception($mysqli->error);
        }
    } catch (Exception $e) {
        // Ignore "Query was empty" error which can happen with trailing comments/whitespace in multi_query
        if ($e->getMessage() == 'Query was empty') {
             file_put_contents('/var/www/html/storage/logs/install_debug.log', "Warning: Ignored 'Query was empty' for $filename\n", FILE_APPEND);
             $processed++; // Treat as success
             echo "PROGRESS:$processed\n";
             flush();
        } else {
            $error = "Migration failed ($filename): " . $e->getMessage();
            file_put_contents('/var/www/html/storage/logs/install_debug.log', $error . "\n", FILE_APPEND);
            echo "ERROR:$error\n";
            $mysqli->close();
            exit;
        }
    }
    
    $mysqli->close();
    
    usleep(50000); // Small delay so user can see progress
}

echo "COMPLETE\n";
$_SESSION["db_installed"] = true;

// If quick install, create .env file and admin account
if ($isQuickInstall) {
    file_put_contents('/var/www/html/storage/logs/install_debug.log', "Quick install block entered\n", FILE_APPEND);
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

    $envResult = file_put_contents($rootDir . '/.env', $envContent);
    file_put_contents('/var/www/html/storage/logs/install_debug.log', "Env write result: " . ($envResult === false ? "FALSE" : $envResult) . "\n", FILE_APPEND);

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
        $instResult = file_put_contents($rootDir . '/.installed', date('Y-m-d H:i:s'));
        file_put_contents('/var/www/html/storage/logs/install_debug.log', "Installed write result: " . ($instResult === false ? "FALSE" : $instResult) . "\n", FILE_APPEND);

    } catch (PDOException $e) {
        file_put_contents('/var/www/html/storage/logs/install_debug.log', "DB Error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "ERROR:Failed to create admin: " . $e->getMessage() . "\n";
    }
} else {
    file_put_contents('/var/www/html/storage/logs/install_debug.log', "Quick install block SKIPPED. Session: " . print_r($_SESSION, true) . "\n", FILE_APPEND);
}
