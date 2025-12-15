<?php
/**
 * Backend migration processor - V3 FORCE SQL DIRECTLY
 */
session_start();
set_time_limit(0);
ignore_user_abort(true);
ini_set('memory_limit', '512M');
header("Content-Type: text/plain");
header("X-Accel-Buffering: no"); 
ob_implicit_flush(true);

// HARDCODED CONFIG
$config = [
    "db_host" => "nautilus-db",
    "db_port" => "3306",
    "db_name" => "nautilus",
    "db_user" => "root",
    "db_pass" => "Frogman09!"
];

echo "STARTING V3 DIRECT SQL\n";

$mysqli = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name'], $config['db_port']);

if ($mysqli->connect_error) {
     echo "ERROR: Connection failed: " . $mysqli->connect_error . "\n";
     exit;
}

// SQL DIRECTLY HERE
$sql = "
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_type` VARCHAR(20) DEFAULT 'string',
    `description` TEXT,
    `category` VARCHAR(50) DEFAULT 'general',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `category`) VALUES
('business_name', 'Nautilus Dive Shop', 'string', 'general'),
('business_email', 'info@nautilus.local', 'string', 'general'),
('business_hours', 'Mon-Fri: 9am - 6pm\\nSat: 10am - 4pm\\nSun: Closed', 'string', 'general'),
('brand_primary_color', '#0066cc', 'string', 'branding'),
('brand_secondary_color', '#003366', 'string', 'branding'),
('business_country', 'US', 'string', 'general'),
('currency', 'USD', 'string', 'regional'),
('timezone', 'America/New_York', 'string', 'regional');
";

// Enable exception reporting
$mysqli_driver = new mysqli_driver();
$mysqli_driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

try {
    if ($mysqli->multi_query($sql)) {
        do {
            if ($result = $mysqli->store_result()) {
                $result->free();
            }
        } while ($mysqli->more_results() && $mysqli->next_result());
        
        echo "SUCCESS: Migration executed!\n";
    } else {
         echo "ERROR: " . $mysqli->error . "\n";
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}

$mysqli->close();
echo "COMPLETE\n";
