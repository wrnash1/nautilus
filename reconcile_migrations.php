<?php
/**
 * Migration Reconciliation Script
 * Lists tables and compares with expected migrations.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = '127.0.0.1';
$db   = 'nautilus';
$user = 'nautilus';
$host = '127.0.0.1';
$db   = 'nautilus';
$user = 'nautilus';
$passwords = ['nautilus123', 'password'];

$pdo = null;
foreach ($passwords as $p) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $p);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Connected with password: " . substr($p, 0, 3) . "***\n";
        break;
    } catch (PDOException $e) {
        continue;
    }
}

if (!$pdo) {
    die("Connection Failed: All passwords failed.");
}

    // Get Tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Found " . count($tables) . " tables.\n";
    foreach ($tables as $t) {
        echo "- $t\n";
    }

    echo "\n--- Migration Files ---\n";
    $files = glob(__DIR__ . '/database/migrations/*.sql');
    foreach ($files as $f) {
        echo basename($f) . "\n";
    }
