<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Adding customer authentication fields...\n";

try {
    $columns = Database::fetchAll("SHOW COLUMNS FROM customers LIKE 'password'");
    if (empty($columns)) {
        Database::query("ALTER TABLE customers ADD COLUMN password VARCHAR(255) AFTER email");
        echo "✓ Added password column\n";
    } else {
        echo "- Password column already exists\n";
    }
    
    $columns = Database::fetchAll("SHOW COLUMNS FROM customers LIKE 'email_verified_at'");
    if (empty($columns)) {
        Database::query("ALTER TABLE customers ADD COLUMN email_verified_at TIMESTAMP NULL AFTER password");
        echo "✓ Added email_verified_at column\n";
    } else {
        echo "- Email_verified_at column already exists\n";
    }
    
    $columns = Database::fetchAll("SHOW COLUMNS FROM customers LIKE 'remember_token'");
    if (empty($columns)) {
        Database::query("ALTER TABLE customers ADD COLUMN remember_token VARCHAR(100) NULL AFTER email_verified_at");
        echo "✓ Added remember_token column\n";
    } else {
        echo "- Remember_token column already exists\n";
    }
    
    echo "\n✓ Successfully added customer authentication fields!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
