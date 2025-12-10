<?php

namespace App\Controllers\Reports;

use App\Services\Reports\ReportService;

class SalesReportController
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
        
        $period = $_GET['period'] ?? 'last_30_days';
        $customStart = $_GET['start_date'] ?? null;
        $customEnd = $_GET['end_date'] ?? null;
        
        [$startDate, $endDate] = $this->getDateRange($period, $customStart, $customEnd);
        
        $metrics = $this->reportService->getSalesMetrics($startDate, $endDate);
        $dailySales = $this->reportService->getSalesByDateRange($startDate, $endDate);
        
        require __DIR__ . '/../../Views/reports/sales.php';
    }
    
    public function exportCsv()
    {
        if (!hasPermission('dashboard.export')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/reports/sales');
        }
        
        $period = $_GET['period'] ?? 'last_30_days';
        $customStart = $_GET['start_date'] ?? null;
        $customEnd = $_GET['end_date'] ?? null;
        
        [$startDate, $endDate] = $this->getDateRange($period, $customStart, $customEnd);
        $dailySales = $this->reportService->getSalesByDateRange($startDate, $endDate);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sales-report-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, ['Date', 'Transactions', 'Subtotal', 'Tax', 'Total', 'Avg Order Value']);
        
        foreach ($dailySales as $row) {
            fputcsv($output, [
                $row['date'],
                $row['transaction_count'],
                $row['subtotal'],
                $row['tax'],
                $row['total'],
                $row['avg_order_value']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    private function getDateRange(string $period, ?string $customStart, ?string $customEnd): array
    {
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
                $startDate = date('Y-m-d', strtotime('-30 days'));
        }
        
        return [$startDate, $endDate];
    }
}
