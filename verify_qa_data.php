<?php
require 'vendor/autoload.php';
use App\Models\User;
use App\Core\Database;
Database::init();
try {
    $count = User::count();
    file_put_contents('qa_status.txt', "Users: $count\n");
} catch (Throwable $e) {
    file_put_contents('qa_status.txt', "Error: " . $e->getMessage() . "\n");
}
