<?php
// get_data.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$course = $pdo->query("SELECT id FROM courses WHERE name LIKE '%Open Water%' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$schedule = $pdo->query("SELECT id FROM course_schedules WHERE start_date >= CURRENT_DATE LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$student = $pdo->query("SELECT id FROM customers LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$instructor = $pdo->query("SELECT id FROM users WHERE role = 'instructor' LIMIT 1")->fetch(PDO::FETCH_ASSOC);

echo "COURSE_ID=" . ($course['id'] ?? '0') . "\n";
echo "SCHEDULE_ID=" . ($schedule['id'] ?? '0') . "\n";
echo "STUDENT_ID=" . ($student['id'] ?? '1') . "\n";
echo "INSTRUCTOR_ID=" . ($instructor['id'] ?? '0') . "\n";
