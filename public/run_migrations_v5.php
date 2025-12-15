<?php
/**
 * Backend migration processor - V5 RESTORE USERS
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

echo "STARTING V5 RESTORE USERS\n";

$mysqli = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name'], $config['db_port']);

if ($mysqli->connect_error) {
     echo "ERROR: Connection failed: " . $mysqli->connect_error . "\n";
     exit;
}

$passwordHash = password_hash('Frogman09!', PASSWORD_DEFAULT);
$billHash = password_hash('Frogman09!', PASSWORD_DEFAULT);

$sql = "
SET FOREIGN_KEY_CHECKS=0;

INSERT IGNORE INTO `roles` (`name`, `description`) VALUES
('Super Admin', 'Full system access'),
('Admin', 'Store administrator'),
('Manager', 'Store manager'),
('Staff', 'Store staff'),
('Instructor', 'Diving instructor');

INSERT IGNORE INTO `users` (`id`, `tenant_id`, `username`, `email`, `password_hash`, `first_name`, `last_name`) VALUES
(1, 1, 'admin', 'admin@nautilus.local', '$passwordHash', 'Admin', 'User');

INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`) VALUES (1, 1);

-- Bill
INSERT IGNORE INTO `users` (`tenant_id`, `username`, `email`, `password_hash`, `first_name`, `last_name`) VALUES
(1, 'bill', 'bill@ascubadiving.com', '$billHash', 'Bill', 'Nash');

-- Assign Super Admin to Bill (assuming Bill is ID 2 if newly created, or we look it up)
INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`) 
SELECT id, 1 FROM users WHERE email = 'bill@ascubadiving.com';

SET FOREIGN_KEY_CHECKS=1;
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
        
        echo "SUCCESS: Users restored!\n";
    } else {
         echo "ERROR: " . $mysqli->error . "\n";
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}

$mysqli->close();
echo "COMPLETE\n";
