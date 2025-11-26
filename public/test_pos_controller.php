<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Database connected successfully.\n";

    // 1. Test Products Query
    echo "Testing Products Query...\n";
    $stmt = $pdo->query("SELECT * FROM products LIMIT 1");
    $products = $stmt->fetchAll();
    echo "Products found: " . count($products) . "\n";

    // 2. Test Customers Query
    echo "Testing Customers Query...\n";
    $stmt = $pdo->query("SELECT * FROM customers LIMIT 1");
    $customers = $stmt->fetchAll();
    echo "Customers found: " . count($customers) . "\n";

    // 3. Test Courses Query
    echo "Testing Courses Query...\n";
    $stmt = $pdo->query("
        SELECT id, course_code, name, price, duration_days, max_students
        FROM courses
        WHERE is_active = 1
        ORDER BY name
    ");
    $courses = $stmt->fetchAll();
    echo "Courses found: " . count($courses) . "\n";

    // 4. Test Rentals Query
    echo "Testing Rentals Query...\n";
    $stmt = $pdo->query("
        SELECT id, name, daily_rate, stock_quantity, sku
        FROM rental_equipment
        WHERE is_active = 1 AND stock_quantity > 0
        ORDER BY name
    ");
    $rentals = $stmt->fetchAll();
    echo "Rentals found: " . count($rentals) . "\n";

    // 5. Test Trips Query
    echo "Testing Trips Query...\n";
    $stmt = $pdo->query("
        SELECT t.id, t.name, t.price, t.start_date, t.max_spots,
               (SELECT COUNT(*) FROM trip_bookings WHERE trip_id = t.id AND status != 'cancelled') as booked_spots
        FROM trips t
        WHERE t.start_date >= CURDATE() AND t.status = 'scheduled'
        ORDER BY t.start_date
    ");
    $trips = $stmt->fetchAll();
    echo "Trips found: " . count($trips) . "\n";

    echo "All queries executed successfully.\n";

} catch (\PDOException $e) {
    echo "PDO Error: " . $e->getMessage() . "\n";
} catch (\Throwable $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
