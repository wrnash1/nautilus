<?php
// setup_test_data.php
// setup_test_data.php
// Manually load env if needed or rely on hardcoded creds for this ephemeral test if env parsing is complex without composer
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

require_once __DIR__ . '/app/Core/Logger.php';
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

echo "Checking Test Data...\n";

// 1. Check/Create Course
$courseName = "PADI Open Water Diver";
$course = Database::fetchOne("SELECT * FROM courses WHERE name LIKE ?", ["%$courseName%"]);
if (!$course) {
    echo "Creating Course: $courseName\n";
    Database::execute("INSERT INTO courses (course_code, name, description, duration_days, price, created_by) VALUES (?, ?, ?, ?, ?, ?)", [
        'OWD-001', $courseName, 'Entry level diver certification', 3, 499.00, 1
    ]);
    $course = Database::fetchOne("SELECT * FROM courses WHERE name = ?", [$courseName]);
}
echo "Course ID: " . $course['id'] . "\n";

// 2. Check/Create Instructor
$instructorEmail = "instructor@nautilus.local";
$instructor = Database::fetchOne("SELECT * FROM users WHERE email = ?", [$instructorEmail]);
if (!$instructor) {
    echo "Creating Instructor: $instructorEmail\n";
    // Password hash for 'password'
    $hash = password_hash('password', PASSWORD_DEFAULT);
    Database::execute("INSERT INTO users (first_name, last_name, email, password, role, is_active) VALUES (?, ?, ?, ?, ?, ?)", [
        'John', 'Diver', $instructorEmail, $hash, 'instructor', 1
    ]);
    $instructor = Database::fetchOne("SELECT * FROM users WHERE email = ?", [$instructorEmail]);
}
echo "Instructor ID: " . $instructor['id'] . "\n";

// 3. Check/Create Schedule
$startDate = date('Y-m-d', strtotime('+1 week'));
$endDate = date('Y-m-d', strtotime('+1 week +3 days'));
$schedule = Database::fetchOne("SELECT * FROM course_schedules WHERE course_id = ? AND start_date >= CURRENT_DATE", [$course['id']]);
if (!$schedule) {
    echo "Creating Schedule starting $startDate\n";
    Database::execute("INSERT INTO course_schedules (course_id, instructor_id, start_date, end_date, location, status, max_students, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
        $course['id'], $instructor['id'], $startDate, $endDate, 'Main Pool', 'scheduled', 8, 1
    ]);
    $schedule = Database::fetchOne("SELECT * FROM course_schedules WHERE course_id = ? AND start_date >= CURRENT_DATE", [$course['id']]);
}
echo "Schedule ID: " . $schedule['id'] . "\n";

// 4. Check/Create Waiver Template
$waiver = Database::fetchOne("SELECT * FROM waiver_templates WHERE type = 'training' AND is_active = 1");
if (!$waiver) {
    echo "Creating Waiver Template\n";
    Database::execute("INSERT INTO waiver_templates (name, type, content, is_active, created_by) VALUES (?, ?, ?, ?, ?)", [
        'Standard Liability Release', 'training', '<h1>Liability Release</h1><p>I hereby release...</p>', 1, 1
    ]);
    $waiver = Database::fetchOne("SELECT * FROM waiver_templates WHERE type = 'training' AND is_active = 1");
}
echo "Waiver ID: " . $waiver['id'] . "\n";

echo "Setup Complete.\n";
