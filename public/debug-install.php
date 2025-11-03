<?php
/**
 * Debug Installation Issues
 * Shows detailed error messages for SQL problems
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Installation Debugger</h1>";
echo "<style>body{font-family:monospace;} .error{color:red;background:#ffe;padding:10px;margin:10px 0;border-left:4px solid red;} .success{color:green;} .info{color:blue;}</style>";

// Load environment
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "<div class='error'><strong>ERROR:</strong> vendor/ folder not found!<br>You must include the vendor folder in your upload.</div>";
    exit;
}

require __DIR__ . '/../vendor/autoload.php';

if (!file_exists(__DIR__ . '/../.env')) {
    echo "<div class='error'><strong>ERROR:</strong> .env file not found!<br>Copy .env.example to .env and configure database credentials.</div>";
    exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

echo "<h2>1. Environment Check</h2>";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "<br>";
echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'NOT SET') . "<br>";
echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'NOT SET') . "<br>";
echo "DB_PASSWORD: " . (isset($_ENV['DB_PASSWORD']) ? '[SET]' : 'NOT SET') . "<br><br>";

// Test database connection
echo "<h2>2. Database Connection</h2>";

try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? '';
    $username = $_ENV['DB_USERNAME'] ?? '';
    $password = $_ENV['DB_PASSWORD'] ?? '';

    $mysqli = new mysqli($host, $username, $password, $database, $port);

    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    echo "<span class='success'>✓ Connected successfully</span><br>";

    // Get MySQL version
    $result = $mysqli->query("SELECT VERSION() as version");
    $version = $result->fetch_assoc();
    echo "MySQL Version: " . $version['version'] . "<br>";

    // Check version compatibility
    if (version_compare($version['version'], '8.0.0', '<') &&
        strpos($version['version'], 'MariaDB') === false) {
        echo "<div class='error'><strong>WARNING:</strong> MySQL version is below 8.0. Some features may not work.</div>";
    }

    echo "<br>";

} catch (Exception $e) {
    echo "<div class='error'><strong>ERROR:</strong> " . $e->getMessage() . "</div>";
    exit;
}

// Test creating migrations table
echo "<h2>3. Test Creating Migrations Table</h2>";

try {
    $sql = "
        CREATE TABLE IF NOT EXISTS migrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";

    if ($mysqli->query($sql)) {
        echo "<span class='success'>✓ Migrations table created/verified</span><br><br>";
    } else {
        throw new Exception("SQL Error: " . $mysqli->error);
    }

} catch (Exception $e) {
    echo "<div class='error'><strong>ERROR Creating Migrations Table:</strong><br>" . $e->getMessage() . "</div>";
    exit;
}

// Check if migrations already run
echo "<h2>4. Check Existing Migrations</h2>";

try {
    $result = $mysqli->query("SELECT COUNT(*) as count FROM migrations");
    $count = $result->fetch_assoc()['count'];

    if ($count > 0) {
        echo "<span class='info'>Found {$count} existing migrations in database.</span><br>";
        echo "<strong>Database appears to be already installed!</strong><br><br>";

        // Show which migrations are installed
        $result = $mysqli->query("SELECT migration, executed_at FROM migrations ORDER BY id LIMIT 10");
        echo "First 10 migrations:<br>";
        while ($row = $result->fetch_assoc()) {
            echo "- " . $row['migration'] . " (executed: " . $row['executed_at'] . ")<br>";
        }
        echo "<br>";
    } else {
        echo "<span class='success'>No migrations found - ready for fresh installation.</span><br><br>";
    }

} catch (Exception $e) {
    echo "<div class='error'>Error checking migrations: " . $e->getMessage() . "</div>";
}

// Test running first migration
echo "<h2>5. Test First Migration</h2>";

$migrationFile = __DIR__ . '/../database/migrations/001_create_users_and_auth_tables.sql';

if (!file_exists($migrationFile)) {
    echo "<div class='error'>Migration file not found: {$migrationFile}</div>";
    exit;
}

echo "Migration file: " . basename($migrationFile) . "<br>";
echo "File size: " . filesize($migrationFile) . " bytes<br><br>";

// Check if this migration already ran
$result = $mysqli->query("SELECT COUNT(*) as count FROM migrations WHERE migration = '001_create_users_and_auth_tables.sql'");
$alreadyRan = $result->fetch_assoc()['count'] > 0;

if ($alreadyRan) {
    echo "<span class='info'>This migration already ran. Skipping test.</span><br>";
} else {
    echo "Attempting to run first migration...<br>";

    try {
        $sql = file_get_contents($migrationFile);

        // Execute using multi_query
        if (!$mysqli->multi_query($sql)) {
            throw new Exception("SQL Error: " . $mysqli->error);
        }

        // Clear all result sets
        do {
            if ($result = $mysqli->store_result()) {
                $result->free();
            }
        } while ($mysqli->more_results() && $mysqli->next_result());

        // Check for errors
        if ($mysqli->error) {
            throw new Exception("SQL Error: " . $mysqli->error);
        }

        // Record migration
        $stmt = $mysqli->prepare("INSERT INTO migrations (migration, batch) VALUES (?, 1)");
        $migrationName = '001_create_users_and_auth_tables.sql';
        $stmt->bind_param("s", $migrationName);
        $stmt->execute();
        $stmt->close();

        echo "<span class='success'>✓ First migration executed successfully!</span><br><br>";

        // Show created tables
        $result = $mysqli->query("SHOW TABLES");
        echo "Tables created:<br>";
        while ($row = $result->fetch_array()) {
            echo "- " . $row[0] . "<br>";
        }

    } catch (Exception $e) {
        echo "<div class='error'><strong>ERROR Running Migration:</strong><br><br>";
        echo "<strong>Error Message:</strong><br>" . htmlspecialchars($e->getMessage()) . "<br><br>";

        // Show the SQL that failed
        echo "<strong>SQL Content (first 500 chars):</strong><br>";
        echo "<pre>" . htmlspecialchars(substr($sql, 0, 500)) . "...</pre>";

        echo "</div>";
        exit;
    }
}

echo "<h2>Summary</h2>";

if ($alreadyRan) {
    echo "<div class='info'>";
    echo "<strong>Database is already installed!</strong><br><br>";
    echo "Next steps:<br>";
    echo "1. Visit <a href='/store/login'>/store/login</a> to login<br>";
    echo "2. If you need to reinstall, drop all tables and try again<br>";
    echo "</div>";
} else {
    echo "<div class='success'>";
    echo "<strong>Test successful!</strong><br><br>";
    echo "Your database is ready for installation.<br><br>";
    echo "Next steps:<br>";
    echo "1. Delete this file: public/debug-install.php<br>";
    echo "2. Visit <a href='/install'>/install</a> to run full installation<br>";
    echo "</div>";
}

$mysqli->close();
?>
