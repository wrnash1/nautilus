<?php
require __DIR__ . '/../vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
try {
    $db = \App\Core\Database::getInstance()->getConnection();
    $id = $db->query("SELECT id FROM users WHERE email='diver@nautilus.local'")->fetchColumn();
    if(!$id) {
        // Fallback: get ANY user that is not admin?
        $id = $db->query("SELECT id FROM users ORDER BY id DESC LIMIT 1")->fetchColumn();
    }
    file_put_contents(__DIR__ . '/id.txt', $id);
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/id.txt', "ERROR");
}
