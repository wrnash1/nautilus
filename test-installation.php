#!/usr/bin/env php
<?php
/**
 * Nautilus Installation Test Script
 * Tests database connection, migrations, and basic functionality
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "========================================\n";
echo "Nautilus Installation Test\n";
echo "========================================\n\n";

// Test 1: Vendor autoload
echo "[1/10] Checking Composer dependencies... ";
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✗ FAIL\n";
    echo "ERROR: vendor/autoload.php not found. Run: composer install\n";
    exit(1);
}
require __DIR__ . '/vendor/autoload.php';
echo "✓ PASS\n";

// Test 2: .env file
echo "[2/10] Checking .env file... ";
if (!file_exists(__DIR__ . '/.env')) {
    echo "✗ FAIL\n";
    echo "ERROR: .env file not found. Copy .env.example to .env\n";
    exit(1);
}
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
echo "✓ PASS\n";

// Test 3: Database connection
echo "[3/10] Testing database connection... ";
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ PASS\n";
} catch (PDOException $e) {
    echo "✗ FAIL\n";
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Check your .env database credentials\n";
    exit(1);
}

// Test 4: Check critical tables exist
echo "[4/10] Checking database tables... ";
$requiredTables = ['users', 'customers', 'products', 'transactions', 'roles', 'permissions'];
$missingTables = [];

foreach ($requiredTables as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() === 0) {
        $missingTables[] = $table;
    }
}

if (!empty($missingTables)) {
    echo "✗ FAIL\n";
    echo "ERROR: Missing tables: " . implode(', ', $missingTables) . "\n";
    echo "Run migrations: ./setup-database.sh\n";
    exit(1);
}
echo "✓ PASS\n";

// Test 5: Check for admin user
echo "[5/10] Checking for admin user... ";
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

if ($userCount == 0) {
    echo "⚠ WARNING\n";
    echo "No users found. Create admin: php public/create-admin.php\n";
} else {
    echo "✓ PASS ($userCount user(s) found)\n";
}

// Test 6: Check roles exist
echo "[6/10] Checking roles... ";
$stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
$roleCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

if ($roleCount == 0) {
    echo "✗ FAIL\n";
    echo "ERROR: No roles found. Ensure seeders ran correctly\n";
    exit(1);
}
echo "✓ PASS ($roleCount role(s) found)\n";

// Test 7: Check file permissions
echo "[7/10] Checking file permissions... ";
$writableDirs = ['storage', 'storage/logs', 'storage/cache', 'public/uploads'];
$permissionIssues = [];

foreach ($writableDirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
    }
    if (!is_writable($path)) {
        $permissionIssues[] = $dir;
    }
}

if (!empty($permissionIssues)) {
    echo "⚠ WARNING\n";
    echo "Not writable: " . implode(', ', $permissionIssues) . "\n";
    echo "Run: chmod -R 775 storage public/uploads\n";
} else {
    echo "✓ PASS\n";
}

// Test 8: Check PHP extensions
echo "[8/10] Checking PHP extensions... ";
$requiredExtensions = ['mysqli', 'pdo', 'pdo_mysql', 'json', 'curl', 'mbstring', 'openssl', 'gd'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    echo "✗ FAIL\n";
    echo "ERROR: Missing extensions: " . implode(', ', $missingExtensions) . "\n";
    exit(1);
}
echo "✓ PASS\n";

// Test 9: Check APP_KEY and JWT_SECRET
echo "[9/10] Checking security keys... ";
$keysOk = true;

if (empty($_ENV['APP_KEY'])) {
    echo "⚠ WARNING\n";
    echo "APP_KEY not set in .env\n";
    $keysOk = false;
}

if (empty($_ENV['JWT_SECRET'])) {
    echo "⚠ WARNING\n";
    echo "JWT_SECRET not set in .env\n";
    $keysOk = false;
}

if ($keysOk) {
    echo "✓ PASS\n";
}

// Test 10: Check web server configuration
echo "[10/10] Checking web server... ";
$publicIndex = __DIR__ . '/public/index.php';
if (!file_exists($publicIndex)) {
    echo "✗ FAIL\n";
    echo "ERROR: public/index.php not found\n";
    exit(1);
}
echo "✓ PASS\n";

// Summary
echo "\n========================================\n";
echo "Installation Test Complete\n";
echo "========================================\n\n";

// Database statistics
$stmt = $pdo->query("SELECT
    (SELECT COUNT(*) FROM users) as user_count,
    (SELECT COUNT(*) FROM customers) as customer_count,
    (SELECT COUNT(*) FROM products) as product_count,
    (SELECT COUNT(*) FROM transactions) as transaction_count");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Database Statistics:\n";
echo "  Users:        " . $stats['user_count'] . "\n";
echo "  Customers:    " . $stats['customer_count'] . "\n";
echo "  Products:     " . $stats['product_count'] . "\n";
echo "  Transactions: " . $stats['transaction_count'] . "\n";
echo "\n";

if ($userCount == 0) {
    echo "⚠ NEXT STEP: Create admin user\n";
    echo "Run: php public/create-admin.php\n";
    echo "Or visit: http://localhost/nautilus/public/create-admin.php\n";
} else {
    echo "✓ READY TO USE\n";
    echo "Login at: http://localhost/nautilus/public/store/login\n";

    // Show admin credentials if default admin exists
    $stmt = $pdo->query("SELECT email FROM users WHERE email = 'admin@nautilus.local' LIMIT 1");
    if ($stmt->rowCount() > 0) {
        echo "\nDefault Admin Credentials:\n";
        echo "  Email:    admin@nautilus.local\n";
        echo "  Password: password\n";
        echo "  ⚠ Change password after first login!\n";
    }
}

echo "\n";
