<?php
// verify_instructor_view_raw.php
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

// 1. Get Schedule
$sql = "SELECT cs.id, cs.course_id, c.name as course_name 
        FROM course_schedules cs 
        JOIN courses c ON cs.course_id = c.id 
        ORDER BY cs.id DESC LIMIT 1";
echo "Executing SQL: $sql\n";
try {
    $schedule = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Query Failed: " . $e->getMessage() . "\n";
    exit(1);
}

if (!$schedule) {
    echo "No schedule found. Checking tables...\n";
    $count = $pdo->query("SELECT COUNT(*) FROM course_schedules")->fetchColumn();
    echo "Total Schedules: $count\n";
    exit(1);
}

$scheduleId = $schedule['id'];
echo "Checking Schedule ID: $scheduleId (" . $schedule['course_name'] . ")\n";

// 2. Check Enrollments via SQL (simulating what getRoster does)
$sql = "SELECT e.id, c.first_name, c.last_name, e.status, e.enrollment_date 
        FROM course_enrollments e 
        JOIN customers c ON e.customer_id = c.id 
        WHERE e.schedule_id = $scheduleId";
$enrollments = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($enrollments) . " enrollments.\n";
foreach ($enrollments as $student) {
    echo " - " . $student['first_name'] . " " . $student['last_name'] . " (" . $student['status'] . ")\n";
}

// 3. Check Instructor Assignment (Course Schedule Instructor ID)
$sql = "SELECT instructor_id FROM course_schedules WHERE id = $scheduleId";
$res = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
echo "Instructor ID assigned: " . $res['instructor_id'] . "\n";
