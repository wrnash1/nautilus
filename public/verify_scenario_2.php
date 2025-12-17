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
    
    // Check PADI Agency
    $stmt = $conn->prepare("SELECT id, name FROM certification_agencies WHERE name LIKE '%PADI%'");
    $stmt->execute();
    $agency = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($agency) {
        echo "AGENCY FOUND: {$agency['name']} (ID: {$agency['id']})\n";
    } else {
        echo "AGENCY NOT FOUND: Creating PADI...\n";
        $conn->exec("INSERT INTO certification_agencies (name, code, is_active) VALUES ('PADI', 'PADI', 1)");
        echo "AGENCY CREATED: PADI\n";
    }
    
    // Check Course
    $stmt = $conn->prepare("SELECT id, name FROM courses WHERE name LIKE '%Open Water%'");
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($course) {
        echo "COURSE FOUND: {$course['name']} (ID: {$course['id']})\n";
    } else {
        echo "COURSE NOT FOUND, checking recent seeds...\n";
        // seed_test_data.php should have created it, but let's check exact name
        $stmt = $conn->query("SELECT id, name FROM courses LIMIT 5");
        $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Available Courses:\n";
        foreach($all as $c) echo "- " . $c['name'] . "\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
