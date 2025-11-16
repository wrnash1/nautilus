<?php
/**
 * Nautilus System Verification Script
 * Run this after installation to verify everything works correctly
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/tests/SystemIntegrationTest.php';

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════════╗\n";
echo "║         NAUTILUS SYSTEM VERIFICATION & HEALTH CHECK              ║\n";
echo "╚═══════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Step 1: Check PHP version
echo "1️⃣  Checking PHP Version...\n";
$phpVersion = phpversion();
$minVersion = '8.0.0';
if (version_compare($phpVersion, $minVersion, '>=')) {
    echo "   ✅ PHP $phpVersion (meets minimum $minVersion)\n\n";
} else {
    echo "   ❌ PHP $phpVersion (requires $minVersion or higher)\n\n";
    exit(1);
}

// Step 2: Check required extensions
echo "2️⃣  Checking Required PHP Extensions...\n";
$required = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'curl'];
$missing = [];

foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ $ext\n";
    } else {
        echo "   ❌ $ext (missing)\n";
        $missing[] = $ext;
    }
}

if (empty($missing)) {
    echo "   ✅ All required extensions loaded\n\n";
} else {
    echo "   ❌ Missing extensions: " . implode(', ', $missing) . "\n\n";
    exit(1);
}

// Step 3: Check database connection
echo "3️⃣  Checking Database Connection...\n";
try {
    $db = new PDO(
        "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME'),
        getenv('DB_USER'),
        getenv('DB_PASS'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "   ✅ Database connected successfully\n\n";
} catch (PDOException $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Step 4: Check migrations
echo "4️⃣  Verifying Database Migrations...\n";
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$expectedTables = [
    'customers', 'bookings', 'courses', 'equipment_inventory',
    'inventory_stock_levels', 'security_cameras', 'communication_channels',
    'pos_terminals', 'loyalty_programs', 'travel_packages',
    'dashboards', 'kpi_definitions', 'diving_clubs', 'layaway_agreements'
];

$missingTables = [];
foreach ($expectedTables as $table) {
    if (in_array($table, $tables)) {
        echo "   ✅ $table\n";
    } else {
        echo "   ❌ $table (missing)\n";
        $missingTables[] = $table;
    }
}

if (empty($missingTables)) {
    echo "   ✅ All core tables present (" . count($tables) . " total tables)\n\n";
} else {
    echo "   ❌ Missing tables: " . implode(', ', $missingTables) . "\n";
    echo "   Run all migration files in order\n\n";
    exit(1);
}

// Step 5: Check sample data
echo "5️⃣  Verifying Sample Data...\n";
$sampleData = [
    'inventory_locations' => 4,
    'security_cameras' => 6,
    'communication_channels' => 3,
    'diving_clubs' => 4,
    'layaway_plans' => 2
];

foreach ($sampleData as $table => $expectedCount) {
    $count = $db->query("SELECT COUNT(*) FROM $table WHERE tenant_id = 1")->fetchColumn();
    if ($count >= $expectedCount) {
        echo "   ✅ $table: $count records (expected $expectedCount+)\n";
    } else {
        echo "   ⚠️  $table: $count records (expected $expectedCount+)\n";
    }
}
echo "\n";

// Step 6: Check service classes
echo "6️⃣  Checking Service Classes...\n";
$services = [
    'app/Services/Travel/TravelBookingService.php',
    'app/Services/Analytics/BusinessIntelligenceService.php',
    'app/Services/Analytics/CustomerAnalyticsService.php',
    'app/Services/Club/DivingClubService.php',
    'app/Services/Financial/LayawayService.php'
];

foreach ($services as $service) {
    if (file_exists(__DIR__ . '/' . $service)) {
        echo "   ✅ " . basename($service) . "\n";
    } else {
        echo "   ❌ " . basename($service) . " (missing)\n";
    }
}
echo "\n";

// Step 7: Run integration tests
echo "7️⃣  Running Integration Tests...\n\n";
$tester = new SystemIntegrationTest($db);
$report = $tester->runAllTests();

// Step 8: Final summary
echo "\n";
echo "╔═══════════════════════════════════════════════════════════════════╗\n";
echo "║                    VERIFICATION COMPLETE                          ║\n";
echo "╚═══════════════════════════════════════════════════════════════════╝\n";
echo "\n";

if ($report['pass_rate'] >= 95) {
    echo "✅ SUCCESS! Nautilus is fully operational and ready for production.\n\n";
    echo "Next Steps:\n";
    echo "1. Review your .env configuration\n";
    echo "2. Set up your first tenant/dive shop\n";
    echo "3. Create admin user account\n";
    echo "4. Import your existing data (optional)\n";
    echo "5. Train your staff\n";
    echo "6. Start using Nautilus!\n\n";
    echo "📚 Documentation: See README.md and QUICK_START_GUIDE.md\n";
    echo "💡 Examples: See SIMPLE_USAGE_GUIDE.md\n\n";
} else {
    echo "⚠️  WARNING: Some tests failed. Review the results above.\n\n";
    echo "Common Issues:\n";
    echo "- Missing database tables: Run all migrations\n";
    echo "- Missing sample data: Re-run migrations 092-098\n";
    echo "- Permission errors: Check file/database permissions\n\n";
}

echo "═══════════════════════════════════════════════════════════════════\n\n";
