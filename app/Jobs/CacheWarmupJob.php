<?php

namespace App\Jobs;

use App\Core\Database;
use App\Core\Logger;
use App\Services\Analytics\AdvancedDashboardService;

/**
 * Cache Warmup Job
 *
 * Pre-calculates and caches frequently accessed metrics
 * Run every 6 hours for optimal performance
 *
 * Cron: 0 *\/6 * * * php /path/to/nautilus/app/Jobs/CacheWarmupJob.php
 */
class CacheWarmupJob
{
    private Logger $logger;
    private AdvancedDashboardService $dashboardService;
    private array $results = [];

    public function __construct()
    {
        $this->logger = new Logger();
        $this->dashboardService = new AdvancedDashboardService();
    }

    public function execute(): void
    {
        $this->logger->info('Starting cache warmup job');
        $startTime = microtime(true);

        try {
            // Define time periods to cache
            $periods = [
                'today' => [date('Y-m-d'), date('Y-m-d')],
                'yesterday' => [date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day'))],
                'last_7_days' => [date('Y-m-d', strtotime('-7 days')), date('Y-m-d')],
                'last_30_days' => [date('Y-m-d', strtotime('-30 days')), date('Y-m-d')],
                'this_month' => [date('Y-m-01'), date('Y-m-d')],
                'last_month' => [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last month'))]
            ];

            foreach ($periods as $periodName => $dates) {
                $this->warmupPeriod($periodName, $dates[0], $dates[1]);
            }

            $duration = round(microtime(true) - $startTime, 2);
            $this->logger->info('Cache warmup completed', [
                'duration' => $duration,
                'results' => $this->results
            ]);

            $this->outputSummary($duration);

        } catch (\Exception $e) {
            $this->logger->error('Cache warmup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            echo "ERROR: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Warmup cache for a specific period
     */
    private function warmupPeriod(string $periodName, string $startDate, string $endDate): void
    {
        try {
            $calcStart = microtime(true);

            // Calculate sales metrics
            $salesMetrics = $this->dashboardService->getSalesMetrics($startDate, $endDate);
            $this->cacheMetric("sales_metrics_{$periodName}", $salesMetrics, $startDate, $endDate, microtime(true) - $calcStart);

            // Calculate customer metrics
            $calcStart = microtime(true);
            $customerMetrics = $this->dashboardService->getCustomerMetrics($startDate, $endDate);
            $this->cacheMetric("customer_metrics_{$periodName}", $customerMetrics, $startDate, $endDate, microtime(true) - $calcStart);

            // Calculate inventory metrics
            $calcStart = microtime(true);
            $inventoryMetrics = $this->dashboardService->getInventoryMetrics();
            $this->cacheMetric("inventory_metrics_{$periodName}", $inventoryMetrics, $startDate, $endDate, microtime(true) - $calcStart);

            // Calculate KPIs
            $calcStart = microtime(true);
            $kpis = $this->dashboardService->getPerformanceIndicators($startDate, $endDate);
            $this->cacheMetric("kpis_{$periodName}", $kpis, $startDate, $endDate, microtime(true) - $calcStart);

            $this->results[$periodName] = 'cached';
            $this->logger->info("Warmed up cache for {$periodName}");

        } catch (\Exception $e) {
            $this->logger->error("Failed to warmup {$periodName}", ['error' => $e->getMessage()]);
            $this->results[$periodName] = 'failed';
        }
    }

    /**
     * Cache a metric
     */
    private function cacheMetric(string $metricKey, array $data, string $startDate, string $endDate, float $calcTime): void
    {
        // Extract main value if available
        $metricValue = null;
        if (isset($data['current']['total_revenue'])) {
            $metricValue = $data['current']['total_revenue'];
        } elseif (isset($data['total_revenue'])) {
            $metricValue = $data['total_revenue'];
        } elseif (isset($data['revenue_per_day'])) {
            $metricValue = $data['revenue_per_day'];
        }

        // Previous period value
        $previousValue = $data['previous']['total_revenue'] ?? null;

        // Growth rate
        $growthRate = $data['revenue_growth'] ?? null;

        // Calculate expiration (6 hours from now)
        $expiresAt = date('Y-m-d H:i:s', strtotime('+6 hours'));

        Database::query(
            "INSERT INTO dashboard_metrics_cache (
                metric_key, period_type, period_start, period_end,
                metric_value, metric_data, previous_period_value, growth_rate,
                calculation_time, expires_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                metric_value = VALUES(metric_value),
                metric_data = VALUES(metric_data),
                previous_period_value = VALUES(previous_period_value),
                growth_rate = VALUES(growth_rate),
                calculation_time = VALUES(calculation_time),
                last_calculated_at = NOW(),
                expires_at = VALUES(expires_at)",
            [
                $metricKey,
                'custom',
                $startDate,
                $endDate,
                $metricValue,
                json_encode($data),
                $previousValue,
                $growthRate,
                round($calcTime, 4),
                $expiresAt
            ]
        );
    }

    /**
     * Output summary
     */
    private function outputSummary(float $duration): void
    {
        echo "==================================================\n";
        echo "Cache Warmup Summary\n";
        echo "==================================================\n";
        echo "Execution Time: {$duration}s\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($this->results as $period => $result) {
            echo ucfirst(str_replace('_', ' ', $period)) . ": {$result}\n";
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

    $job = new CacheWarmupJob();
    $job->execute();
}
