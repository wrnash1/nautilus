<?php
/**
 * Pre-flight Requirements Checker
 * This script checks if the server meets all requirements for Nautilus
 * Access this BEFORE running the installer
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$checks = [];
$allPassed = true;

// PHP Version Check
$phpVersion = phpversion();
$phpRequired = '8.1.0';
$phpCheck = version_compare($phpVersion, $phpRequired, '>=');
$checks[] = [
    'name' => 'PHP Version',
    'required' => '>= ' . $phpRequired,
    'current' => $phpVersion,
    'passed' => $phpCheck,
    'critical' => true
];
if (!$phpCheck) $allPassed = false;

// Required PHP Extensions
$requiredExtensions = [
    'pdo' => 'PDO (Database abstraction)',
    'pdo_mysql' => 'PDO MySQL Driver',
    'mbstring' => 'Multibyte String',
    'json' => 'JSON',
    'openssl' => 'OpenSSL',
    'curl' => 'cURL',
    'fileinfo' => 'File Info',
    'gd' => 'GD (Image processing)',
    'zip' => 'ZIP Archive',
];

foreach ($requiredExtensions as $ext => $description) {
    $loaded = extension_loaded($ext);
    $checks[] = [
        'name' => "PHP Extension: $ext",
        'required' => $description,
        'current' => $loaded ? 'Installed' : 'Not installed',
        'passed' => $loaded,
        'critical' => true
    ];
    if (!$loaded) $allPassed = false;
}

// Check for .env file
$envExists = file_exists(__DIR__ . '/../.env');
$checks[] = [
    'name' => '.env Configuration File',
    'required' => 'Must exist',
    'current' => $envExists ? 'Exists' : 'Missing',
    'passed' => $envExists,
    'critical' => true
];
if (!$envExists) $allPassed = false;

// Check if vendor directory exists (Composer)
$vendorExists = is_dir(__DIR__ . '/../vendor');
$checks[] = [
    'name' => 'Composer Dependencies',
    'required' => 'vendor/ directory must exist',
    'current' => $vendorExists ? 'Installed' : 'Not installed',
    'passed' => $vendorExists,
    'critical' => true
];
if (!$vendorExists) $allPassed = false;

// Check writable directories
$writableDirs = [
    'storage' => __DIR__ . '/../storage',
    'storage/logs' => __DIR__ . '/../storage/logs',
    'storage/uploads' => __DIR__ . '/../storage/uploads',
    'storage/cache' => __DIR__ . '/../storage/cache',
];

foreach ($writableDirs as $name => $path) {
    $exists = is_dir($path);
    $writable = $exists && is_writable($path);
    $checks[] = [
        'name' => "Writable: $name",
        'required' => 'Must be writable by web server',
        'current' => !$exists ? 'Missing' : ($writable ? 'Writable' : 'Not writable'),
        'passed' => $writable,
        'critical' => true
    ];
    if (!$writable) $allPassed = false;
}

// Database connection test (if .env exists)
if ($envExists && $vendorExists) {
    try {
        require __DIR__ . '/../vendor/autoload.php';
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? 3306;
        $database = $_ENV['DB_DATABASE'] ?? '';
        $username = $_ENV['DB_USERNAME'] ?? '';
        $password = $_ENV['DB_PASSWORD'] ?? '';

        $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $checks[] = [
            'name' => 'Database Connection',
            'required' => 'Must connect to MySQL/MariaDB',
            'current' => "Connected to $host:$port",
            'passed' => true,
            'critical' => true
        ];

        // Check if database exists
        $stmt = $pdo->query("SHOW DATABASES LIKE '$database'");
        $dbExists = $stmt->fetch() !== false;
        $checks[] = [
            'name' => 'Database Exists',
            'required' => "Database '$database' should exist",
            'current' => $dbExists ? "Exists" : "Will be created",
            'passed' => true,
            'critical' => false
        ];

    } catch (Exception $e) {
        $checks[] = [
            'name' => 'Database Connection',
            'required' => 'Must connect to MySQL/MariaDB',
            'current' => 'Failed: ' . $e->getMessage(),
            'passed' => false,
            'critical' => true
        ];
        $allPassed = false;
    }
}

// Check memory limit
$memoryLimit = ini_get('memory_limit');
$memoryLimitBytes = return_bytes($memoryLimit);
$requiredMemory = 128 * 1024 * 1024; // 128MB
$memoryCheck = $memoryLimitBytes >= $requiredMemory || $memoryLimitBytes == -1;
$checks[] = [
    'name' => 'PHP Memory Limit',
    'required' => '>= 128M',
    'current' => $memoryLimit,
    'passed' => $memoryCheck,
    'critical' => false
];

// Check max execution time
$maxExecutionTime = ini_get('max_execution_time');
$timeCheck = $maxExecutionTime >= 60 || $maxExecutionTime == 0;
$checks[] = [
    'name' => 'Max Execution Time',
    'required' => '>= 60 seconds',
    'current' => $maxExecutionTime . ' seconds',
    'passed' => $timeCheck,
    'critical' => false
];

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

// Display results
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nautilus - System Requirements Check</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        h1 { color: #333; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 30px; }
        .status-banner {
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            font-weight: 600;
            text-align: center;
        }
        .status-pass { background: #d4edda; color: #155724; border: 2px solid #c3e6cb; }
        .status-fail { background: #f8d7da; color: #721c24; border: 2px solid #f5c6cb; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .check-pass { color: #28a745; }
        .check-fail { color: #dc3545; }
        .check-icon { font-size: 18px; font-weight: bold; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 20px;
        }
        .btn:hover { background: #5568d3; }
        .btn-disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .help-text {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .help-text strong { display: block; margin-bottom: 10px; }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌊 Nautilus System Requirements Check</h1>
        <p class="subtitle">Pre-installation verification</p>

        <?php if ($allPassed): ?>
            <div class="status-banner status-pass">
                ✓ All requirements met! You can proceed with installation.
            </div>
        <?php else: ?>
            <div class="status-banner status-fail">
                ✗ Some requirements are not met. Please fix the issues below before installing.
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th width="30">Status</th>
                    <th>Requirement</th>
                    <th>Required</th>
                    <th>Current</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checks as $check): ?>
                <tr class="<?= $check['passed'] ? 'check-pass' : 'check-fail' ?>">
                    <td class="check-icon"><?= $check['passed'] ? '✓' : '✗' ?></td>
                    <td><strong><?= htmlspecialchars($check['name']) ?></strong></td>
                    <td><?= htmlspecialchars($check['required']) ?></td>
                    <td><?= htmlspecialchars($check['current']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (!$allPassed): ?>
            <div class="help-text">
                <strong>⚠️ How to fix common issues:</strong>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <li style="margin: 8px 0;"><strong>Missing .env file:</strong> Copy <code>.env.example</code> to <code>.env</code> and configure database settings</li>
                    <li style="margin: 8px 0;"><strong>Composer dependencies:</strong> Run <code>composer install</code> in the project root</li>
                    <li style="margin: 8px 0;"><strong>Directory permissions:</strong> Run <code>chmod -R 775 storage</code> and <code>chown -R www-data:www-data storage</code></li>
                    <li style="margin: 8px 0;"><strong>PHP extensions:</strong> Install via package manager (e.g., <code>apt install php-{extension}</code>)</li>
                </ul>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px;">
            <?php if ($allPassed): ?>
                <a href="/simple-install.php" class="btn">Proceed to Installation</a>
            <?php else: ?>
                <span class="btn btn-disabled">Fix issues above to continue</span>
            <?php endif; ?>
            <br><br>
            <a href="javascript:location.reload()" style="color: #667eea;">Refresh Check</a>
        </div>
    </div>
</body>
</html>
