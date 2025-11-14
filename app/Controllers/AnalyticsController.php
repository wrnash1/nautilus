<?php

namespace App\Controllers;

use App\Services\Analytics\AnalyticsService;
use App\Helpers\Auth;

/**
 * Analytics Controller
 *
 * Advanced reporting and business intelligence
 */
class AnalyticsController
{
    private AnalyticsService $analyticsService;

    public function __construct()
    {
        $this->analyticsService = new AnalyticsService();
    }

    /**
     * Show analytics dashboard
     */
    public function index()
    {
        if (!Auth::check() || !Auth::hasPermission('analytics.view')) {
            redirect('/login');
            return;
        }

        // Default to last 30 days
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

        $metrics = $this->analyticsService->getDashboardMetrics($startDate, $endDate);
        $revenueBreakdown = $this->analyticsService->getRevenueBreakdown($startDate, $endDate);
        $hourlySales = $this->analyticsService->getHourlySalesPattern($startDate, $endDate);
        $dayOfWeek = $this->analyticsService->getDayOfWeekPerformance($startDate, $endDate);

        require __DIR__ . '/../Views/analytics/index.php';
    }

    /**
     * Sales analytics report
     */
    public function sales()
    {
        if (!Auth::check() || !Auth::hasPermission('analytics.view')) {
            redirect('/login');
            return;
        }

        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

        $salesMetrics = $this->analyticsService->getSalesMetrics($startDate, $endDate);

        require __DIR__ . '/../Views/analytics/sales.php';
    }

    /**
     * Customer analytics report
     */
    public function customers()
    {
        if (!Auth::check() || !Auth::hasPermission('analytics.view')) {
            redirect('/login');
            return;
        }

        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

        $customerMetrics = $this->analyticsService->getCustomerMetrics($startDate, $endDate);

        require __DIR__ . '/../Views/analytics/customers.php';
    }

    /**
     * Product analytics report
     */
    public function products()
    {
        if (!Auth::check() || !Auth::hasPermission('analytics.view')) {
            redirect('/login');
            return;
        }

        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

        $productMetrics = $this->analyticsService->getProductMetrics($startDate, $endDate);

        require __DIR__ . '/../Views/analytics/products.php';
    }

    /**
     * Export analytics data
     */
    public function export()
    {
        if (!Auth::check() || !Auth::hasPermission('analytics.export')) {
            http_response_code(403);
            echo "Unauthorized";
            exit;
        }

        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $type = $_GET['type'] ?? 'dashboard';

        $data = [];

        switch ($type) {
            case 'sales':
                $data = $this->analyticsService->getSalesMetrics($startDate, $endDate);
                break;
            case 'customers':
                $data = $this->analyticsService->getCustomerMetrics($startDate, $endDate);
                break;
            case 'products':
                $data = $this->analyticsService->getProductMetrics($startDate, $endDate);
                break;
            default:
                $data = $this->analyticsService->getDashboardMetrics($startDate, $endDate);
        }

        // Export as CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="analytics_' . $type . '_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Flatten array for CSV
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    if (!is_array($subValue)) {
                        fputcsv($output, [$key . '_' . $subKey, $subValue]);
                    }
                }
            } else {
                fputcsv($output, [$key, $value]);
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Get metrics via AJAX
     */
    public function getMetrics()
    {
        if (!Auth::check() || !Auth::hasPermission('analytics.view')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

        $metrics = $this->analyticsService->getDashboardMetrics($startDate, $endDate);

        return $this->jsonResponse(['success' => true, 'metrics' => $metrics]);
    }

    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
