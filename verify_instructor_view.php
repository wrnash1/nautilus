<?php
// verify_instructor_view.php
// Mock session
session_start();
$_SESSION['user_id'] = 2; // Assuming Instructor ID is 2, will verify from get_data output

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load .env manual
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Services/Courses/CourseEnrollmentWorkflow.php';

use App\Core\Database;
use App\Services\Courses\CourseEnrollmentWorkflow;

$workflow = new CourseEnrollmentWorkflow();

// Get the schedule ID (assuming 1 or from arg)
$scheduleId = $argv[1] ?? 0;

if ($scheduleId == 0) {
    // Fetch latest schedule
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT id FROM course_schedules ORDER BY id DESC LIMIT 1");
    $schedule = $stmt->fetch();
    $scheduleId = $schedule['id'] ?? 0;
}

if ($scheduleId == 0) {
    echo "No schedule found.\n";
    exit(1);
}

echo "Checking Roster for Schedule $scheduleId...\n";
$roster = $workflow->getRosterWithRequirements($scheduleId);

if (empty($roster)) {
    echo "Roster is EMPTY.\n";
    exit(1);
}

echo "Found " . count($roster) . " students.\n";
foreach ($roster as $student) {
    echo "Student: " . $student['student_name'] . " - Status: " . $student['enrollment_status'] . "\n";
    echo "Requirements: " . count($student['requirements']) . "\n";
    foreach ($student['requirements'] as $req) {
        echo " - " . $req['requirement_name'] . ": " . ($req['is_completed'] ? "Done" : "Pending") . "\n";
    }
}
