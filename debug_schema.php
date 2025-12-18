<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Core\Database;

try {
    ob_start();
    $tables = Database::fetchAll("SHOW TABLES LIKE '%time%'");
    echo "Time Tables:\n";
    var_dump($tables);

    $tables2 = Database::fetchAll("SHOW TABLES LIKE '%clock%'");
    echo "Clock Tables:\n";
    var_dump($tables2);

    $users = Database::fetchAll("DESCRIBE users");
    echo "Users Table:\n";
    var_dump($users);
    
    $output = ob_get_clean();
    file_put_contents('debug_output.txt', $output);
    echo "Output written to debug_output.txt";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
