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
    
    echo "--- DEBUG COURSE QUERY ---\n";
    $sql = "SELECT * FROM courses WHERE is_active = 1 ORDER BY display_order ASC, name ASC";
    echo "Query: $sql\n";
    
    // Test raw PDO
    $stmt = $conn->query($sql);
    $rawCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Raw Count: " . count($rawCourses) . "\n";
    
    // Test App\Core\Database Wrapper (imitating Controller)
    // Note: Database::fetchAll uses static instance internally usually
    $wrapperCourses = Database::fetchAll($sql);
    echo "Wrapper Count: " . count($wrapperCourses ?? []) . "\n";
    
    if (empty($wrapperCourses)) {
        echo "Wrapper returned EMPTY array.\n";
    } else {
        print_r($wrapperCourses[0]);
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
