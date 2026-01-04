<?php
$host = 'database';
$db   = 'nautilus';
$user = 'nautilus';
$pass = 'nautilus123';
$port = "3306";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "Dropped $table\n";
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "All tables dropped.\n";

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
    // Fallback to localhost if 'database' host fails (e.g. running from host machine)
    try {
         $host = '127.0.0.1';
         $port = '3307'; // Often exposed on 3307 or 3306
         echo "Retrying with localhost...\n";
         $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", 'root', 'Frogman09!'); // Host credentials
         $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
         $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
         foreach ($tables as $table) {
             $pdo->exec("DROP TABLE IF EXISTS `$table`");
             echo "Dropped $table\n";
         }
         $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
         echo "All tables dropped (via localhost).\n";
    } catch (PDOException $e2) {
        echo "DB Error (localhost): " . $e2->getMessage() . "\n";
    }
}
