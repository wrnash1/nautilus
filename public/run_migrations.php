<?php
/**
 * AJAX Migration Runner - Processes migrations in small batches
 */
set_time_limit(30);
require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

session_start();
$config = $_SESSION['db_config'] ?? [
    'host' => $_ENV['DB_HOST'] ?? 'database',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'database' => $_ENV['DB_DATABASE'] ?? 'nautilus',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? 'Frogman09!'
];

try {
    $pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
                   $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        batch INT NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $executed = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
    $files = glob(__DIR__ . '/../database/migrations/*.sql');
    sort($files);

    $batch_size = 5;
    $processed = 0;
    $results = [];

    foreach ($files as $file) {
        $filename = basename($file);
        if (in_array($filename, $executed) || $processed >= $batch_size) {
            if ($processed >= $batch_size) break;
            continue;
        }

        $sql = file_get_contents($file);
        $statements = array_filter(array_map('trim', explode(';', $sql)),
            fn($s) => !empty($s) && !preg_match('/^\s*--/', $s));

        foreach ($statements as $stmt) {
            if (empty(trim($stmt))) continue;
            try { $pdo->exec($stmt); } catch (PDOException $e) { }
        }

        $pdo->exec("INSERT IGNORE INTO migrations (migration, batch) VALUES ('{$filename}', 1)");
        $results[] = $filename;
        $processed++;
    }

    $completed = count($pdo->query("SELECT migration FROM migrations")->fetchAll());

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'processed' => $processed,
        'completed' => $completed,
        'total' => count($files),
        'done' => $completed >= count($files),
        'results' => $results
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
