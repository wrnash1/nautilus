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
    
    // Check Diver Dave
    $stmt = $conn->prepare("SELECT id FROM customers WHERE email = 'diverdave@example.com'");
    $stmt->execute();
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($customer) {
        echo "CUSTOMER_ID: " . $customer['id'] . "\n";
        
        // Check PADI
        $stmt2 = $conn->prepare("SELECT id FROM certification_agencies WHERE name LIKE '%PADI%'");
        $stmt2->execute();
        $padi = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        if ($padi) {
             // Certify him
             $stmt3 = $conn->prepare("INSERT INTO customer_certifications (customer_id, certification_agency_id, certification_level, certification_number, issue_date) VALUES (?, ?, 'Open Water Diver', '123456789', '2020-01-01') ON DUPLICATE KEY UPDATE certification_number = VALUES(certification_number)");
             $stmt3->execute([$customer['id'], $padi['id']]);
             echo "CERTIFIED: YES\n";
        }
    } else {
        echo "CUSTOMER_ID: NOT_FOUND\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
