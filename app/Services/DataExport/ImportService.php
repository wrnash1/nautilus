<?php

namespace App\Services\DataExport;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;

/**
 * Data Import Service
 *
 * Import data from CSV, JSON, or Excel files
 */
class ImportService
{
    private Logger $logger;
    private array $errors = [];
    private array $warnings = [];

    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Import products from file
     */
    public function importProducts(string $filepath, string $format = 'csv'): array
    {
        try {
            $this->errors = [];
            $this->warnings = [];

            // Read data from file
            $data = $this->readFile($filepath, $format);

            $imported = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($data as $index => $row) {
                try {
                    // Validate required fields
                    if (empty($row['sku']) || empty($row['name'])) {
                        $this->warnings[] = "Row " . ($index + 1) . ": SKU and name are required";
                        $skipped++;
                        continue;
                    }

                    // Check if product exists
                    $existing = TenantDatabase::fetchOneTenant(
                        "SELECT id FROM products WHERE sku = ?",
                        [$row['sku']]
                    );

                    if ($existing) {
                        // Update existing product
                        TenantDatabase::updateTenant('products', [
                            'name' => $row['name'],
                            'description' => $row['description'] ?? null,
                            'price' => $row['price'] ?? 0,
                            'cost' => $row['cost'] ?? 0,
                            'stock_quantity' => $row['stock_quantity'] ?? 0,
                            'barcode' => $row['barcode'] ?? null,
                            'updated_at' => date('Y-m-d H:i:s')
                        ], 'id = ?', [$existing['id']]);

                        $updated++;
                    } else {
                        // Insert new product
                        TenantDatabase::insertTenant('products', [
                            'sku' => $row['sku'],
                            'name' => $row['name'],
                            'description' => $row['description'] ?? null,
                            'price' => $row['price'] ?? 0,
                            'cost' => $row['cost'] ?? 0,
                            'stock_quantity' => $row['stock_quantity'] ?? 0,
                            'low_stock_threshold' => $row['low_stock_threshold'] ?? 5,
                            'barcode' => $row['barcode'] ?? null,
                            'is_active' => 1,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $imported++;
                    }

                } catch (\Exception $e) {
                    $this->errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                    $skipped++;
                }
            }

            return [
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'total' => count($data),
                'errors' => $this->errors,
                'warnings' => $this->warnings
            ];

        } catch (\Exception $e) {
            $this->logger->error('Product import failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Import customers from file
     */
    public function importCustomers(string $filepath, string $format = 'csv'): array
    {
        try {
            $this->errors = [];
            $this->warnings = [];

            $data = $this->readFile($filepath, $format);

            $imported = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($data as $index => $row) {
                try {
                    // Validate required fields
                    if (empty($row['email'])) {
                        $this->warnings[] = "Row " . ($index + 1) . ": Email is required";
                        $skipped++;
                        continue;
                    }

                    // Validate email format
                    if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                        $this->warnings[] = "Row " . ($index + 1) . ": Invalid email format";
                        $skipped++;
                        continue;
                    }

                    // Check if customer exists
                    $existing = TenantDatabase::fetchOneTenant(
                        "SELECT id FROM customers WHERE email = ?",
                        [$row['email']]
                    );

                    $customerData = [
                        'first_name' => $row['first_name'] ?? '',
                        'last_name' => $row['last_name'] ?? '',
                        'email' => $row['email'],
                        'phone' => $row['phone'] ?? null,
                        'address_line1' => $row['address_line1'] ?? null,
                        'address_line2' => $row['address_line2'] ?? null,
                        'city' => $row['city'] ?? null,
                        'state' => $row['state'] ?? null,
                        'postal_code' => $row['postal_code'] ?? null,
                        'country' => $row['country'] ?? null,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if ($existing) {
                        TenantDatabase::updateTenant('customers', $customerData, 'id = ?', [$existing['id']]);
                        $updated++;
                    } else {
                        $customerData['is_active'] = 1;
                        $customerData['created_at'] = date('Y-m-d H:i:s');
                        TenantDatabase::insertTenant('customers', $customerData);
                        $imported++;
                    }

                } catch (\Exception $e) {
                    $this->errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                    $skipped++;
                }
            }

            return [
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'total' => count($data),
                'errors' => $this->errors,
                'warnings' => $this->warnings
            ];

        } catch (\Exception $e) {
            $this->logger->error('Customer import failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Read data from file based on format
     */
    private function readFile(string $filepath, string $format): array
    {
        if (!file_exists($filepath)) {
            throw new \Exception('File not found');
        }

        switch ($format) {
            case 'csv':
                return $this->readCsvFile($filepath);

            case 'json':
                return $this->readJsonFile($filepath);

            case 'excel':
                return $this->readExcelFile($filepath);

            default:
                throw new \Exception('Unsupported file format');
        }
    }

    /**
     * Read CSV file
     */
    private function readCsvFile(string $filepath): array
    {
        $data = [];
        $handle = fopen($filepath, 'r');

        if ($handle === false) {
            throw new \Exception('Cannot open CSV file');
        }

        // Read header row
        $headers = fgetcsv($handle);

        if ($headers === false) {
            fclose($handle);
            throw new \Exception('CSV file is empty');
        }

        // Normalize headers (lowercase, replace spaces with underscores)
        $headers = array_map(function($header) {
            return strtolower(str_replace(' ', '_', trim($header)));
        }, $headers);

        // Read data rows
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
        return $data;
    }

    /**
     * Read JSON file
     */
    private function readJsonFile(string $filepath): array
    {
        $content = file_get_contents($filepath);
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON file: ' . json_last_error_msg());
        }

        // Handle both direct arrays and wrapped data
        if (isset($json['data'])) {
            return $json['data'];
        }

        return $json;
    }

    /**
     * Read Excel file (simplified - reads as CSV)
     */
    private function readExcelFile(string $filepath): array
    {
        // For now, treat Excel files as CSV
        // In production, you'd use PHPSpreadsheet library
        return $this->readCsvFile($filepath);
    }

    /**
     * Validate import file
     */
    public function validateImportFile(string $filepath, string $type, string $format): array
    {
        try {
            $data = $this->readFile($filepath, $format);

            $requiredFields = $this->getRequiredFields($type);
            $issues = [];

            if (empty($data)) {
                return [
                    'valid' => false,
                    'error' => 'File is empty or invalid format'
                ];
            }

            // Check first row for required fields
            $firstRow = $data[0];
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (!isset($firstRow[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                $issues[] = 'Missing required fields: ' . implode(', ', $missingFields);
            }

            // Sample validation (check first 10 rows)
            $sampleSize = min(10, count($data));
            for ($i = 0; $i < $sampleSize; $i++) {
                $validationResult = $this->validateRow($data[$i], $type);
                if (!$validationResult['valid']) {
                    $issues[] = "Row " . ($i + 1) . ": " . $validationResult['error'];
                }
            }

            return [
                'valid' => empty($issues),
                'record_count' => count($data),
                'sample_size' => $sampleSize,
                'issues' => $issues,
                'preview' => array_slice($data, 0, 5) // Preview first 5 rows
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get required fields for import type
     */
    private function getRequiredFields(string $type): array
    {
        $fields = [
            'products' => ['sku', 'name'],
            'customers' => ['email'],
            'transactions' => ['transaction_number', 'total_amount']
        ];

        return $fields[$type] ?? [];
    }

    /**
     * Validate individual row
     */
    private function validateRow(array $row, string $type): array
    {
        switch ($type) {
            case 'products':
                if (empty($row['sku'])) {
                    return ['valid' => false, 'error' => 'SKU is required'];
                }
                if (empty($row['name'])) {
                    return ['valid' => false, 'error' => 'Name is required'];
                }
                if (isset($row['price']) && !is_numeric($row['price'])) {
                    return ['valid' => false, 'error' => 'Price must be numeric'];
                }
                break;

            case 'customers':
                if (empty($row['email'])) {
                    return ['valid' => false, 'error' => 'Email is required'];
                }
                if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                    return ['valid' => false, 'error' => 'Invalid email format'];
                }
                break;
        }

        return ['valid' => true];
    }

    /**
     * Get import template for type
     */
    public function getTemplate(string $type, string $format = 'csv'): string
    {
        $templates = [
            'products' => [
                'headers' => ['sku', 'name', 'description', 'price', 'cost', 'stock_quantity', 'barcode'],
                'sample' => ['PROD-001', 'Sample Product', 'Product description', '29.99', '15.00', '10', '123456789']
            ],
            'customers' => [
                'headers' => ['first_name', 'last_name', 'email', 'phone', 'city', 'state', 'country'],
                'sample' => ['John', 'Doe', 'john@example.com', '+1-555-0100', 'Miami', 'FL', 'USA']
            ]
        ];

        if (!isset($templates[$type])) {
            throw new \Exception('Unknown template type');
        }

        $template = $templates[$type];

        switch ($format) {
            case 'csv':
                return $this->generateCsvTemplate($template);

            case 'json':
                return $this->generateJsonTemplate($template);

            default:
                throw new \Exception('Unsupported template format');
        }
    }

    /**
     * Generate CSV template
     */
    private function generateCsvTemplate(array $template): string
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $template['headers']);
        fputcsv($output, $template['sample']);
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Generate JSON template
     */
    private function generateJsonTemplate(array $template): string
    {
        $data = [array_combine($template['headers'], $template['sample'])];

        return json_encode([
            'data' => $data,
            'description' => 'Import template - add your records to the data array'
        ], JSON_PRETTY_PRINT);
    }
}
