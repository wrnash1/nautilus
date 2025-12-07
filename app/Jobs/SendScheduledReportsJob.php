<?php

namespace App\Jobs;

use App\Core\Database;
use App\Core\Logger;
use App\Services\Analytics\AdvancedDashboardService;
use App\Services\Email\EmailService;

/**
 * Send Scheduled Reports Job
 *
 * Sends scheduled reports to configured recipients
 * Run every Monday at 9 AM
 *
 * Cron: 0 9 * * 1 php /path/to/nautilus/app/Jobs/SendScheduledReportsJob.php
 */
class SendScheduledReportsJob
{
    private Logger $logger;
    private EmailService $emailService;
    private AdvancedDashboardService $dashboardService;
    private array $results = [];

    public function __construct()
    {
        $this->logger = new Logger();
        $this->emailService = new EmailService();
        $this->dashboardService = new AdvancedDashboardService();
    }

    public function execute(): void
    {
        $this->logger->info('Starting scheduled reports job');
        $startTime = microtime(true);

        try {
            // Get active report schedules that are due
            $schedules = $this->getDueReports();

            echo "Found " . count($schedules) . " reports to send\n\n";

            foreach ($schedules as $schedule) {
                $this->processReport($schedule);
            }

            $duration = round(microtime(true) - $startTime, 2);
            $this->logger->info('Scheduled reports job completed', [
                'duration' => $duration,
                'results' => $this->results
            ]);

            $this->outputSummary($duration);

        } catch (\Exception $e) {
            $this->logger->error('Scheduled reports job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            echo "ERROR: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Get reports that are due to be sent
     */
    private function getDueReports(): array
    {
        $now = date('Y-m-d H:i:s');
        $today = date('w'); // Day of week (0=Sunday, 1=Monday, etc.)

        return Database::fetchAll(
            "SELECT *
             FROM report_schedules
             WHERE is_active = 1
             AND (next_run_at IS NULL OR next_run_at <= ?)
             ORDER BY schedule_type, id",
            [$now]
        ) ?? [];
    }

    /**
     * Process a single report
     */
    private function processReport(array $schedule): void
    {
        try {
            echo "Processing report: {$schedule['report_name']}\n";

            // Determine date range based on schedule type
            $dateRange = $this->calculateDateRange($schedule['schedule_type']);

            // Generate report data
            $reportData = $this->generateReportData($schedule['report_type'], $dateRange, $schedule['parameters']);

            // Format report based on output format
            $reportContent = $this->formatReport($reportData, $schedule['output_format'], $schedule['report_name']);

            // Send to all recipients
            $recipients = json_decode($schedule['recipients'], true);
            $sent = 0;

            foreach ($recipients as $recipient) {
                if ($this->sendReport($recipient, $schedule, $reportContent, $dateRange)) {
                    $sent++;
                }
            }

            // Update next run time
            $this->updateNextRunTime($schedule);

            $this->results[$schedule['report_name']] = "{$sent} sent";
            echo "  Sent to {$sent} recipients\n\n";

        } catch (\Exception $e) {
            $this->logger->error("Failed to process report: {$schedule['report_name']}", [
                'error' => $e->getMessage()
            ]);
            $this->results[$schedule['report_name']] = 'failed';
            echo "  ERROR: {$e->getMessage()}\n\n";
        }
    }

    /**
     * Calculate date range based on schedule type
     */
    private function calculateDateRange(string $scheduleType): array
    {
        switch ($scheduleType) {
            case 'daily':
                return [
                    'start' => date('Y-m-d', strtotime('yesterday')),
                    'end' => date('Y-m-d', strtotime('yesterday'))
                ];

            case 'weekly':
                return [
                    'start' => date('Y-m-d', strtotime('last Monday', strtotime('yesterday'))),
                    'end' => date('Y-m-d', strtotime('last Sunday', strtotime('yesterday')))
                ];

            case 'monthly':
                return [
                    'start' => date('Y-m-01', strtotime('last month')),
                    'end' => date('Y-m-t', strtotime('last month'))
                ];

            case 'quarterly':
                $currentQuarter = ceil(date('n') / 3);
                $lastQuarter = $currentQuarter - 1;
                if ($lastQuarter < 1) {
                    $lastQuarter = 4;
                    $year = date('Y') - 1;
                } else {
                    $year = date('Y');
                }
                $startMonth = ($lastQuarter - 1) * 3 + 1;

                return [
                    'start' => date('Y-m-01', strtotime("{$year}-{$startMonth}-01")),
                    'end' => date('Y-m-t', strtotime("{$year}-" . ($startMonth + 2) . "-01"))
                ];

            default:
                return [
                    'start' => date('Y-m-d', strtotime('-7 days')),
                    'end' => date('Y-m-d', strtotime('yesterday'))
                ];
        }
    }

    /**
     * Generate report data
     */
    private function generateReportData(string $reportType, array $dateRange, ?string $parameters): array
    {
        $params = $parameters ? json_decode($parameters, true) : [];

        switch ($reportType) {
            case 'sales_summary':
                return [
                    'sales_metrics' => $this->dashboardService->getSalesMetrics($dateRange['start'], $dateRange['end']),
                    'trends' => $this->dashboardService->getTrendAnalysis($dateRange['start'], $dateRange['end'])
                ];

            case 'customer_analytics':
                return $this->dashboardService->getCustomerMetrics($dateRange['start'], $dateRange['end']);

            case 'inventory_report':
                return $this->dashboardService->getInventoryMetrics();

            case 'full_dashboard':
                return $this->dashboardService->getDashboardOverview($dateRange['start'], $dateRange['end']);

            default:
                return ['error' => 'Unknown report type'];
        }
    }

    /**
     * Format report for sending
     */
    private function formatReport(array $data, string $format, string $reportName): string
    {
        switch ($format) {
            case 'html':
                return $this->formatHtmlReport($data, $reportName);

            case 'csv':
                return $this->formatCsvReport($data);

            case 'pdf':
                // Would use TCPDF here
                return $this->formatHtmlReport($data, $reportName);

            default:
                return $this->formatHtmlReport($data, $reportName);
        }
    }

    /**
     * Format HTML report
     */
    private function formatHtmlReport(array $data, string $reportName): string
    {
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                h1 { color: #0066cc; }
                h2 { color: #333; border-bottom: 2px solid #0066cc; padding-bottom: 5px; }
                table { border-collapse: collapse; width: 100%; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                th { background-color: #0066cc; color: white; }
                .metric { background-color: #f0f8ff; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .metric-value { font-size: 24px; font-weight: bold; color: #0066cc; }
                .growth-positive { color: #28a745; }
                .growth-negative { color: #dc3545; }
            </style>
        </head>
        <body>
            <h1>' . htmlspecialchars($reportName) . '</h1>
            <p>Generated: ' . date('F j, Y g:i A') . '</p>';

        // Add sales metrics if available
        if (isset($data['sales_metrics'])) {
            $sales = $data['sales_metrics'];
            $html .= '
            <h2>Sales Performance</h2>
            <div class="metric">
                <div>Total Revenue</div>
                <div class="metric-value">$' . number_format($sales['current']['total_revenue'] ?? 0, 2) . '</div>
                <div class="' . (($sales['revenue_growth'] ?? 0) >= 0 ? 'growth-positive' : 'growth-negative') . '">
                    ' . ($sales['revenue_growth'] ?? 0) . '% vs previous period
                </div>
            </div>
            <div class="metric">
                <div>Total Transactions</div>
                <div class="metric-value">' . number_format($sales['current']['total_transactions'] ?? 0) . '</div>
            </div>
            <div class="metric">
                <div>Average Order Value</div>
                <div class="metric-value">$' . number_format($sales['current']['avg_order_value'] ?? 0, 2) . '</div>
            </div>';
        }

        // Add customer metrics if available
        if (isset($data['new_customers'])) {
            $html .= '
            <h2>Customer Metrics</h2>
            <div class="metric">
                <div>New Customers</div>
                <div class="metric-value">' . ($data['new_customers'] ?? 0) . '</div>
            </div>
            <div class="metric">
                <div>Customer Lifetime Value</div>
                <div class="metric-value">$' . number_format($data['avg_customer_lifetime_value'] ?? 0, 2) . '</div>
            </div>';
        }

        $html .= '
        </body>
        </html>';

        return $html;
    }

    /**
     * Format CSV report
     */
    private function formatCsvReport(array $data): string
    {
        // Simple CSV formatting
        $csv = "Metric,Value\n";

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    if (!is_array($subValue)) {
                        $csv .= "\"{$key} - {$subKey}\",\"{$subValue}\"\n";
                    }
                }
            } else {
                $csv .= "\"{$key}\",\"{$value}\"\n";
            }
        }

        return $csv;
    }

    /**
     * Send report to recipient
     */
    private function sendReport(string $recipient, array $schedule, string $content, array $dateRange): bool
    {
        $subject = $schedule['report_name'] . ' - ' . date('F j, Y', strtotime($dateRange['start'])) . ' to ' . date('F j, Y', strtotime($dateRange['end']));

        return $this->emailService->send(
            $recipient,
            $subject,
            $content,
            ['is_html' => true]
        );
    }

    /**
     * Update next run time for a schedule
     */
    private function updateNextRunTime(array $schedule): void
    {
        $nextRun = null;

        switch ($schedule['schedule_type']) {
            case 'daily':
                $nextRun = date('Y-m-d', strtotime('+1 day')) . ' ' . $schedule['schedule_time'];
                break;

            case 'weekly':
                $nextRun = date('Y-m-d', strtotime('next ' . $this->getDayName($schedule['schedule_day']))) . ' ' . $schedule['schedule_time'];
                break;

            case 'monthly':
                $nextRun = date('Y-m-' . str_pad($schedule['schedule_day'], 2, '0', STR_PAD_LEFT), strtotime('next month')) . ' ' . $schedule['schedule_time'];
                break;

            case 'quarterly':
                $nextRun = date('Y-m-d', strtotime('+3 months')) . ' ' . $schedule['schedule_time'];
                break;
        }

        if ($nextRun) {
            Database::query(
                "UPDATE report_schedules
                 SET last_run_at = NOW(),
                     next_run_at = ?
                 WHERE id = ?",
                [$nextRun, $schedule['id']]
            );
        }
    }

    /**
     * Get day name from number
     */
    private function getDayName(int $dayNumber): string
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $days[$dayNumber] ?? 'Monday';
    }

    /**
     * Output summary
     */
    private function outputSummary(float $duration): void
    {
        echo "==================================================\n";
        echo "Scheduled Reports Summary\n";
        echo "==================================================\n";
        echo "Execution Time: {$duration}s\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($this->results as $report => $result) {
            echo "{$report}: {$result}\n";
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

    $job = new SendScheduledReportsJob();
    $job->execute();
}
