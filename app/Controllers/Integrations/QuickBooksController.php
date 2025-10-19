<?php

namespace App\Controllers\Integrations;

use App\Core\Controller;
use App\Services\Integrations\QuickBooksExportService;
use DateTime;
use Exception;

/**
 * QuickBooks Integration Controller
 *
 * Handles QuickBooks export configuration and file generation
 */
class QuickBooksController extends Controller
{
    private QuickBooksExportService $exportService;

    public function __construct()
    {
        parent::__construct();
        $this->exportService = new QuickBooksExportService();
    }

    /**
     * Configuration page
     * GET /integrations/quickbooks
     */
    public function index(): void
    {
        $this->requirePermission('manage_integrations');

        $config = $this->exportService->getConfiguration();
        $exportHistory = $this->exportService->getExportHistory(20);

        $this->render('integrations/quickbooks/index', [
            'pageTitle' => 'QuickBooks Integration',
            'config' => $config,
            'exportHistory' => $exportHistory
        ]);
    }

    /**
     * Save configuration
     * POST /integrations/quickbooks/config
     */
    public function saveConfig(): void
    {
        $this->requirePermission('manage_integrations');

        try {
            $config = [
                'company_name' => $_POST['company_name'] ?? '',
                'format' => $_POST['format'] ?? 'iif',
                'account_mappings' => [
                    'revenue_account' => $_POST['revenue_account'] ?? 'Sales',
                    'cogs_account' => $_POST['cogs_account'] ?? 'Cost of Goods Sold',
                    'inventory_asset_account' => $_POST['inventory_asset_account'] ?? 'Inventory Asset',
                    'sales_tax_account' => $_POST['sales_tax_account'] ?? 'Sales Tax Payable',
                    'accounts_receivable' => $_POST['accounts_receivable'] ?? 'Accounts Receivable',
                    'deposit_to_account' => $_POST['deposit_to_account'] ?? 'Undeposited Funds'
                ],
                'tax_rate' => floatval($_POST['tax_rate'] ?? 8.0),
                'include_customers' => isset($_POST['include_customers']),
                'include_products' => isset($_POST['include_products']),
                'include_invoices' => isset($_POST['include_invoices'])
            ];

            $success = $this->exportService->saveConfiguration($config);

            if ($success) {
                $_SESSION['success_message'] = 'QuickBooks configuration saved successfully.';
            } else {
                $_SESSION['error_message'] = 'Failed to save configuration.';
            }

        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
        }

        $this->redirect('/integrations/quickbooks');
    }

    /**
     * Export page
     * GET /integrations/quickbooks/export
     */
    public function exportPage(): void
    {
        $this->requirePermission('manage_integrations');

        $config = $this->exportService->getConfiguration();

        // Get date ranges for quick selection
        $today = new DateTime();
        $ranges = [
            'today' => [
                'start' => $today->format('Y-m-d'),
                'end' => $today->format('Y-m-d'),
                'label' => 'Today'
            ],
            'this_week' => [
                'start' => (clone $today)->modify('monday this week')->format('Y-m-d'),
                'end' => $today->format('Y-m-d'),
                'label' => 'This Week'
            ],
            'this_month' => [
                'start' => $today->format('Y-m-01'),
                'end' => $today->format('Y-m-d'),
                'label' => 'This Month'
            ],
            'last_month' => [
                'start' => (clone $today)->modify('first day of last month')->format('Y-m-01'),
                'end' => (clone $today)->modify('last day of last month')->format('Y-m-d'),
                'label' => 'Last Month'
            ],
            'this_year' => [
                'start' => $today->format('Y-01-01'),
                'end' => $today->format('Y-m-d'),
                'label' => 'Year to Date'
            ],
            'all' => [
                'start' => '',
                'end' => '',
                'label' => 'All Time'
            ]
        ];

        $this->render('integrations/quickbooks/export', [
            'pageTitle' => 'Export to QuickBooks',
            'config' => $config,
            'dateRanges' => $ranges
        ]);
    }

    /**
     * Generate and download export file
     * POST /integrations/quickbooks/download
     */
    public function download(): void
    {
        $this->requirePermission('manage_integrations');

        try {
            $format = $_POST['format'] ?? 'iif';
            $startDateStr = $_POST['start_date'] ?? null;
            $endDateStr = $_POST['end_date'] ?? null;

            $startDate = $startDateStr ? new DateTime($startDateStr) : null;
            $endDate = $endDateStr ? new DateTime($endDateStr) : null;

            // Generate export file
            $result = $this->exportService->exportToFile($format, $startDate, $endDate);

            if ($result['success']) {
                // Download file
                $filepath = $result['filepath'];
                $filename = $result['filename'];

                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($filepath));
                header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: no-cache');

                readfile($filepath);

                // Optionally delete file after download
                // unlink($filepath);

                exit;
            } else {
                $_SESSION['error_message'] = 'Export failed: ' . ($result['error'] ?? 'Unknown error');
                $this->redirect('/integrations/quickbooks/export');
            }

        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Export error: ' . $e->getMessage();
            $this->redirect('/integrations/quickbooks/export');
        }
    }

    /**
     * Preview export data (AJAX)
     * POST /integrations/quickbooks/preview
     */
    public function preview(): void
    {
        $this->requirePermission('manage_integrations');

        try {
            $startDateStr = $_POST['start_date'] ?? null;
            $endDateStr = $_POST['end_date'] ?? null;

            $startDate = $startDateStr ? new DateTime($startDateStr) : null;
            $endDate = $endDateStr ? new DateTime($endDateStr) : null;

            $data = [
                'customers' => [],
                'products' => [],
                'invoices' => []
            ];

            $config = $this->exportService->getConfiguration();

            if ($config['include_customers']) {
                $data['customers'] = $this->exportService->exportCustomers($startDate, $endDate);
            }

            if ($config['include_products']) {
                $data['products'] = $this->exportService->exportProducts($startDate, $endDate);
            }

            if ($config['include_invoices']) {
                $data['invoices'] = $this->exportService->exportInvoices($startDate, $endDate);
            }

            // Return summary
            $this->jsonResponse([
                'success' => true,
                'summary' => [
                    'customers' => count($data['customers']),
                    'products' => count($data['products']),
                    'invoices' => count($data['invoices']),
                    'total_revenue' => array_sum(array_column($data['invoices'], 'total'))
                ]
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete export file
     * POST /integrations/quickbooks/delete/{id}
     */
    public function deleteExport(int $id): void
    {
        $this->requirePermission('manage_integrations');

        try {
            // Get export log
            $export = \App\Core\Database::fetchOne(
                "SELECT * FROM export_logs WHERE id = ? AND export_type = 'quickbooks'",
                [$id]
            );

            if (!$export) {
                $_SESSION['error_message'] = 'Export not found.';
                $this->redirect('/integrations/quickbooks');
                return;
            }

            // Delete file
            $filepath = __DIR__ . '/../../../storage/exports/' . $export['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            // Delete log entry
            \App\Core\Database::query(
                "DELETE FROM export_logs WHERE id = ?",
                [$id]
            );

            $_SESSION['success_message'] = 'Export file deleted successfully.';

        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Error deleting export: ' . $e->getMessage();
        }

        $this->redirect('/integrations/quickbooks');
    }
}
