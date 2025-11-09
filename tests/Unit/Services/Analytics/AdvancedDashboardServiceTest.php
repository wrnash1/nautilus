<?php

namespace Tests\Unit\Services\Analytics;

use Tests\TestCase;
use App\Services\Analytics\AdvancedDashboardService;

class AdvancedDashboardServiceTest extends TestCase
{
    private AdvancedDashboardService $dashboardService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dashboardService = new AdvancedDashboardService();
    }

    public function testGetDashboardOverview(): void
    {
        $startDate = date('Y-m-01'); // First day of current month
        $endDate = date('Y-m-d');    // Today

        $overview = $this->dashboardService->getDashboardOverview($startDate, $endDate);

        // Verify structure of overview
        $this->assertIsArray($overview);
        $this->assertArrayHasKey('sales_metrics', $overview);
        $this->assertArrayHasKey('customer_metrics', $overview);
        $this->assertArrayHasKey('product_metrics', $overview);
        $this->assertArrayHasKey('inventory_metrics', $overview);
        $this->assertArrayHasKey('course_metrics', $overview);
        $this->assertArrayHasKey('rental_metrics', $overview);
        $this->assertArrayHasKey('trends', $overview);
        $this->assertArrayHasKey('performance', $overview);
    }

    public function testGetSalesMetrics(): void
    {
        // Create test data
        $customer = $this->createTestCustomer();
        $product = $this->createTestProduct(['price' => 100.00, 'cost' => 50.00]);
        $user = $this->createTestUser();
        $_SESSION['user_id'] = $user['id'];

        // Create a transaction
        $stmt = $this->db->prepare(
            "INSERT INTO transactions (transaction_number, customer_id, subtotal, tax, total, status, cashier_id, transaction_date, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $transactionDate = date('Y-m-d H:i:s');
        $stmt->execute([
            'TXN-' . uniqid(),
            $customer['id'],
            100.00,
            8.00,
            108.00,
            'completed',
            $user['id'],
            $transactionDate,
            $transactionDate
        ]);

        $startDate = date('Y-m-01');
        $endDate = date('Y-m-d');

        $metrics = $this->dashboardService->getSalesMetrics($startDate, $endDate);

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('current', $metrics);
        $this->assertArrayHasKey('previous', $metrics);
        $this->assertArrayHasKey('revenue_growth', $metrics);
        $this->assertArrayHasKey('transaction_growth', $metrics);
        $this->assertArrayHasKey('avg_daily_revenue', $metrics);

        // Verify current metrics
        $this->assertGreaterThan(0, $metrics['current']['total_revenue']);
        $this->assertGreaterThan(0, $metrics['current']['total_transactions']);
    }

    public function testGetInventoryMetrics(): void
    {
        // Create test products with various stock levels
        $this->createTestProduct(['stock_quantity' => 100, 'low_stock_threshold' => 10, 'cost' => 50.00, 'price' => 100.00]);
        $this->createTestProduct(['stock_quantity' => 5, 'low_stock_threshold' => 10, 'cost' => 30.00, 'price' => 60.00]);
        $this->createTestProduct(['stock_quantity' => 0, 'low_stock_threshold' => 5, 'cost' => 20.00, 'price' => 40.00]);

        $metrics = $this->dashboardService->getInventoryMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('low_stock_count', $metrics);
        $this->assertArrayHasKey('out_of_stock_count', $metrics);
        $this->assertArrayHasKey('total_inventory_cost', $metrics);
        $this->assertArrayHasKey('total_inventory_retail', $metrics);
        $this->assertArrayHasKey('total_products', $metrics);
        $this->assertArrayHasKey('turnover_ratio_30d', $metrics);

        // Verify counts
        $this->assertGreaterThanOrEqual(1, $metrics['low_stock_count']); // At least the product with stock 5
        $this->assertGreaterThanOrEqual(1, $metrics['out_of_stock_count']); // The product with stock 0
        $this->assertGreaterThan(0, $metrics['total_inventory_cost']);
    }

    public function testGetCustomerMetrics(): void
    {
        // Create test customers
        $customer1 = $this->createTestCustomer();
        $customer2 = $this->createTestCustomer();
        $user = $this->createTestUser();
        $_SESSION['user_id'] = $user['id'];

        // Create transactions for these customers
        $stmt = $this->db->prepare(
            "INSERT INTO transactions (transaction_number, customer_id, subtotal, tax, total, status, cashier_id, transaction_date, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $transactionDate = date('Y-m-d H:i:s');
        $stmt->execute([
            'TXN-' . uniqid(),
            $customer1['id'],
            100.00,
            8.00,
            108.00,
            'completed',
            $user['id'],
            $transactionDate,
            $transactionDate
        ]);

        $startDate = date('Y-m-01');
        $endDate = date('Y-m-d');

        $metrics = $this->dashboardService->getCustomerMetrics($startDate, $endDate);

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('new_customers', $metrics);
        $this->assertArrayHasKey('repeat_customers', $metrics);
        $this->assertArrayHasKey('avg_customer_lifetime_value', $metrics);
        $this->assertArrayHasKey('retention_rate', $metrics);
        $this->assertArrayHasKey('top_customers', $metrics);

        // We created customers in the test
        $this->assertGreaterThanOrEqual(2, $metrics['new_customers']);
    }

    public function testGetPerformanceIndicators(): void
    {
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-d');

        $kpis = $this->dashboardService->getPerformanceIndicators($startDate, $endDate);

        $this->assertIsArray($kpis);
        $this->assertArrayHasKey('revenue_per_day', $kpis);
        $this->assertArrayHasKey('transactions_per_day', $kpis);
        $this->assertArrayHasKey('conversion_rate', $kpis);
        $this->assertArrayHasKey('average_order_value', $kpis);
        $this->assertArrayHasKey('gross_profit_margin', $kpis);

        // All KPIs should be numeric
        $this->assertIsNumeric($kpis['revenue_per_day']);
        $this->assertIsNumeric($kpis['transactions_per_day']);
        $this->assertIsNumeric($kpis['average_order_value']);
    }
}
