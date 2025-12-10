<?php

namespace App\Controllers\Reports;

use App\Services\Reports\ReportService;

class CustomerReportController
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
        
        $topCustomers = $this->reportService->getTopCustomers(20, $startDate, $endDate);
        $metrics = $this->reportService->getCustomerMetrics();
        
        require __DIR__ . '/../../Views/reports/customers.php';
    }
    
    public function exportCsv()
    {
        if (!hasPermission('dashboard.export')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/reports/customers');
        }
        
        $period = $_GET['period'] ?? 'all_time';
        $customStart = $_GET['start_date'] ?? null;
        $customEnd = $_GET['end_date'] ?? null;
        
        [$startDate, $endDate] = $this->getDateRange($period, $customStart, $customEnd);
        $topCustomers = $this->reportService->getTopCustomers(100, $startDate, $endDate);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="customer-report-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, ['Customer ID', 'Name', 'Type', 'Total Spent', 'Transactions', 'Avg Order', 'Last Purchase']);
        
        foreach ($topCustomers as $customer) {
            $name = $customer['customer_type'] === 'B2B' 
                ? $customer['company_name'] 
                : $customer['first_name'] . ' ' . $customer['last_name'];
            
            fputcsv($output, [
                $customer['id'],
                $name,
                $customer['customer_type'],
                $customer['total_spent'],
                $customer['transaction_count'],
                $customer['avg_order_value'],
                $customer['last_purchase_date']
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
