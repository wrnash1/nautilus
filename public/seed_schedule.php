<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    // Get Course ID (Open Water)
    $stmt = $db->query("SELECT id FROM courses WHERE course_code = 'OWD-001'");
    $courseId = $stmt->fetchColumn();
    
    if (!$courseId) {
        die("Course OWD-001 not found. Run /debug-courses first.\n");
    }
    
    // Create Schedule
    $startDate = date('Y-m-d', strtotime('+1 week'));
    $endDate = date('Y-m-d', strtotime('+2 weeks'));
    
    $check = $db->prepare("SELECT id FROM course_schedules WHERE course_id = ? AND start_date = ?");
    $check->execute([$courseId, $startDate]);
    
    if ($scheduleId = $check->fetchColumn()) {
        echo "Schedule already exists: ID $scheduleId\n";
    } else {
        $sql = "INSERT INTO course_schedules (course_id, instructor_id, location_id, start_date, end_date, start_time, end_time, capacity, enrolled_count, status, created_at)
                VALUES (?, ?, 1, ?, ?, '09:00:00', '17:00:00', 10, 0, 'scheduled', NOW())";
        
        // Assuming Instructor ID 2 exists (or use Admin ID since we don't strictly enforce role FK in this loose seed)
        // Wait, Instructor ID might be different. Admin is 1?
        // Let's use user_id = 1 (Admin) as instructor for simplicity, or 0 if NULL allowed.
        $instructorId = 1; 
        
        $db->prepare($sql)->execute([$courseId, $instructorId, $startDate, $endDate]);
        $scheduleId = $db->lastInsertId();
        echo "Created Schedule: ID $scheduleId\n";
    }
    
    file_put_contents(__DIR__ . '/seed_schedule.log', "Schedule ID: $scheduleId");

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
