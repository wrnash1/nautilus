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
    
    echo "--- SEEDING DIVER DAVE ---\n";

    // 1. Ensure PADI Agency
    $conn->exec("INSERT INTO certification_agencies (name, abbreviation) VALUES ('Professional Association of Diving Instructors', 'PADI') ON DUPLICATE KEY UPDATE name=name");
    $stmt = $conn->prepare("SELECT id FROM certification_agencies WHERE abbreviation = 'PADI'");
    $stmt->execute();
    $padiId = $stmt->fetchColumn();
    echo "Agencies: OK (ID: $padiId)\n";

    // 2. Create/Update Diver Dave
    // Note: We include state/zip/country to pass validation
    $email = 'diverdave@example.com';
    $password = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $daveId = $stmt->fetchColumn();

    if ($daveId) {
        $conn->prepare("UPDATE customers SET password = ?, state = 'FL', postal_code = '33010', country = 'US' WHERE id = ?")->execute([$password, $daveId]);
        echo "Customer: UPDATED (ID: $daveId)\n";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO customers 
            (first_name, last_name, email, phone, birth_date, password, state, postal_code, country, customer_type, created_at)
            VALUES 
            ('Diver', 'Dave', ?, '555-0199', '1985-06-15', ?, 'FL', '33010', 'US', 'B2C', NOW())
        ");
        $stmt->execute([$email, $password]);
        $daveId = $conn->lastInsertId();
        echo "Customer: CREATED (ID: $daveId)\n";
    }

    // 3. Add Certification
    $stmt = $conn->prepare("
        INSERT INTO customer_certifications 
        (customer_id, certification_agency_id, certification_level, certification_number, issue_date) 
        VALUES (?, ?, 'Open Water Diver', 'PADI-123456', '2020-01-15')
        ON DUPLICATE KEY UPDATE certification_number = VALUES(certification_number)
    ");
    $stmt->execute([$daveId, $padiId]);
    echo "Certification: OK\n";
    
    echo "--- SUCCESS ---\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
