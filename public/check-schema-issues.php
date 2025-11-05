<?php
/**
 * Check for common schema issues
 * Run at: https://nautilus.local/check-schema-issues.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

echo "<h2>Schema Issue Checker</h2><pre>";

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Checking common schema issues...\n\n";

    // Check cash_drawer_sessions table
    echo "=== cash_drawer_sessions ===\n";
    $stmt = $pdo->query("DESCRIBE cash_drawer_sessions");
    $columns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

    $expected = ['drawer_id', 'status', 'difference', 'variance', 'register_id'];
    foreach ($expected as $col) {
        $exists = in_array($col, $columns);
        $icon = $exists ? '✓' : '✗';
        echo "  $icon $col: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
    }

    // Check air_fills table
    echo "\n=== air_fills ===\n";
    try {
        $stmt = $pdo->query("DESCRIBE air_fills");
        echo "  ✓ Table exists\n";
        $columns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
        echo "  Columns: " . implode(', ', $columns) . "\n";
    } catch (Exception $e) {
        echo "  ✗ Table missing or error: " . $e->getMessage() . "\n";
    }

    // Check rental_equipment table
    echo "\n=== rental_equipment ===\n";
    try {
        $stmt = $pdo->query("DESCRIBE rental_equipment");
        echo "  ✓ Table exists\n";
        $hasNextInspection = in_array('next_inspection_due', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field'));
        echo "  Has next_inspection_due: " . ($hasNextInspection ? '✓ YES' : '✗ NO') . "\n";
    } catch (Exception $e) {
        echo "  ✗ Table missing or error: " . $e->getMessage() . "\n";
    }

    // Check all tables exist
    echo "\n=== Critical Tables ===\n";
    $criticalTables = [
        'users', 'roles', 'permissions', 'customers', 'products',
        'transactions', 'transaction_items', 'categories', 'vendors',
        'cash_drawer_sessions', 'rental_equipment', 'rental_reservations',
        'courses', 'course_schedules', 'course_enrollments',
        'trips', 'trip_schedules', 'trip_bookings'
    ];

    foreach ($criticalTables as $table) {
        try {
            $pdo->query("SELECT 1 FROM $table LIMIT 1");
            echo "  ✓ $table\n";
        } catch (Exception $e) {
            echo "  ✗ $table - MISSING\n";
        }
    }

    echo "\n=== Database Summary ===\n";
    $stmt = $pdo->query("
        SELECT
            (SELECT COUNT(*) FROM users) as users,
            (SELECT COUNT(*) FROM customers) as customers,
            (SELECT COUNT(*) FROM products) as products,
            (SELECT COUNT(*) FROM transactions) as transactions
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    foreach ($stats as $table => $count) {
        echo "  $table: $count\n";
    }

    echo "\n✓ Schema check complete\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo '<p><a href="/store">Back to Dashboard</a></p>';
?>