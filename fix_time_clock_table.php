<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Core\Database;

try {
    $db = Database::getInstance();
    $sql = "CREATE TABLE IF NOT EXISTS time_clock_entries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        clock_in DATETIME NOT NULL,
        clock_out DATETIME NULL,
        total_hours DECIMAL(10, 2) NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );";
    
    echo "Attempting to create table...\n";
    $db->query($sql);
    echo "Table time_clock_entries created or already exists.\n";
    
    // Check if table exists
    $check = $db->query("SHOW TABLES LIKE 'time_clock_entries'");
    if ($check->rowCount() > 0) {
        echo "VERIFIED: Table 'time_clock_entries' exists.\n";
    } else {
        echo "ERROR: Table 'time_clock_entries' does not exist.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
