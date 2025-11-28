<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnostic Info</h1>";

// Test DB Connection
echo "<h2>Database Connection Test</h2>";
$host = '127.0.0.1';
$port = '3306';
$db   = 'nautilus';
$user = 'nautilus';
$pass = 'NautilusR0cks!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<p style='color:green'>Database connection successful!</p>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tables found: " . count($tables) . "</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";

} catch (\PDOException $e) {
    echo "<p style='color:red'>Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Code: " . $e->getCode() . "</p>";
}

// List /tmp
echo "<h2>/tmp Directory Listing</h2>";
$tmpFiles = scandir('/tmp');
if ($tmpFiles === false) {
    echo "<p style='color:red'>Failed to scan /tmp</p>";
} else {
    echo "<ul>";
    foreach ($tmpFiles as $file) {
        if ($file != "." && $file != "..") {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
}
?>
