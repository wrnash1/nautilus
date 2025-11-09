<?php

namespace App\Controllers\API;

use App\Services\Analytics\AdvancedDashboardService;
use App\Core\Database;

/**
 * Analytics Dashboard API Controller
 *
 * Provides REST API endpoints for the advanced analytics dashboard
 */
class AnalyticsDashboardController
{
    private AdvancedDashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new AdvancedDashboardService();
    }

    /**
     * GET /api/analytics/overview
     * Get comprehensive dashboard overview
     */
    public function getOverview(): void
    {
        try {
            // Get date range from query params or default to last 30 days
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

            // Validate date format
            if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
                $this->jsonResponse(['error' => 'Invalid date format. Use YYYY-MM-DD'], 400);
                return;
            }

            $overview = $this->dashboardService->getDashboardOverview($startDate, $endDate);

            $this->jsonResponse([
                'success' => true,
                'data' => $overview,
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                    'days' => (strtotime($endDate) - strtotime($startDate)) / 86400
                ]
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to fetch dashboard overview: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/analytics/sales
     * Get sales metrics with comparisons
     */
    public function getSalesMetrics(): void
    {
        try {
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

            if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
                $this->jsonResponse(['error' => 'Invalid date format'], 400);
                return;
            }

            $metrics = $this->dashboardService->getSalesMetrics($startDate, $endDate);

            $this->jsonResponse([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to fetch sales metrics: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/analytics/customers
     * Get customer analytics
     */
    public function getCustomerMetrics(): void
    {
        try {
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

            if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
                $this->jsonResponse(['error' => 'Invalid date format'], 400);
                return;
            }

            $metrics = $this->dashboardService->getCustomerMetrics($startDate, $endDate);

            $this->jsonResponse([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to fetch customer metrics: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/analytics/products
     * Get product performance metrics
     */
    public function getProductMetrics(): void
    {
        try {
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

            if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
                $this->jsonResponse(['error' => 'Invalid date format'], 400);
                return;
            }

            $metrics = $this->dashboardService->getProductMetrics($startDate, $endDate);

            $this->jsonResponse([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to fetch product metrics: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/analytics/inventory
     * Get inventory health metrics
     */
    public function getInventoryMetrics(): void
    {
        try {
            $metrics = $this->dashboardService->getInventoryMetrics();

            $this->jsonResponse([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to fetch inventory metrics: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/analytics/courses
     * Get course and training metrics
     */
    public function getCourseMetrics(): void
    {
        try {
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

            if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
                $this->jsonResponse(['error' => 'Invalid date format'], 400);
                return;
            }

            $metrics = $this->dashboardService->getCourseMetrics($startDate, $endDate);

            $this->jsonResponse([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to fetch course metrics: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/analytics/rentals
     * Get rental metrics
     */
    public function getRentalMetrics(): void
    {
        try {
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

            if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
                $this->jsonResponse(['error' => 'Invalid date format'], 400);
                return;
            }

            $metrics = $this->dashboardService->getRentalMetrics($startDate, $endDate);

            $this->jsonResponse([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to fetch rental metrics: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/analytics/trends
     * Get trend analysis
     */
    public function getTrendAnalysis(): void
    {
        try {
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

            if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
                $this->jsonResponse(['error' => 'Invalid date format'], 400);
                return;
            }

            $trends = $this->dashboardService->getTrendAnalysis($startDate, $endDate);

            $this->jsonResponse([
                'success' => true,
                'data' => $trends
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to fetch trend analysis: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/analytics/kpis
     * Get key performance indicators
     */
    public function getPerformanceIndicators(): void
    {
        try {
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

            if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
                $this->jsonResponse(['error' => 'Invalid date format'], 400);
                return;
            }

            $kpis = $this->dashboardService->getPerformanceIndicators($startDate, $endDate);

            $this->jsonResponse([
                'success' => true,
                'data' => $kpis
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to fetch KPIs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/analytics/cached-metrics
     * Get cached metrics for faster loading
     */
    public function getCachedMetrics(): void
    {
        try {
            $metricKey = $_GET['metric_key'] ?? null;
            $periodType = $_GET['period_type'] ?? 'daily';

            if (!$metricKey) {
                $this->jsonResponse(['error' => 'metric_key parameter is required'], 400);
                return;
            }

            $cached = Database::fetchOne(
                "SELECT * FROM dashboard_metrics_cache
                 WHERE metric_key = ?
                 AND period_type = ?
                 AND (expires_at IS NULL OR expires_at > NOW())
                 ORDER BY period_end DESC
                 LIMIT 1",
                [$metricKey, $periodType]
            );

            if ($cached) {
                $this->jsonResponse([
                    'success' => true,
                    'data' => [
                        'metric_value' => $cached['metric_value'],
                        'metric_data' => json_decode($cached['metric_data'], true),
                        'previous_period_value' => $cached['previous_period_value'],
                        'growth_rate' => $cached['growth_rate'],
                        'period_start' => $cached['period_start'],
                        'period_end' => $cached['period_end'],
                        'cached_at' => $cached['last_calculated_at']
                    ]
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No cached data available'
                ], 404);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to fetch cached metrics: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/analytics/refresh-cache
     * Refresh cached metrics
     */
    public function refreshCache(): void
    {
        try {
            // This would trigger a background job to recalculate metrics
            // For now, we'll just acknowledge the request

            $this->jsonResponse([
                'success' => true,
                'message' => 'Cache refresh initiated'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to refresh cache: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Validate date format
     */
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}
