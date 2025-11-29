<?php
try {
    $pdo = new PDO('mysql:host=localhost', 'root', 'Frogman09!', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "SUCCESS: Connected to MySQL\n";
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "MySQL Version: $version\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
