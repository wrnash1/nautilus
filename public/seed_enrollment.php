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
    
    echo "--- SEEDING INSTRUCTOR & ENROLLMENT ---\n";

    // 1. Create Instructor Ian
    $instEmail = 'ian@nautilus.local';
    // Password: password123 (Hash it)
    $passHash = password_hash('password123', PASSWORD_BCRYPT);
    $conn->exec("INSERT INTO users (tenant_id, username, email, password_hash, first_name, last_name, is_active) 
                 VALUES (1, 'instructor_ian', '$instEmail', '$passHash', 'Ian', 'Instructor', 1) 
                 ON DUPLICATE KEY UPDATE first_name='Ian'");
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$instEmail]);
    $instId = $stmt->fetchColumn();
    
    // Assign Instructor Role (ID 5 based on schema)
    $conn->exec("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES ($instId, 5)"); // 5 = Instructor
    echo "Instructor Ian ID: $instId\n";

    // 2. Get Course ID
    $stmt = $conn->query("SELECT id FROM courses WHERE name LIKE '%Open Water%' LIMIT 1");
    $courseId = $stmt->fetchColumn();
    if (!$courseId) die("Error: Course not found\n");
    
    // 3. Create Schedule
    $conn->exec("INSERT INTO course_schedules (course_id, instructor_id, start_date, end_date, location, max_students, status) 
                 VALUES ($courseId, $instId, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Pool & Ocean', 8, 'scheduled')");
    $scheduleId = $conn->lastInsertId();
    echo "Schedule Created ID: $scheduleId\n";

    // 4. Get Diver Dave ID
    $daveEmail = 'diverdave@example.com';
    $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->execute([$daveEmail]);
    $daveId = $stmt->fetchColumn();
    if (!$daveId) die("Error: Diver Dave not found\n");

    // 5. Enroll Dave
    // Status: enrolled, Payment: partial ($100 paid of $399)
    $conn->exec("INSERT INTO course_enrollments (schedule_id, customer_id, enrollment_date, status, amount_paid, payment_status, notes)
                 VALUES ($scheduleId, $daveId, CURDATE(), 'enrolled', 100.00, 'partial', 'Needs to sign waiver')
                 ON DUPLICATE KEY UPDATE notes='Seeded for testing'");
    
    echo "Diver Dave Enrolled in Sched #$scheduleId\n";
    echo "--- SEEDING COMPLETE ---\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
