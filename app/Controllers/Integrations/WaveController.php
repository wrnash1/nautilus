<?php

namespace App\Controllers\Integrations;

use App\Services\Integrations\WaveService;

class WaveController
{
    private WaveService $waveService;

    public function __construct()
    {
        $this->waveService = new WaveService();
    }

    /**
     * Wave integration dashboard
     */
    public function index()
    {
        require __DIR__ . '/../../Views/integrations/wave/index.php';
    }

    /**
     * Test Wave connection
     */
    public function testConnection()
    {
        try {
            $connected = $this->waveService->testConnection();

            if ($connected) {
                $businessInfo = $this->waveService->getBusinessInfo();
                echo json_encode([
                    'success' => true,
                    'message' => 'Successfully connected to Wave!',
                    'business' => $businessInfo
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Connection failed. Check your credentials.'
                ]);
            }

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync single transaction
     */
    public function syncTransaction(int $id)
    {
        try {
            $invoiceId = $this->waveService->syncTransaction($id);

            setFlashMessage('success', 'Transaction synced to Wave! Invoice ID: ' . $invoiceId);
            header('Location: /transactions/' . $id);
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Failed to sync: ' . $e->getMessage());
            header('Location: /transactions/' . $id);
            exit;
        }
    }

    /**
     * Bulk sync transactions
     */
    public function bulkSync()
    {
        try {
            $dateFrom = $_POST['date_from'] ?? date('Y-m-01'); // First of month
            $dateTo = $_POST['date_to'] ?? date('Y-m-d'); // Today

            $results = $this->waveService->bulkSyncTransactions($dateFrom, $dateTo);

            setFlashMessage('success',
                "Synced {$results['success']} transactions. " .
                "Failed: {$results['failed']}"
            );

            if (!empty($results['errors'])) {
                setFlashMessage('warning', 'Errors: ' . implode(', ', array_slice($results['errors'], 0, 3)));
            }

            header('Location: /integrations/wave');
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Bulk sync failed: ' . $e->getMessage());
            header('Location: /integrations/wave');
            exit;
        }
    }

    /**
     * Export to CSV for manual Wave import
     */
    public function exportCSV()
    {
        try {
            $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
            $dateTo = $_GET['date_to'] ?? date('Y-m-d');

            $filename = $this->waveService->exportTransactionsToCSV($dateFrom, $dateTo);

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="wave_transactions_' . date('Y-m-d') . '.csv"');
            readfile($filename);
            unlink($filename);
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Export failed: ' . $e->getMessage());
            header('Location: /integrations/wave');
            exit;
        }
    }
}
