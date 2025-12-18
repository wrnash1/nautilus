<?php
// simple_seed.php
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

$host = $env['DB_HOST'] ?? '127.0.0.1';
$db   = $env['DB_DATABASE'] ?? 'nautilus';
$user = $env['DB_USERNAME'] ?? 'nautilus';
$pass = $env['DB_PASSWORD'] ?? 'nautilus123';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

echo "Connected to DB\n";

// 1. Ensure Course
$courseName = "PADI Open Water Diver";
$stmt = $pdo->prepare("SELECT id FROM courses WHERE name = ?");
$stmt->execute([$courseName]);
$course = $stmt->fetch();

if (!$course) {
    echo "Creating Course\n";
    $stmt = $pdo->prepare("INSERT INTO courses (course_code, name, duration_days, price, description, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['OWD-01', $courseName, 3, 499.00, 'Learn to dive', 1]);
    $courseId = $pdo->lastInsertId();
} else {
    $courseId = $course['id'];
}
echo "Course ID: $courseId\n";

// 2. Ensure Schedule
$startDate = date('Y-m-d', strtotime('+3 days'));
$endDate = date('Y-m-d', strtotime('+6 days'));

$stmt = $pdo->prepare("SELECT id FROM course_schedules WHERE course_id = ? AND start_date >= CURRENT_DATE");
$stmt->execute([$courseId]);
$schedule = $stmt->fetch();

if (!$schedule) {
    echo "Creating Schedule\n";
    $stmt = $pdo->prepare("INSERT INTO course_schedules (course_id, start_date, end_date, max_students, status, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$courseId, $startDate, $endDate, 8, 'scheduled', 1]);
    $scheduleId = $pdo->lastInsertId();
} else {
    $scheduleId = $schedule['id'];
}
echo "Schedule ID: $scheduleId\n";

// 3. Ensure Waiver Template
$stmt = $pdo->prepare("SELECT id FROM waiver_templates WHERE type = 'training' AND is_active = 1");
$stmt->execute();
$waiver = $stmt->fetch();

if (!$waiver) {
    echo "Creating Waiver Template\n";
    $stmt = $pdo->prepare("INSERT INTO waiver_templates (name, type, content, is_active) VALUES (?, ?, ?, ?)");
    $stmt->execute(['General Training Waiver', 'training', '<body>IM A WAIVER</body>', 1]);
    $waiverId = $pdo->lastInsertId();
} else {
    $waiverId = $waiver['id'];
}
echo "Waiver ID: $waiverId\n";
