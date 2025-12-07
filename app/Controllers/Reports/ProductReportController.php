<?php

namespace App\Controllers\Reports;

use App\Services\Reports\ReportService;

class ProductReportController
{
    private ReportService $reportService;
    
    public function __construct()
    {
        $this->reportService = new ReportService();
    }
    
    public function index()
    {
        if (!hasPermission('dashboard.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }
        
        $period = $_GET['period'] ?? 'all_time';
        $customStart = $_GET['start_date'] ?? null;
        $customEnd = $_GET['end_date'] ?? null;
        
        [$startDate, $endDate] = $this->getDateRange($period, $customStart, $customEnd);
        
        $bestSellers = $this->reportService->getBestSellingProducts(20, $startDate, $endDate);
        $categoryRevenue = $this->reportService->getRevenueByCategory($startDate, $endDate);
        
        require __DIR__ . '/../../Views/reports/products.php';
    }
    
    public function exportCsv()
    {
        if (!hasPermission('dashboard.export')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/reports/products');
        }
        
        $period = $_GET['period'] ?? 'all_time';
        $customStart = $_GET['start_date'] ?? null;
        $customEnd = $_GET['end_date'] ?? null;
        
        [$startDate, $endDate] = $this->getDateRange($period, $customStart, $customEnd);
        $bestSellers = $this->reportService->getBestSellingProducts(100, $startDate, $endDate);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="product-report-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, ['Product ID', 'SKU', 'Name', 'Category', 'Units Sold', 'Revenue', 'Orders', 'Retail Price']);
        
        foreach ($bestSellers as $product) {
            fputcsv($output, [
                $product['id'],
                $product['sku'],
                $product['name'],
                $product['category_name'] ?? 'Uncategorized',
                $product['units_sold'],
                $product['revenue'],
                $product['order_count'],
                $product['retail_price']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    private function getDateRange(string $period, ?string $customStart, ?string $customEnd): ?array
    {
        if ($period === 'all_time') {
            return [null, null];
        }
        
        $endDate = date('Y-m-d');
        
        switch ($period) {
            case 'today':
                $startDate = date('Y-m-d');
                break;
            case 'this_week':
                $startDate = date('Y-m-d', strtotime('monday this week'));
                break;
            case 'this_month':
                $startDate = date('Y-m-01');
                break;
            case 'last_30_days':
                $startDate = date('Y-m-d', strtotime('-30 days'));
                break;
            case 'custom':
                $startDate = $customStart ?? date('Y-m-d', strtotime('-30 days'));
                $endDate = $customEnd ?? date('Y-m-d');
                break;
            default:
                return [null, null];
        }
        
        return [$startDate, $endDate];
    }
}
