<?php

namespace App\Services\DataExport;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;

/**
 * Data Export Service
 *
 * Export tenant data in various formats (CSV, JSON, Excel)
 */
class ExportService
{
    private Logger $logger;
    private string $exportPath;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->exportPath = __DIR__ . '/../../../storage/exports';

        // Create exports directory if it doesn't exist
        if (!is_dir($this->exportPath)) {
            mkdir($this->exportPath, 0755, true);
        }
    }

    /**
     * Export products to CSV
     */
    public function exportProducts(string $format = 'csv'): array
    {
        try {
            $products = TenantDatabase::fetchAllTenant(
                "SELECT p.*, pc.name as category_name
                 FROM products p
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 ORDER BY p.name"
            );

            $filename = $this->generateFilename('products', $format);

            switch ($format) {
                case 'csv':
                    $filepath = $this->exportToCsv($products, $filename, [
                        'id', 'sku', 'name', 'description', 'category_name',
                        'price', 'cost', 'stock_quantity', 'barcode', 'is_active'
                    ]);
                    break;

                case 'json':
                    $filepath = $this->exportToJson($products, $filename);
                    break;

                case 'excel':
                    $filepath = $this->exportToExcel($products, $filename, [
                        'id' => 'ID',
                        'sku' => 'SKU',
                        'name' => 'Name',
                        'description' => 'Description',
                        'category_name' => 'Category',
                        'price' => 'Price',
                        'cost' => 'Cost',
                        'stock_quantity' => 'Stock',
                        'barcode' => 'Barcode',
                        'is_active' => 'Active'
                    ]);
                    break;

                default:
                    throw new \Exception('Unsupported export format');
            }

            return [
                'success' => true,
                'filename' => basename($filepath),
                'filepath' => $filepath,
                'format' => $format,
                'records' => count($products)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Product export failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Export customers to CSV
     */
    public function exportCustomers(string $format = 'csv'): array
    {
        try {
            $customers = TenantDatabase::fetchAllTenant(
                "SELECT id, first_name, last_name, email, phone,
                        address_line1, address_line2, city, state, postal_code, country,
                        date_of_birth, emergency_contact_name, emergency_contact_phone,
                        certification_level, is_active, created_at
                 FROM customers
                 ORDER BY last_name, first_name"
            );

            $filename = $this->generateFilename('customers', $format);

            switch ($format) {
                case 'csv':
                    $filepath = $this->exportToCsv($customers, $filename);
                    break;

                case 'json':
                    $filepath = $this->exportToJson($customers, $filename);
                    break;

                case 'excel':
                    $filepath = $this->exportToExcel($customers, $filename, [
                        'id' => 'ID',
                        'first_name' => 'First Name',
                        'last_name' => 'Last Name',
                        'email' => 'Email',
                        'phone' => 'Phone',
                        'city' => 'City',
                        'state' => 'State',
                        'country' => 'Country'
                    ]);
                    break;

                default:
                    throw new \Exception('Unsupported export format');
            }

            return [
                'success' => true,
                'filename' => basename($filepath),
                'filepath' => $filepath,
                'format' => $format,
                'records' => count($customers)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Customer export failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Export transactions to CSV
     */
    public function exportTransactions(string $startDate, string $endDate, string $format = 'csv'): array
    {
        try {
            $transactions = TenantDatabase::fetchAllTenant(
                "SELECT t.id, t.transaction_number, t.transaction_date,
                        CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                        t.subtotal, t.tax_amount, t.discount_amount, t.total_amount,
                        t.payment_method, t.status, t.created_at
                 FROM pos_transactions t
                 LEFT JOIN customers c ON t.customer_id = c.id
                 WHERE t.transaction_date BETWEEN ? AND ?
                 ORDER BY t.transaction_date DESC",
                [$startDate, $endDate]
            );

            $filename = $this->generateFilename('transactions', $format);

            switch ($format) {
                case 'csv':
                    $filepath = $this->exportToCsv($transactions, $filename);
                    break;

                case 'json':
                    $filepath = $this->exportToJson($transactions, $filename);
                    break;

                case 'excel':
                    $filepath = $this->exportToExcel($transactions, $filename, [
                        'transaction_number' => 'Transaction #',
                        'transaction_date' => 'Date',
                        'customer_name' => 'Customer',
                        'subtotal' => 'Subtotal',
                        'tax_amount' => 'Tax',
                        'total_amount' => 'Total',
                        'payment_method' => 'Payment Method',
                        'status' => 'Status'
                    ]);
                    break;

                default:
                    throw new \Exception('Unsupported export format');
            }

            return [
                'success' => true,
                'filename' => basename($filepath),
                'filepath' => $filepath,
                'format' => $format,
                'records' => count($transactions),
                'date_range' => "$startDate to $endDate"
            ];

        } catch (\Exception $e) {
            $this->logger->error('Transaction export failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Export complete tenant data backup
     */
    public function exportFullBackup(): array
    {
        try {
            $tenantId = TenantMiddleware::getCurrentTenantId();
            $timestamp = date('Y-m-d_H-i-s');
            $backupDir = $this->exportPath . '/backup_' . $timestamp;

            mkdir($backupDir, 0755, true);

            $exports = [
                'products' => $this->exportProducts('json'),
                'customers' => $this->exportCustomers('json'),
                'transactions' => $this->exportTransactions(
                    date('Y-01-01'),
                    date('Y-m-d'),
                    'json'
                )
            ];

            // Create backup manifest
            $manifest = [
                'tenant_id' => $tenantId,
                'backup_date' => date('Y-m-d H:i:s'),
                'version' => '1.0',
                'exports' => $exports
            ];

            file_put_contents(
                $backupDir . '/manifest.json',
                json_encode($manifest, JSON_PRETTY_PRINT)
            );

            // Create ZIP archive
            $zipFile = $this->exportPath . '/backup_' . $timestamp . '.zip';
            $this->createZipArchive($backupDir, $zipFile);

            // Clean up temporary directory
            $this->deleteDirectory($backupDir);

            return [
                'success' => true,
                'filename' => basename($zipFile),
                'filepath' => $zipFile,
                'exports' => array_keys($exports)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Full backup failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Export data to CSV format
     */
    private function exportToCsv(array $data, string $filename, ?array $columns = null): string
    {
        $filepath = $this->exportPath . '/' . $filename;
        $handle = fopen($filepath, 'w');

        if (empty($data)) {
            fclose($handle);
            return $filepath;
        }

        // Determine columns
        if ($columns === null) {
            $columns = array_keys($data[0]);
        }

        // Write header
        fputcsv($handle, $columns);

        // Write data
        foreach ($data as $row) {
            $outputRow = [];
            foreach ($columns as $column) {
                $outputRow[] = $row[$column] ?? '';
            }
            fputcsv($handle, $outputRow);
        }

        fclose($handle);
        return $filepath;
    }

    /**
     * Export data to JSON format
     */
    private function exportToJson(array $data, string $filename): string
    {
        $filepath = $this->exportPath . '/' . $filename;

        file_put_contents(
            $filepath,
            json_encode([
                'exported_at' => date('Y-m-d H:i:s'),
                'record_count' => count($data),
                'data' => $data
            ], JSON_PRETTY_PRINT)
        );

        return $filepath;
    }

    /**
     * Export data to Excel format (simplified CSV with headers)
     */
    private function exportToExcel(array $data, string $filename, array $columnMap): string
    {
        $filepath = $this->exportPath . '/' . str_replace('.xlsx', '.csv', $filename);
        $handle = fopen($filepath, 'w');

        if (empty($data)) {
            fclose($handle);
            return $filepath;
        }

        // Write header with custom labels
        fputcsv($handle, array_values($columnMap));

        // Write data
        foreach ($data as $row) {
            $outputRow = [];
            foreach (array_keys($columnMap) as $column) {
                $outputRow[] = $row[$column] ?? '';
            }
            fputcsv($handle, $outputRow);
        }

        fclose($handle);
        return $filepath;
    }

    /**
     * Create ZIP archive from directory
     */
    private function createZipArchive(string $sourceDir, string $zipFile): void
    {
        if (!class_exists('ZipArchive')) {
            throw new \Exception('ZIP extension not available');
        }

        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Cannot create ZIP file');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($sourceDir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
    }

    /**
     * Delete directory recursively
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }

    /**
     * Generate export filename
     */
    private function generateFilename(string $type, string $format): string
    {
        $tenantId = TenantMiddleware::getCurrentTenantId();
        $timestamp = date('Y-m-d_H-i-s');
        $extension = $format === 'excel' ? 'xlsx' : $format;

        return "{$type}_export_tenant{$tenantId}_{$timestamp}.{$extension}";
    }

    /**
     * Get export file for download
     */
    public function downloadExport(string $filename): void
    {
        $filepath = $this->exportPath . '/' . basename($filename);

        if (!file_exists($filepath)) {
            http_response_code(404);
            echo json_encode(['error' => 'File not found']);
            return;
        }

        // Determine content type
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $contentTypes = [
            'csv' => 'text/csv',
            'json' => 'application/json',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip'
        ];

        $contentType = $contentTypes[$extension] ?? 'application/octet-stream';

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filepath));

        readfile($filepath);
        exit;
    }

    /**
     * Clean up old export files
     */
    public function cleanupOldExports(int $daysOld = 7): int
    {
        $deleted = 0;
        $cutoffTime = time() - ($daysOld * 86400);

        $files = glob($this->exportPath . '/*');

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }
}
