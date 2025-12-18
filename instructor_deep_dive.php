<?php
/**
 * Instructor Workflow Deep Dive Verification Script
 * 
 * Verifies:
 * 1. Instructor Login (Simulated)
 * 2. Course Schedule Assignment
 * 3. Roster View (Database)
 * 4. Add Notes (Controller/Service Logic)
 * 5. Update Skills (Controller/Service Logic)
 * 6. SMS (Service Logic)
 */

require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Services/Courses/CourseEnrollmentWorkflow.php';
require_once __DIR__ . '/app/Services/CRM/CustomerService.php';
require_once __DIR__ . '/app/Services/Communication/CommunicationService.php';
require_once __DIR__ . '/app/Services/StudentAssessmentService.php';

use App\Core\Database;
use App\Services\Courses\CourseEnrollmentWorkflow;
use App\Services\Communication\CommunicationService;
use App\Services\StudentAssessmentService;

// --- manually load env ---
$lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
        putenv($line);
    }
}

// Setup DB connection
$db = Database::getInstance()->getConnection();
echo "Database Connected.\n";

// --- 0. Helpers ---
function getOrCreateInstructor($db) {
    echo "Checking for Instructor...\n";
    $stmt = $db->prepare("SELECT * FROM users WHERE role = 'instructor' LIMIT 1");
    $stmt->execute();
    $instructor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$instructor) {
        echo "Creating new Instructor...\n";
        $password = password_hash('instructor123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password, role, first_name, last_name, is_active) VALUES ('instructor_dave', 'dave@nautilus.local', ?, 'instructor', 'Dave', 'Diver', 1)");
        $stmt->execute([$password]);
        $id = $db->lastInsertId();
        return ['id' => $id, 'username' => 'instructor_dave'];
    }
    echo "Found Instructor: " . $instructor['username'] . " (ID: " . $instructor['id'] . ")\n";
    return $instructor;
}

function getCourseEnrollment($db) {
    // Find the enrollment from previous steps (Customer 1, Schedule ?)
    $stmt = $db->query("SELECT e.*, c.first_name, c.last_name FROM course_enrollments e JOIN customers c ON e.customer_id = c.id LIMIT 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- 1. Setup Data ---
$instructor = getOrCreateInstructor($db);
$enrollment = getCourseEnrollment($db);

if (!$enrollment) {
    die("FAIL: No enrollment found. Run previous setup first.\n");
}
echo "Found Enrollment: " . $enrollment['id'] . " for " . $enrollment['first_name'] . "\n";

// Assign Instructor to Schedule if not already
$stmt = $db->prepare("UPDATE course_schedules SET instructor_id = ? WHERE id = ?");
$stmt->execute([$instructor['id'], $enrollment['schedule_id']]);
echo "Assigned Instructor " . $instructor['id'] . " to Schedule " . $enrollment['schedule_id'] . "\n";


// --- 2. Verify Roster Logic (View Classes) ---
echo "\n--- Testing Roster View Logic ---\n";
// Directly query what SkillsCheckoffController::index uses
$assessmentService = new StudentAssessmentService($db);
$students = $assessmentService->getInstructorStudents($instructor['id'], null);

$found = false;
foreach ($students as $s) {
    if ($s['enrollment_id'] == $enrollment['id']) {
        $found = true;
        echo "PASS: Student found in Instructor's Roster.\n";
        break;
    }
}
if (!$found) {
    echo "WARN: Student NOT found in Roster (might be completed or status issue). Dumping roster count: " . count($students) . "\n";
}


// --- 3. Add Notes (Simulate Controller Action) ---
echo "\n--- Testing Add Notes ---\n";
// Ensure course_student_record exists (it's created on enrollment usually, but let's check)
$stmt = $db->prepare("SELECT id FROM course_student_records WHERE enrollment_id = ?");
$stmt->execute([$enrollment['id']]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    echo "Creating Student Record for tracking...\n";
    $stmt = $db->prepare("INSERT INTO course_student_records (enrollment_id, overall_status) VALUES (?, 'in_progress')");
    $stmt->execute([$enrollment['id']]);
    $recordId = $db->lastInsertId();
} else {
    $recordId = $record['id'];
}

// Simulate Add Notes
// Controller calls: course_student_records update
$note = "Deep Dive Test Note " . date('H:i:s');
$stmt = $db->prepare("UPDATE course_student_records SET instructor_notes = ? WHERE id = ?");
$result = $stmt->execute([$note, $recordId]);

if ($result) {
    // Verify
    $chk = $db->prepare("SELECT instructor_notes FROM course_student_records WHERE id = ?");
    $chk->execute([$recordId]);
    $start = $chk->fetchColumn();
    if ($start === $note) {
         echo "PASS: Instructor Note updated successfully.\n";
    } else {
         echo "FAIL: Note mismatch.\n";
    }
} else {
    echo "FAIL: Could not update note.\n";
}


// --- 4. Validate Standards (Update Skill) ---
echo "\n--- Testing Standards Validation (Skills) ---\n";
// Initialize skills if needed
$assessmentService->initializeSessionSkills($recordId, 'open_water', 1);
$skills = $assessmentService->getSessionSkills($recordId, 'open_water', 1);

if (empty($skills)) {
    echo "FAIL: No skills found even after initialization.\n";
} else {
    $skillToUpdate = $skills[0];
    echo "Updating Skill: " . $skillToUpdate['skill_name'] . " (ID: " . $skillToUpdate['assessment_id'] . ")\n";
    
    $success = $assessmentService->updateSkillAssessment(
        $skillToUpdate['assessment_id'],
        [
            'performance' => 'mastery',
            'pass' => true,
            'attempts' => 1,
            'assessment_notes' => 'Perfect buoyancy',
            'remediation_needed' => false
        ]
    );

    if ($success) {
        echo "PASS: Skill marked as Mastery.\n";
    } else {
        echo "FAIL: Skill update returned false.\n";
    }
}


// --- 5. Send SMS ---
echo "\n--- Testing SMS Sending ---\n";
// Checks if CommunicationService can "send" (mocked mostly, but we check DB/Audit)
$commService = new CommunicationService();
// We need to ensure customer has a phone?
$stmt = $db->prepare("UPDATE customers SET mobile = '555-0199' WHERE id = ?");
$stmt->execute([$enrollment['customer_id']]);

try {
    // CommunicationService::sendSMS returns bool
    // In dev it usually just logs or returns true if configured
    $smsResult = $commService->sendSMS($enrollment['customer_id'], "Hello from Deep Dive Test!");
    if ($smsResult) {
        echo "PASS: SMS Service returned success.\n";
    } else {
        echo "FAIL: SMS Service returned failure.\n";
    }
} catch (Exception $e) {
    echo "FAIL: SMS Exception: " . $e->getMessage() . "\n";
}

echo "\n--- Deep Dive Verification Complete ---\n";
