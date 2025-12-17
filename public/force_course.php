<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();
require_once BASE_PATH . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Cleanup first to be sure
    $conn->exec("DELETE FROM courses WHERE name LIKE '%Open Water%'");
    
    // Insert Fresh
    $sql = "INSERT INTO courses (name, code, description, price, capacity, is_active, created_at) VALUES ('Open Water Diver', 'OWD-001', 'Description', 399.00, 10, 1, NOW())";
    $conn->exec($sql);
    
    echo "COURSE_CREATED_SUCCESS\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
