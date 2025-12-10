<?php

namespace App\Controllers\Reports;

use App\Services\Reports\ReportService;

class PaymentReportController
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
        
        $paymentBreakdown = $this->reportService->getPaymentMethodBreakdown($startDate, $endDate);
        
        $totalAmount = array_sum(array_column($paymentBreakdown, 'total_amount'));
        $totalTransactions = array_sum(array_column($paymentBreakdown, 'transaction_count'));
        
        require __DIR__ . '/../../Views/reports/payments.php';
    }
    
    public function exportCsv()
    {
        if (!hasPermission('dashboard.export')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/reports/payments');
        }
        
        $period = $_GET['period'] ?? 'all_time';
        $customStart = $_GET['start_date'] ?? null;
        $customEnd = $_GET['end_date'] ?? null;
        
        [$startDate, $endDate] = $this->getDateRange($period, $customStart, $customEnd);
        $paymentBreakdown = $this->reportService->getPaymentMethodBreakdown($startDate, $endDate);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="payment-report-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, ['Payment Method', 'Transaction Count', 'Total Amount', 'Percentage']);
        
        $totalAmount = array_sum(array_column($paymentBreakdown, 'total_amount'));
        
        foreach ($paymentBreakdown as $payment) {
            $percentage = $totalAmount > 0 ? ($payment['total_amount'] / $totalAmount * 100) : 0;
            fputcsv($output, [
                ucwords(str_replace('_', ' ', $payment['payment_method'])),
                $payment['transaction_count'],
                $payment['total_amount'],
                number_format($percentage, 2) . '%'
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
