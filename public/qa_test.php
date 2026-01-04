<?php
/**
 * Comprehensive QA Testing Script
 * Tests all critical features and generates a report
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

// Initialize database
$db = Database::getInstance()->getConnection();

echo "===========================================\n";
echo "  NAUTILUS COMPREHENSIVE QA TEST REPORT\n";
echo "===========================================\n\n";

// 1. Check Admin Users
echo "1. ADMIN USERS\n";
echo str_repeat("-", 50) . "\n";
try {
    $stmt = $db->query("SELECT id, first_name, last_name, email, role FROM users WHERE role IN ('admin', 'owner') LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf(
            "  ID: %d | %s %s | %s | Role: %s\n",
            $row['id'],
            $row['first_name'],
            $row['last_name'],
            $row['email'],
            $row['role']
        );
    }
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// 2. Database Statistics
echo "2. DATABASE STATISTICS\n";
echo str_repeat("-", 50) . "\n";
$tables = [
    'products' => 'Active products (is_active=1)',
    'customers' => 'Total customers',
    'courses' => 'Active courses (is_active=1)',
    'users' => 'Total users',
    'transactions' => 'Total transactions',
    'rental_equipment' => 'Rental equipment items'
];

foreach ($tables as $table => $description) {
    try {
        if ($table === 'products' || $table === 'courses') {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table WHERE is_active = 1");
        } else {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
        }
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo sprintf("  %-40s: %d\n", $description, $count);
    } catch (Exception $e) {
        echo sprintf("  %-40s: ERROR - %s\n", $description, $e->getMessage());
    }
}
echo "\n";

// 3. Check Critical Tables Exist
echo "3. CRITICAL TABLES CHECK\n";
echo str_repeat("-", 50) . "\n";
$criticalTables = [
    'products',
    'customers',
    'courses',
    'course_schedules',
    'rental_equipment',
    'compressor_maintenance',
    'air_fills',
    'work_orders',
    'transactions',
    'newsletter_subscriptions',
    'student_medical_forms',
    'student_liability_waivers',
    'student_training_records',
    'certification_agencies',
    'rental_agreements',
    'rental_reservations'
];

foreach ($criticalTables as $table) {
    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo sprintf("  %-35s: ✓ EXISTS (%d records)\n", $table, $count);
    } catch (Exception $e) {
        echo sprintf("  %-35s: ✗ MISSING or ERROR\n", $table);
    }
}
echo "\n";

// 4. Check Business Settings
echo "4. BUSINESS SETTINGS\n";
echo str_repeat("-", 50) . "\n";
try {
    $settings = ['business_name', 'business_phone', 'business_email', 'business_address', 'business_city', 'business_state', 'business_zip'];
    foreach ($settings as $key) {
        $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        echo sprintf("  %-20s: %s\n", $key, $value ?: 'NOT SET');
    }
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Check PADI Agencies
echo "5. CERTIFICATION AGENCIES\n";
echo str_repeat("-", 50) . "\n";
try {
    $stmt = $db->query("SELECT id, name, code FROM certification_agencies WHERE is_active = 1");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("  ID: %d | %s (%s)\n", $row['id'], $row['name'], $row['code']);
    }
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// 6. Check Compressor Maintenance
echo "6. COMPRESSOR MAINTENANCE RECORDS\n";
echo str_repeat("-", 50) . "\n";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM compressor_maintenance");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "  Total maintenance records: $count\n";

    $stmt = $db->query("SELECT maintenance_type, COUNT(*) as count FROM compressor_maintenance GROUP BY maintenance_type");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("    - %s: %d\n", $row['maintenance_type'], $row['count']);
    }
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// 7. Check Recent Transactions
echo "7. RECENT TRANSACTIONS\n";
echo str_repeat("-", 50) . "\n";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM transactions WHERE DATE(created_at) = CURDATE()");
    $today = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "  Transactions today: $today\n";

    $stmt = $db->query("SELECT SUM(total_amount) as total FROM transactions WHERE DATE(created_at) = CURDATE()");
    $totalSales = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
    echo "  Total sales today: $" . number_format($totalSales, 2) . "\n";
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// 8. Check Course Enrollments
echo "8. COURSE ENROLLMENTS\n";
echo str_repeat("-", 50) . "\n";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM course_enrollments");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "  Total enrollments: $count\n";

    $stmt = $db->query("SELECT status, COUNT(*) as count FROM course_enrollments GROUP BY status");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("    - %s: %d\n", $row['status'], $row['count']);
    }
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

echo "===========================================\n";
echo "  QA TEST REPORT COMPLETE\n";
echo "===========================================\n";
