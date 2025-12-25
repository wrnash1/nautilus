<?php
// Public System Reset Script
// Dropped databases, runs migrations, seeds data.

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "========================================\n";
echo "      NAUTILUS SYSTEM RESET TOOL        \n";
echo "========================================\n\n";

// 1. Load Env & Prioritize CLI
require_once __DIR__ . '/../vendor/autoload.php';
// Load .env but don't overwrite existing
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$tryLoad = @$dotenv->safeLoad();

// Helper to get env with precedence: CLI/Server -> .env
function get_env_var($key, $default) {
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    return $_ENV[$key] ?? $default;
}

$dbHost = get_env_var('DB_HOST', 'localhost');
$dbPort = get_env_var('DB_PORT', '3306');
$dbName = get_env_var('DB_DATABASE', 'nautilus');
$dbUser = get_env_var('DB_USERNAME', 'nautilus');
$dbPass = get_env_var('DB_PASSWORD', 'nautilus123');

echo "Debug: Host=$dbHost Port=$dbPort DB=$dbName User=$dbUser\n";

// 2. Drop Tables (Soft Reset)
echo "[1/6] Resetting Database '$dbName' (Dropping Tables)...\n";
try {
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Disable FK checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "      Dropped table: $table\n";
    }
    
    // Drop Views as well
    // (Ideally enable extended search for views, but usually SHOW FULL TABLES covers it)
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Unlink installed file
    if (file_exists(__DIR__ . '/../.installed')) {
        unlink(__DIR__ . '/../.installed');
        echo "      Removed .installed flag.\n";
    }
    
} catch (PDOException $e) {
    echo "ERROR: Database reset failed: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Run Migrations
echo "[2/6] Running Migrations...\n";
// We use shell_exec to run the backend migration script
// We need to set env vars for it just in case
$cmd = "DB_HOST=$dbHost DB_PORT=$dbPort DB_DATABASE=$dbName DB_USERNAME=$dbUser DB_PASSWORD=$dbPass php " . __DIR__ . "/run_migrations_backend.php";
$output = shell_exec($cmd . " 2>&1");
echo $output;

// 4. Run QA Seeder (Users/Roles)
echo "\n[3/6] Seeding Users and Roles...\n";
$cmd = "DB_HOST=$dbHost DB_PORT=$dbPort DB_DATABASE=$dbName DB_USERNAME=$dbUser DB_PASSWORD=$dbPass php " . __DIR__ . "/../tests/qa_seed_data.php";
$output = shell_exec($cmd . " 2>&1");
echo $output;

// 5. Run Product Seeder
echo "\n[4/6] Seeding Products...\n";
$cmd = "DB_HOST=$dbHost DB_PORT=$dbPort DB_DATABASE=$dbName DB_USERNAME=$dbUser DB_PASSWORD=$dbPass php " . __DIR__ . "/seed_products.php";
$output = shell_exec($cmd . " 2>&1");
echo $output;

// 6. Run POS Permissions Seeder
echo "\n[5/6] Seeding POS Permissions...\n";
$cmd = "DB_HOST=$dbHost DB_PORT=$dbPort DB_DATABASE=$dbName DB_USERNAME=$dbUser DB_PASSWORD=$dbPass php " . __DIR__ . "/seed_pos_permissions.php";
$output = shell_exec($cmd . " 2>&1");
echo $output;

echo "\n[6/6] System Reset Complete.\n";
echo "========================================\n";
