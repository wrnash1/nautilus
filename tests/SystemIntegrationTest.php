<?php

/**
 * System Integration Tests
 * Verify all modules work together correctly
 */

class SystemIntegrationTest
{
    private PDO $db;
    private array $testResults = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Run all integration tests
     */
    public function runAllTests(): array
    {
        echo "🧪 Running Nautilus System Integration Tests...\n\n";

        $this->testDatabaseStructure();
        $this->testCoreFunctionality();
        $this->testEnterpriseFeatures();
        $this->testDataIntegrity();
        $this->testPerformance();

        return $this->generateReport();
    }

    /**
     * Test 1: Database Structure
     */
    private function testDatabaseStructure(): void
    {
        echo "📊 Testing Database Structure...\n";

        // Count migrations
        $tables = $this->db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $this->addResult('Database Tables', count($tables) >= 200,
            count($tables) . " tables found (expected 200+)");

        // Check key tables exist
        $keyTables = [
            'customers', 'bookings', 'courses', 'equipment_inventory',
            'inventory_stock_levels', 'security_cameras', 'communication_channels',
            'pos_terminals', 'loyalty_programs', 'travel_packages',
            'dashboards', 'kpi_definitions', 'diving_clubs', 'layaway_agreements'
        ];

        foreach ($keyTables as $table) {
            $exists = in_array($table, $tables);
            $this->addResult("Table: $table", $exists, $exists ? "✓" : "Missing");
        }

        // Check indexes
        $indexQuery = $this->db->query("
            SELECT COUNT(*) FROM information_schema.statistics
            WHERE table_schema = DATABASE()
        ")->fetchColumn();
        $this->addResult('Database Indexes', $indexQuery >= 500,
            "$indexQuery indexes found (expected 500+)");

        echo "  ✓ Database structure tests complete\n\n";
    }

    /**
     * Test 2: Core Functionality
     */
    private function testCoreFunctionality(): void
    {
        echo "⚙️  Testing Core Functionality...\n";

        // Test customer creation
        try {
            $stmt = $this->db->prepare("
                INSERT INTO customers (tenant_id, first_name, last_name, email)
                VALUES (1, 'Test', 'Customer', 'test@example.com')
            ");
            $stmt->execute();
            $customerId = $this->db->lastInsertId();
            $this->addResult('Customer Creation', true, "Customer ID: $customerId");

            // Test booking creation
            $stmt = $this->db->prepare("
                INSERT INTO bookings (tenant_id, customer_id, booking_date, total_amount)
                VALUES (1, ?, CURDATE(), 100.00)
            ");
            $stmt->execute([$customerId]);
            $bookingId = $this->db->lastInsertId();
            $this->addResult('Booking Creation', true, "Booking ID: $bookingId");

            // Test payment
            $stmt = $this->db->prepare("
                INSERT INTO payments (tenant_id, customer_id, amount, payment_method, status)
                VALUES (1, ?, 100.00, 'credit_card', 'completed')
            ");
            $stmt->execute([$customerId]);
            $this->addResult('Payment Processing', true, "✓");

            // Cleanup
            $this->db->exec("DELETE FROM payments WHERE customer_id = $customerId");
            $this->db->exec("DELETE FROM bookings WHERE customer_id = $customerId");
            $this->db->exec("DELETE FROM customers WHERE id = $customerId");

        } catch (Exception $e) {
            $this->addResult('Core Functionality', false, "Error: " . $e->getMessage());
        }

        echo "  ✓ Core functionality tests complete\n\n";
    }

    /**
     * Test 3: Enterprise Features
     */
    private function testEnterpriseFeatures(): void
    {
        echo "🏢 Testing Enterprise Features...\n";

        // Test inventory locations
        $locations = $this->db->query("
            SELECT COUNT(*) FROM inventory_locations WHERE tenant_id = 1
        ")->fetchColumn();
        $this->addResult('Inventory Locations', $locations >= 4,
            "$locations locations (expected 4+)");

        // Test security cameras
        $cameras = $this->db->query("
            SELECT COUNT(*) FROM security_cameras WHERE tenant_id = 1
        ")->fetchColumn();
        $this->addResult('Security Cameras', $cameras >= 6,
            "$cameras cameras (expected 6+)");

        // Test communication channels
        $channels = $this->db->query("
            SELECT COUNT(*) FROM communication_channels WHERE tenant_id = 1
        ")->fetchColumn();
        $this->addResult('Communication Channels', $channels >= 3,
            "$channels channels (expected 3+)");

        // Test POS terminals
        $terminals = $this->db->query("
            SELECT COUNT(*) FROM pos_terminals WHERE tenant_id = 1
        ")->fetchColumn();
        $this->addResult('POS Terminals', $terminals >= 2,
            "$terminals terminals (expected 2+)");

        // Test diving clubs
        $clubs = $this->db->query("
            SELECT COUNT(*) FROM diving_clubs WHERE tenant_id = 1
        ")->fetchColumn();
        $this->addResult('Diving Clubs', $clubs >= 4,
            "$clubs clubs (expected 4+)");

        // Test layaway plans
        $plans = $this->db->query("
            SELECT COUNT(*) FROM layaway_plans WHERE tenant_id = 1
        ")->fetchColumn();
        $this->addResult('Layaway Plans', $plans >= 2,
            "$plans plans (expected 2+)");

        echo "  ✓ Enterprise features tests complete\n\n";
    }

    /**
     * Test 4: Data Integrity
     */
    private function testDataIntegrity(): void
    {
        echo "🔒 Testing Data Integrity...\n";

        // Test foreign key constraints
        try {
            // This should fail due to FK constraint
            $this->db->exec("
                INSERT INTO bookings (tenant_id, customer_id, booking_date)
                VALUES (1, 999999, CURDATE())
            ");
            $this->addResult('Foreign Key Constraints', false,
                "FK constraint not enforced");
        } catch (Exception $e) {
            $this->addResult('Foreign Key Constraints', true,
                "✓ Properly enforced");
        }

        // Test unique constraints
        $uniqueFields = [
            ['table' => 'diving_clubs', 'field' => 'club_code'],
            ['table' => 'layaway_agreements', 'field' => 'agreement_number']
        ];

        foreach ($uniqueFields as $test) {
            $hasUnique = $this->hasUniqueConstraint($test['table'], $test['field']);
            $this->addResult("Unique: {$test['table']}.{$test['field']}",
                $hasUnique, $hasUnique ? "✓" : "Missing");
        }

        // Test tenant isolation
        $hasTermantId = $this->db->query("
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND column_name = 'tenant_id'
        ")->fetchColumn();
        $this->addResult('Multi-Tenant Isolation', $hasTermantId >= 180,
            "$hasTermantId tables with tenant_id (expected 180+)");

        echo "  ✓ Data integrity tests complete\n\n";
    }

    /**
     * Test 5: Performance
     */
    private function testPerformance(): void
    {
        echo "⚡ Testing Performance...\n";

        // Test query performance
        $queries = [
            'Customer Lookup' => "SELECT * FROM customers WHERE email = 'test@example.com' LIMIT 1",
            'Booking Search' => "SELECT * FROM bookings WHERE booking_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) LIMIT 100",
            'Inventory Check' => "SELECT * FROM inventory_stock_levels WHERE quantity_available > 0 LIMIT 100",
            'Dashboard Data' => "SELECT COUNT(*) as total FROM bookings WHERE tenant_id = 1"
        ];

        foreach ($queries as $name => $query) {
            $start = microtime(true);
            $this->db->query($query);
            $duration = (microtime(true) - $start) * 1000;

            $this->addResult("Query: $name", $duration < 100,
                sprintf("%.2f ms", $duration));
        }

        // Test index usage
        $explain = $this->db->query("
            EXPLAIN SELECT * FROM customers WHERE email = 'test@example.com'
        ")->fetch(PDO::FETCH_ASSOC);

        $usesIndex = isset($explain['key']) && $explain['key'] !== null;
        $this->addResult('Index Usage', $usesIndex,
            $usesIndex ? "Using index: {$explain['key']}" : "No index used");

        echo "  ✓ Performance tests complete\n\n";
    }

    /**
     * Check if unique constraint exists
     */
    private function hasUniqueConstraint(string $table, string $column): bool
    {
        $result = $this->db->prepare("
            SELECT COUNT(*)
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = ?
              AND column_name = ?
              AND non_unique = 0
        ");
        $result->execute([$table, $column]);
        return $result->fetchColumn() > 0;
    }

    /**
     * Add test result
     */
    private function addResult(string $test, bool $passed, string $details = ''): void
    {
        $this->testResults[] = [
            'test' => $test,
            'passed' => $passed,
            'details' => $details
        ];
    }

    /**
     * Generate test report
     */
    private function generateReport(): array
    {
        $total = count($this->testResults);
        $passed = count(array_filter($this->testResults, fn($r) => $r['passed']));
        $failed = $total - $passed;
        $passRate = ($passed / $total) * 100;

        echo "\n";
        echo "=" . str_repeat("=", 70) . "\n";
        echo "📋 NAUTILUS SYSTEM INTEGRATION TEST REPORT\n";
        echo "=" . str_repeat("=", 70) . "\n\n";

        echo "📊 Summary:\n";
        echo "  Total Tests:  $total\n";
        echo "  ✅ Passed:    $passed\n";
        echo "  ❌ Failed:    $failed\n";
        echo "  📈 Pass Rate: " . number_format($passRate, 1) . "%\n\n";

        echo "📝 Detailed Results:\n\n";

        foreach ($this->testResults as $result) {
            $status = $result['passed'] ? '✅' : '❌';
            $details = $result['details'] ? " - {$result['details']}" : '';
            echo "  $status {$result['test']}$details\n";
        }

        echo "\n" . str_repeat("=", 72) . "\n";

        if ($passRate >= 95) {
            echo "🎉 EXCELLENT! System is fully integrated and functioning correctly!\n";
        } elseif ($passRate >= 80) {
            echo "✅ GOOD! System is functional with minor issues to address.\n";
        } else {
            echo "⚠️  WARNING! System has integration issues that need attention.\n";
        }

        echo str_repeat("=", 72) . "\n\n";

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'pass_rate' => $passRate,
            'results' => $this->testResults,
            'status' => $passRate >= 95 ? 'excellent' : ($passRate >= 80 ? 'good' : 'needs_attention')
        ];
    }
}

// Example usage:
/*
require_once 'config/database.php';

$tester = new SystemIntegrationTest($db);
$report = $tester->runAllTests();

if ($report['pass_rate'] >= 95) {
    echo "✓ All systems operational!\n";
}
*/
