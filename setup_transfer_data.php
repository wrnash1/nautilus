<?php
// setup_transfer_data.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load .env
$env = [];
if (file_exists(__DIR__.'/.env')) {
    $lines = file(__DIR__.'/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $env[trim($name)] = trim($value);
    }
}

$dsn = "mysql:host=".($env['DB_HOST']??'127.0.0.1').";dbname=".($env['DB_DATABASE']??'nautilus');
$pdo = new PDO($dsn, $env['DB_USERNAME']??'nautilus', $env['DB_PASSWORD']??'nautilus123');

// 1. Get Course ID
$course = $pdo->query("SELECT id FROM courses WHERE name LIKE '%Open Water%' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$courseId = $course['id'];

// 2. Create Target Schedule
$startDate = date('Y-m-d', strtotime('+10 days'));
$endDate = date('Y-m-d', strtotime('+13 days'));
$stmt = $pdo->prepare("INSERT INTO course_schedules (course_id, start_date, end_date, max_students, status, created_by) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$courseId, $startDate, $endDate, 5, 'scheduled', 1]);
$newScheduleId = $pdo->lastInsertId();

echo "NEW_SCHEDULE_ID=$newScheduleId\n";

// 3. Get Enrollment ID
$enrollment = $pdo->query("SELECT id FROM course_enrollments ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($enrollment) {
    echo "ENROLLMENT_ID=" . $enrollment['id'] . "\n";
} else {
    echo "ENROLLMENT_ID=0\n";
}
