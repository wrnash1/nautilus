<?php
/**
 * Test migrations one by one to find which fails
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><title>Migration Test</title>";
echo "<style>body{font-family:monospace;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";
echo "</head><body>";

echo "<h1>Testing Migrations One by One</h1>";
echo "<hr>";

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_DATABASE'] ?? '';
$username = $_ENV['DB_USERNAME'] ?? '';
$password = $_ENV['DB_PASSWORD'] ?? '';

// Connect with mysqli
$mysqli = new mysqli($host, $username, $password, $database, $port);

if ($mysqli->connect_error) {
    die("<p class='error'>Connection failed: " . $mysqli->connect_error . "</p>");
}

$mysqli->set_charset("utf8mb4");

// Create migrations table
$mysqli->query("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        batch INT NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// Get already executed migrations
$result = $mysqli->query("SELECT migration FROM migrations");
$executed = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $executed[] = $row['migration'];
    }
    $result->free();
}

echo "<p class='info'>Already executed: " . count($executed) . " migrations</p>";

// Get all migration files
$migrationsDir = __DIR__ . '/../database/migrations';
$files = glob($migrationsDir . '/*.sql');
sort($files);

$failed = false;
$count = 0;

foreach ($files as $file) {
    $filename = basename($file);

    if (in_array($filename, $executed)) {
        echo "<p class='info'>✓ Skip (already done): $filename</p>";
        continue;
    }

    $count++;
    echo "<p class='info'><strong>[$count] Testing: $filename</strong></p>";
    flush();

    // Read the SQL file
    $sql = file_get_contents($file);

    // Execute using multi_query
    if (!$mysqli->multi_query($sql)) {
        echo "<p class='error'>✗ FAILED: " . htmlspecialchars($mysqli->error) . "</p>";
        echo "<p class='error'>Error Number: " . $mysqli->errno . "</p>";
        echo "<hr>";
        echo "<h2>Failed Migration Content (first 3000 chars):</h2>";
        echo "<pre>" . htmlspecialchars(substr($sql, 0, 3000)) . "\n\n... (truncated)</pre>";
        $failed = true;
        break;
    }

    // Clear all result sets
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());

    // Check for errors after execution
    if ($mysqli->error) {
        echo "<p class='error'>✗ FAILED AFTER EXECUTION: " . htmlspecialchars($mysqli->error) . "</p>";
        echo "<p class='error'>Error Number: " . $mysqli->errno . "</p>";

        // Show last 100 lines of the migration
        echo "<hr>";
        echo "<h2>Failed Migration (last 100 lines):</h2>";
        $lines = explode("\n", $sql);
        $lastLines = array_slice($lines, -100);
        echo "<pre>" . htmlspecialchars(implode("\n", $lastLines)) . "</pre>";

        $failed = true;
        break;
    }

    // Record migration
    $stmt = $mysqli->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
    $batch = 1;
    $stmt->bind_param("si", $filename, $batch);
    $stmt->execute();
    $stmt->close();

    echo "<p class='success'>✓ SUCCESS</p>";
    flush();
}

if (!$failed) {
    echo "<hr>";
    echo "<h2 class='success'>All migrations completed successfully!</h2>";
}

$mysqli->close();

echo "</body></html>";
?>
