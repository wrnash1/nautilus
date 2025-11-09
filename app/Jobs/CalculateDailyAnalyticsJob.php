<?php

namespace App\Jobs;

use App\Core\Database;
use App\Core\Logger;

/**
 * Calculate Daily Analytics Job
 *
 * Calculates and stores daily KPIs, trends, and analytics
 * Run once per day at 1 AM
 *
 * Cron: 0 1 * * * php /path/to/nautilus/app/Jobs/CalculateDailyAnalyticsJob.php
 */
class CalculateDailyAnalyticsJob
{
    private Logger $logger;
    private array $results = [];

    public function __construct()
    {
        $this->logger = new Logger();
    }

    public function execute(): void
    {
        $this->logger->info('Starting daily analytics calculation job');
        $startTime = microtime(true);

        try {
            // Calculate yesterday's KPIs
            $yesterday = date('Y-m-d', strtotime('-1 day'));

            $this->calculateBusinessKPIs($yesterday);
            $this->calculateSalesTrends($yesterday);
            $this->updateCustomerAnalytics();
            $this->updateProductAnalytics($yesterday);
            $this->cleanupExpiredCache();

            $duration = round(microtime(true) - $startTime, 2);
            $this->logger->info('Daily analytics calculation completed', [
                'duration' => $duration,
                'results' => $this->results
            ]);

            $this->outputSummary($duration);

        } catch (\Exception $e) {
            $this->logger->error('Daily analytics calculation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            echo "ERROR: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Calculate business KPIs for a specific date
     */
    private function calculateBusinessKPIs(string $date): void
    {
        try {
            // Sales KPIs
            $salesData = Database::fetchOne(
                "SELECT
                    COALESCE(SUM(total), 0) as total_revenue,
                    COUNT(*) as total_transactions,
                    COALESCE(AVG(total), 0) as average_order_value
                 FROM transactions
                 WHERE DATE(transaction_date) = ?
                 AND status = 'completed'",
                [$date]
            );

            // Customer KPIs
            $customerData = Database::fetchOne(
                "SELECT
                    COUNT(DISTINCT CASE WHEN DATE(created_at) = ? THEN id END) as new_customers,
                    COUNT(DISTINCT t.customer_id) as customers_with_purchases
                 FROM customers c
                 LEFT JOIN transactions t ON c.id = t.customer_id
                    AND DATE(t.transaction_date) = ?
                    AND t.status = 'completed'",
                [$date, $date]
            );

            // Calculate retention rate (customers from 30 days ago who purchased today)
            $retentionData = Database::fetchOne(
                "SELECT COUNT(DISTINCT t2.customer_id) as retained
                 FROM transactions t1
                 INNER JOIN transactions t2 ON t1.customer_id = t2.customer_id
                 WHERE DATE(t1.transaction_date) = DATE_SUB(?, INTERVAL 30 DAY)
                 AND DATE(t2.transaction_date) = ?
                 AND t1.status = 'completed'
                 AND t2.status = 'completed'",
                [$date, $date]
            );

            // Product KPIs
            $productData = Database::fetchOne(
                "SELECT COALESCE(SUM(ti.quantity), 0) as units_sold
                 FROM transaction_items ti
                 INNER JOIN transactions t ON ti.transaction_id = t.id
                 WHERE DATE(t.transaction_date) = ?
                 AND t.status = 'completed'",
                [$date]
            );

            // Course KPIs
            $courseData = Database::fetchOne(
                "SELECT
                    COUNT(*) as course_enrollments,
                    COALESCE(SUM(amount_paid), 0) as course_revenue
                 FROM course_enrollments
                 WHERE DATE(enrollment_date) = ?",
                [$date]
            );

            // Rental KPIs
            $rentalData = Database::fetchOne(
                "SELECT
                    COUNT(*) as rental_count,
                    COALESCE(SUM(total_cost), 0) as rental_revenue
                 FROM rental_transactions
                 WHERE DATE(rental_date) = ?
                 AND status != 'cancelled'",
                [$date]
            );

            // Operational KPIs
            $lowStockCount = Database::fetchOne(
                "SELECT COUNT(*) as count
                 FROM products
                 WHERE stock_quantity <= low_stock_threshold
                 AND is_active = 1"
            );

            // Insert or update business_kpis
            Database::query(
                "INSERT INTO business_kpis (
                    kpi_date, total_revenue, total_transactions, average_order_value,
                    new_customers, repeat_customers, customer_retention_rate,
                    units_sold, course_enrollments, course_revenue,
                    rental_count, rental_revenue, low_stock_items
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    total_revenue = VALUES(total_revenue),
                    total_transactions = VALUES(total_transactions),
                    average_order_value = VALUES(average_order_value),
                    new_customers = VALUES(new_customers),
                    updated_at = NOW()",
                [
                    $date,
                    $salesData['total_revenue'],
                    $salesData['total_transactions'],
                    $salesData['average_order_value'],
                    $customerData['new_customers'],
                    $customerData['customers_with_purchases'],
                    0, // Will calculate retention rate separately
                    $productData['units_sold'],
                    $courseData['course_enrollments'],
                    $courseData['course_revenue'],
                    $rentalData['rental_count'],
                    $rentalData['rental_revenue'],
                    $lowStockCount['count']
                ]
            );

            $this->results['business_kpis'] = 'calculated';
            $this->logger->info("Calculated business KPIs for {$date}");

        } catch (\Exception $e) {
            $this->logger->error('Failed to calculate business KPIs', ['error' => $e->getMessage()]);
            $this->results['business_kpis'] = 'failed';
        }
    }

    /**
     * Calculate sales trends
     */
    private function calculateSalesTrends(string $date): void
    {
        try {
            // Get daily sales data
            $salesData = Database::fetchOne(
                "SELECT
                    COALESCE(SUM(total), 0) as daily_revenue,
                    COUNT(*) as daily_transactions,
                    COALESCE(SUM(ti.quantity), 0) as daily_units_sold
                 FROM transactions t
                 LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
                 WHERE DATE(t.transaction_date) = ?
                 AND t.status = 'completed'",
                [$date]
            );

            // Payment method breakdown
            $paymentBreakdown = Database::fetchAll(
                "SELECT
                    p.payment_method,
                    COALESCE(SUM(p.amount), 0) as amount
                 FROM payments p
                 INNER JOIN transactions t ON p.transaction_id = t.id
                 WHERE DATE(t.transaction_date) = ?
                 AND p.status = 'completed'
                 AND t.status = 'completed'
                 GROUP BY p.payment_method",
                [$date]
            );

            $cashSales = 0;
            $creditSales = 0;
            $otherSales = 0;

            foreach ($paymentBreakdown as $payment) {
                switch (strtolower($payment['payment_method'])) {
                    case 'cash':
                        $cashSales = $payment['amount'];
                        break;
                    case 'credit_card':
                    case 'debit_card':
                        $creditSales += $payment['amount'];
                        break;
                    default:
                        $otherSales += $payment['amount'];
                }
            }

            // Category breakdown
            $categoryBreakdown = Database::fetchAll(
                "SELECT
                    COALESCE(pc.name, 'Uncategorized') as category,
                    COALESCE(SUM(ti.total), 0) as revenue
                 FROM transaction_items ti
                 INNER JOIN transactions t ON ti.transaction_id = t.id
                 LEFT JOIN products p ON ti.product_id = p.id
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 WHERE DATE(t.transaction_date) = ?
                 AND t.status = 'completed'
                 GROUP BY pc.id",
                [$date]
            );

            $categoryData = [];
            foreach ($categoryBreakdown as $cat) {
                $categoryData[$cat['category']] = floatval($cat['revenue']);
            }

            // Get comparison data
            $prevDayRevenue = Database::fetchOne(
                "SELECT COALESCE(SUM(total), 0) as revenue
                 FROM transactions
                 WHERE DATE(transaction_date) = DATE_SUB(?, INTERVAL 1 DAY)
                 AND status = 'completed'",
                [$date]
            );

            // Determine trend direction
            $currentRevenue = $salesData['daily_revenue'];
            $previousRevenue = $prevDayRevenue['revenue'];

            if ($currentRevenue > $previousRevenue * 1.05) {
                $trendDirection = 'increasing';
            } elseif ($currentRevenue < $previousRevenue * 0.95) {
                $trendDirection = 'decreasing';
            } else {
                $trendDirection = 'stable';
            }

            // Insert sales trend data
            Database::query(
                "INSERT INTO sales_trends (
                    trend_date, daily_revenue, daily_transactions, daily_units_sold,
                    cash_sales, credit_sales, other_sales,
                    category_breakdown, trend_direction
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    daily_revenue = VALUES(daily_revenue),
                    daily_transactions = VALUES(daily_transactions),
                    daily_units_sold = VALUES(daily_units_sold),
                    updated_at = NOW()",
                [
                    $date,
                    $salesData['daily_revenue'],
                    $salesData['daily_transactions'],
                    $salesData['daily_units_sold'],
                    $cashSales,
                    $creditSales,
                    $otherSales,
                    json_encode($categoryData),
                    $trendDirection
                ]
            );

            $this->results['sales_trends'] = 'calculated';
            $this->logger->info("Calculated sales trends for {$date}");

        } catch (\Exception $e) {
            $this->logger->error('Failed to calculate sales trends', ['error' => $e->getMessage()]);
            $this->results['sales_trends'] = 'failed';
        }
    }

    /**
     * Update customer analytics
     */
    private function updateCustomerAnalytics(): void
    {
        try {
            // Update analytics for all active customers
            $customers = Database::fetchAll(
                "SELECT id FROM customers WHERE is_active = 1"
            );

            $updated = 0;
            foreach ($customers as $customer) {
                $this->updateSingleCustomerAnalytics($customer['id']);
                $updated++;
            }

            $this->results['customer_analytics'] = $updated;
            $this->logger->info("Updated analytics for {$updated} customers");

        } catch (\Exception $e) {
            $this->logger->error('Failed to update customer analytics', ['error' => $e->getMessage()]);
            $this->results['customer_analytics'] = 'failed';
        }
    }

    /**
     * Update analytics for a single customer
     */
    private function updateSingleCustomerAnalytics(int $customerId): void
    {
        $data = Database::fetchOne(
            "SELECT
                COUNT(*) as total_purchases,
                COALESCE(SUM(t.total), 0) as total_spent,
                COALESCE(AVG(t.total), 0) as average_order_value,
                MAX(t.transaction_date) as last_purchase_date
             FROM transactions t
             WHERE t.customer_id = ?
             AND t.status = 'completed'",
            [$customerId]
        );

        // Calculate days since last purchase
        $daysSince = null;
        if ($data['last_purchase_date']) {
            $daysSince = (strtotime('now') - strtotime($data['last_purchase_date'])) / 86400;
        }

        // Determine customer segment
        $segment = 'new';
        if ($data['total_purchases'] == 0) {
            $segment = 'new';
        } elseif ($daysSince > 180) {
            $segment = 'dormant';
        } elseif ($daysSince > 90) {
            $segment = 'at_risk';
        } elseif ($data['total_spent'] > 5000) {
            $segment = 'vip';
        } else {
            $segment = 'active';
        }

        Database::query(
            "INSERT INTO customer_analytics (
                customer_id, total_purchases, total_spent, average_order_value,
                last_purchase_date, days_since_last_purchase,
                customer_lifetime_value, customer_segment, calculated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                total_purchases = VALUES(total_purchases),
                total_spent = VALUES(total_spent),
                average_order_value = VALUES(average_order_value),
                last_purchase_date = VALUES(last_purchase_date),
                days_since_last_purchase = VALUES(days_since_last_purchase),
                customer_lifetime_value = VALUES(customer_lifetime_value),
                customer_segment = VALUES(customer_segment),
                calculated_at = NOW()",
            [
                $customerId,
                $data['total_purchases'],
                $data['total_spent'],
                $data['average_order_value'],
                $data['last_purchase_date'],
                $daysSince,
                $data['total_spent'], // CLV = total spent for now
                $segment
            ]
        );
    }

    /**
     * Update product analytics
     */
    private function updateProductAnalytics(string $date): void
    {
        try {
            // Get all products sold yesterday
            $products = Database::fetchAll(
                "SELECT DISTINCT ti.product_id
                 FROM transaction_items ti
                 INNER JOIN transactions t ON ti.transaction_id = t.id
                 WHERE DATE(t.transaction_date) = ?
                 AND t.status = 'completed'",
                [$date]
            );

            $updated = 0;
            foreach ($products as $product) {
                $this->updateSingleProductAnalytics($product['product_id'], $date);
                $updated++;
            }

            $this->results['product_analytics'] = $updated;
            $this->logger->info("Updated analytics for {$updated} products");

        } catch (\Exception $e) {
            $this->logger->error('Failed to update product analytics', ['error' => $e->getMessage()]);
            $this->results['product_analytics'] = 'failed';
        }
    }

    /**
     * Update analytics for a single product
     */
    private function updateSingleProductAnalytics(int $productId, string $date): void
    {
        $data = Database::fetchOne(
            "SELECT
                COALESCE(SUM(ti.quantity), 0) as units_sold,
                COALESCE(SUM(ti.total), 0) as revenue,
                COUNT(DISTINCT t.customer_id) as unique_customers
             FROM transaction_items ti
             INNER JOIN transactions t ON ti.transaction_id = t.id
             WHERE ti.product_id = ?
             AND DATE(t.transaction_date) = ?
             AND t.status = 'completed'",
            [$productId, $date]
        );

        Database::query(
            "INSERT INTO product_analytics (
                product_id, period_type, period_start, period_end,
                units_sold, revenue, unique_customers
            ) VALUES (?, 'daily', ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                units_sold = VALUES(units_sold),
                revenue = VALUES(revenue),
                unique_customers = VALUES(unique_customers),
                updated_at = NOW()",
            [
                $productId,
                $date,
                $date,
                $data['units_sold'],
                $data['revenue'],
                $data['unique_customers']
            ]
        );
    }

    /**
     * Cleanup expired cache entries
     */
    private function cleanupExpiredCache(): void
    {
        try {
            $result = Database::query(
                "DELETE FROM dashboard_metrics_cache
                 WHERE expires_at IS NOT NULL
                 AND expires_at < NOW()"
            );

            $this->results['cache_cleanup'] = 'completed';
            $this->logger->info("Cleaned up expired cache entries");

        } catch (\Exception $e) {
            $this->logger->error('Failed to cleanup cache', ['error' => $e->getMessage()]);
            $this->results['cache_cleanup'] = 'failed';
        }
    }

    /**
     * Output summary
     */
    private function outputSummary(float $duration): void
    {
        echo "==================================================\n";
        echo "Daily Analytics Calculation Summary\n";
        echo "==================================================\n";
        echo "Execution Time: {$duration}s\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($this->results as $type => $result) {
            $displayName = ucwords(str_replace('_', ' ', $type));
            echo "{$displayName}: {$result}\n";
        }

        echo "\n==================================================\n";
    }
}

// Allow running from command line
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . '/../../vendor/autoload.php';

    if (file_exists(__DIR__ . '/../../.env')) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();
    }

    $job = new CalculateDailyAnalyticsJob();
    $job->execute();
}
