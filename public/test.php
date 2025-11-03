<?php
/**
 * Simple diagnostic test page
 * Visit: https://yourdomain.com/test.php
 */

echo "<h1>Nautilus Diagnostic Test</h1>";

// Test 1: PHP Version
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Required: 8.0+<br>";
echo "Status: " . (version_compare(phpversion(), '8.0.0', '>=') ? '<span style="color:green">✓ PASS</span>' : '<span style="color:red">✗ FAIL</span>') . "<br><br>";

// Test 2: Required Extensions
echo "<h2>2. PHP Extensions</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 'curl'];
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "$ext: " . ($loaded ? '<span style="color:green">✓ Loaded</span>' : '<span style="color:red">✗ Missing</span>') . "<br>";
}
echo "<br>";

// Test 3: File Paths
echo "<h2>3. File Paths</h2>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
echo "BASE_PATH should be: " . dirname(__DIR__) . "<br><br>";

// Test 4: .env File
echo "<h2>4. Environment File</h2>";
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    echo ".env file: <span style='color:green'>✓ Exists</span><br>";
    echo "Location: $envPath<br>";

    // Try to load it
    if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
        require dirname(__DIR__) . '/vendor/autoload.php';

        try {
            $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
            $dotenv->load();
            echo "Loading: <span style='color:green'>✓ Success</span><br>";
            echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'not set') . "<br>";
            echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'not set') . "<br>";
        } catch (Exception $e) {
            echo "Loading: <span style='color:red'>✗ Failed</span><br>";
            echo "Error: " . $e->getMessage() . "<br>";
        }
    }
} else {
    echo ".env file: <span style='color:red'>✗ Not Found</span><br>";
    echo "Expected location: $envPath<br>";
    echo "<strong>ACTION REQUIRED:</strong> Copy .env.example to .env<br>";
}
echo "<br>";

// Test 5: Vendor Directory
echo "<h2>5. Composer Dependencies</h2>";
$vendorPath = dirname(__DIR__) . '/vendor';
if (is_dir($vendorPath)) {
    echo "vendor/ directory: <span style='color:green'>✓ Exists</span><br>";
    if (file_exists($vendorPath . '/autoload.php')) {
        echo "autoload.php: <span style='color:green'>✓ Exists</span><br>";
    } else {
        echo "autoload.php: <span style='color:red'>✗ Missing</span><br>";
        echo "<strong>ACTION REQUIRED:</strong> Run: composer install<br>";
    }
} else {
    echo "vendor/ directory: <span style='color:red'>✗ Not Found</span><br>";
    echo "<strong>ACTION REQUIRED:</strong> Run: composer install<br>";
}
echo "<br>";

// Test 6: Storage Directory Permissions
echo "<h2>6. Storage Permissions</h2>";
$storagePath = dirname(__DIR__) . '/storage';
if (is_dir($storagePath)) {
    echo "storage/ directory: <span style='color:green'>✓ Exists</span><br>";
    echo "Writable: " . (is_writable($storagePath) ? '<span style="color:green">✓ YES</span>' : '<span style="color:red">✗ NO - Fix with: chmod 775 storage/</span>') . "<br>";
} else {
    echo "storage/ directory: <span style='color:red'>✗ Not Found</span><br>";
}
echo "<br>";

// Test 7: Database Connection
echo "<h2>7. Database Connection</h2>";
if (isset($_ENV['DB_HOST']) && isset($_ENV['DB_DATABASE'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$_ENV['DB_DATABASE']}",
            $_ENV['DB_USERNAME'] ?? '',
            $_ENV['DB_PASSWORD'] ?? '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "Connection: <span style='color:green'>✓ Success</span><br>";

        // Check if migrations table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'migrations'");
        if ($stmt->fetch()) {
            echo "migrations table: <span style='color:green'>✓ Exists</span><br>";

            // Count migrations
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM migrations");
            $count = $stmt->fetch()['count'];
            echo "Migrations run: $count<br>";

            if ($count > 0) {
                echo "<strong>STATUS: Already Installed</strong><br>";
                echo "Visit: <a href='/store/login'>/store/login</a><br>";
            } else {
                echo "<strong>STATUS: Needs Installation</strong><br>";
                echo "Visit: <a href='/install'>/install</a><br>";
            }
        } else {
            echo "migrations table: <span style='color:orange'>⚠ Not Found</span><br>";
            echo "<strong>STATUS: Needs Installation</strong><br>";
            echo "Visit: <a href='/install'>/install</a><br>";
        }
    } catch (PDOException $e) {
        echo "Connection: <span style='color:red'>✗ Failed</span><br>";
        echo "Error: " . $e->getMessage() . "<br>";
        echo "<strong>ACTION REQUIRED:</strong> Check database credentials in .env<br>";
    }
} else {
    echo "Database credentials: <span style='color:red'>✗ Not configured in .env</span><br>";
}
echo "<br>";

// Test 8: .htaccess
echo "<h2>8. Apache Configuration</h2>";
$htaccessPath = __DIR__ . '/.htaccess';
if (file_exists($htaccessPath)) {
    echo ".htaccess file: <span style='color:green'>✓ Exists</span><br>";

    // Check if mod_rewrite is enabled (rough check)
    if (function_exists('apache_get_modules')) {
        $modules = apache_get_modules();
        $rewrite = in_array('mod_rewrite', $modules);
        echo "mod_rewrite: " . ($rewrite ? '<span style="color:green">✓ Enabled</span>' : '<span style="color:red">✗ Disabled - Enable it!</span>') . "<br>";
    } else {
        echo "mod_rewrite: <span style='color:orange'>⚠ Cannot detect (CGI mode)</span><br>";
    }
} else {
    echo ".htaccess file: <span style='color:red'>✗ Missing</span><br>";
}
echo "<br>";

// Summary
echo "<h2>Summary</h2>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";

if (!file_exists(dirname(__DIR__) . '/.env')) {
    echo "<li>Copy .env.example to .env and configure database credentials</li>";
}

if (!is_dir(dirname(__DIR__) . '/vendor')) {
    echo "<li>Run: composer install</li>";
}

if (isset($_ENV['DB_HOST']) && !isset($count)) {
    echo "<li>Check database connection settings</li>";
}

if (isset($count) && $count == 0) {
    echo "<li>Visit <a href='/install'>/install</a> to complete installation</li>";
} elseif (isset($count) && $count > 0) {
    echo "<li>Visit <a href='/store/login'>/store/login</a> to login</li>";
} else {
    echo "<li>Fix the issues above, then visit <a href='/install'>/install</a></li>";
}

echo "</ol>";

echo "<hr>";
echo "<p><small>Delete this file after diagnosing: /public/test.php</small></p>";
