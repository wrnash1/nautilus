<?php

namespace App\Services\Import;

use App\Core\Database;
use App\Services\Inventory\ProductService;

/**
 * Product Import Service
 * Handles CSV/Excel import of products with field mapping and validation
 */
class ProductImportService
{
    private ProductService $productService;

    public function __construct()
    {
        $this->productService = new ProductService();
    }

    /**
     * Create a new import job
     *
     * @param array $fileData Uploaded file information
     * @param array $settings Import settings
     * @return int Import job ID
     */
    public function createImportJob(array $fileData, array $settings = []): int
    {
        $jobId = Database::insert(
            "INSERT INTO product_import_jobs (
                job_name, import_type, source_file, vendor_id, status,
                file_size, update_existing, match_field, skip_duplicates,
                auto_create_categories, auto_create_vendors, created_by, created_at
            ) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $settings['job_name'] ?? 'Product Import ' . date('Y-m-d H:i:s'),
                $fileData['import_type'] ?? 'csv',
                $fileData['file_path'],
                $settings['vendor_id'] ?? null,
                $fileData['file_size'],
                $settings['update_existing'] ?? false,
                $settings['match_field'] ?? 'sku',
                $settings['skip_duplicates'] ?? true,
                $settings['auto_create_categories'] ?? false,
                $settings['auto_create_vendors'] ?? false,
                currentUser()['id'] ?? null
            ]
        );

        return $jobId;
    }

    /**
     * Parse CSV file and return header columns
     *
     * @param string $filePath Path to CSV file
     * @return array Column headers
     */
    public function parseCSVHeaders(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Unable to open file');
        }

        $headers = fgetcsv($handle);
        fclose($handle);

        return array_map('trim', $headers);
    }

    /**
     * Parse CSV file and return all data
     *
     * @param string $filePath Path to CSV file
     * @param int $headerRow Row number containing headers (1-indexed)
     * @param int $maxRows Maximum rows to parse (0 = all)
     * @return array Parsed data with headers as keys
     */
    public function parseCSVData(string $filePath, int $headerRow = 1, int $maxRows = 0): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Unable to open file');
        }

        $data = [];
        $headers = [];
        $rowNumber = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Skip rows before header
            if ($rowNumber < $headerRow) {
                continue;
            }

            // This is the header row
            if ($rowNumber === $headerRow) {
                $headers = array_map('trim', $row);
                continue;
            }

            // Parse data rows
            if (!empty($headers)) {
                $rowData = [];
                foreach ($row as $index => $value) {
                    $header = $headers[$index] ?? "column_$index";
                    $rowData[$header] = trim($value);
                }
                $data[] = $rowData;
            }

            // Stop if we've reached max rows
            if ($maxRows > 0 && count($data) >= $maxRows) {
                break;
            }
        }

        fclose($handle);

        // Update job with total row count
        return $data;
    }

    /**
     * Auto-detect field mapping based on common column names
     *
     * @param array $csvHeaders Headers from CSV file
     * @return array Suggested field mapping
     */
    public function autoDetectFieldMapping(array $csvHeaders): array
    {
        $mapping = [];

        // Common field name patterns
        $patterns = [
            'sku' => ['sku', 'item#', 'item_number', 'partnumber', 'part_number', 'product_code'],
            'name' => ['name', 'product_name', 'title', 'description', 'item_name', 'product'],
            'description' => ['description', 'desc', 'long_description', 'details'],
            'barcode' => ['barcode', 'upc', 'ean', 'gtin', 'upc_code'],
            'cost_price' => ['cost', 'cost_price', 'wholesale', 'wholesale_price', 'dealer_price'],
            'retail_price' => ['price', 'retail', 'retail_price', 'msrp', 'list_price', 'selling_price'],
            'weight' => ['weight', 'wt', 'product_weight', 'item_weight'],
            'weight_unit' => ['weight_unit', 'wt_unit', 'unit'],
            'dimensions' => ['dimensions', 'size', 'dims'],
            'length' => ['length', 'len', 'l'],
            'width' => ['width', 'w'],
            'height' => ['height', 'h', 'ht'],
            'stock_quantity' => ['stock', 'quantity', 'qty', 'inventory', 'on_hand', 'qty_on_hand'],
            'category' => ['category', 'cat', 'product_category', 'type'],
            'vendor' => ['vendor', 'supplier', 'manufacturer', 'brand'],
            'vendor_sku' => ['vendor_sku', 'supplier_sku', 'mfg_sku'],
            'model' => ['model', 'model_number', 'model#'],
        ];

        foreach ($csvHeaders as $csvHeader) {
            $normalizedHeader = strtolower(str_replace([' ', '-'], '_', $csvHeader));

            foreach ($patterns as $fieldName => $possibleNames) {
                if (in_array($normalizedHeader, $possibleNames)) {
                    $mapping[$csvHeader] = $fieldName;
                    break;
                }
            }
        }

        return $mapping;
    }

    /**
     * Save field mapping for an import job
     *
     * @param int $jobId Import job ID
     * @param array $mapping Field mapping array
     * @param array $defaultValues Default values for unmapped fields
     * @return bool Success
     */
    public function saveFieldMapping(int $jobId, array $mapping, array $defaultValues = []): bool
    {
        return Database::execute(
            "UPDATE product_import_jobs
             SET field_mapping = ?, default_values = ?, status = 'mapping'
             WHERE id = ?",
            [json_encode($mapping), json_encode($defaultValues), $jobId]
        );
    }

    /**
     * Validate and preview import data
     *
     * @param int $jobId Import job ID
     * @param int $previewRows Number of rows to preview
     * @return array Preview data with validation
     */
    public function validateAndPreview(int $jobId, int $previewRows = 10): array
    {
        $job = $this->getImportJob($jobId);
        if (!$job) {
            throw new \Exception('Import job not found');
        }

        // Parse CSV data
        $csvData = $this->parseCSVData($job['source_file'], $job['header_row'], $previewRows);

        // Get field mapping
        $mapping = json_decode($job['field_mapping'], true) ?? [];
        $defaultValues = json_decode($job['default_values'], true) ?? [];

        $preview = [];
        $rowNumber = 1;

        foreach ($csvData as $rawRow) {
            $rowNumber++;

            // Map fields
            $mappedData = $this->mapRowData($rawRow, $mapping, $defaultValues);

            // Validate
            $validation = $this->validateProductData($mappedData, $job);

            // Check if product exists
            $existingProduct = null;
            if (!empty($mappedData[$job['match_field']])) {
                $existingProduct = $this->findExistingProduct(
                    $job['match_field'],
                    $mappedData[$job['match_field']]
                );
            }

            // Store preview data
            $previewId = Database::insert(
                "INSERT INTO product_import_preview (
                    import_job_id, row_number, raw_data, mapped_data,
                    validation_status, validation_messages,
                    will_create, will_update, existing_product_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $jobId,
                    $rowNumber,
                    json_encode($rawRow),
                    json_encode($mappedData),
                    $validation['status'],
                    json_encode($validation['messages']),
                    $existingProduct ? 0 : 1,
                    $existingProduct ? 1 : 0,
                    $existingProduct['id'] ?? null
                ]
            );

            $preview[] = [
                'id' => $previewId,
                'row_number' => $rowNumber,
                'raw_data' => $rawRow,
                'mapped_data' => $mappedData,
                'validation' => $validation,
                'existing_product' => $existingProduct,
                'will_create' => !$existingProduct,
                'will_update' => (bool)$existingProduct
            ];
        }

        // Update job status
        Database::execute(
            "UPDATE product_import_jobs
             SET status = 'validating', total_rows = ?
             WHERE id = ?",
            [count($csvData), $jobId]
        );

        return $preview;
    }

    /**
     * Execute the import
     *
     * @param int $jobId Import job ID
     * @return array Import results
     */
    public function executeImport(int $jobId): array
    {
        $job = $this->getImportJob($jobId);
        if (!$job) {
            throw new \Exception('Import job not found');
        }

        // Update status to importing
        Database::execute(
            "UPDATE product_import_jobs
             SET status = 'importing', started_at = NOW()
             WHERE id = ?",
            [$jobId]
        );

        // Parse all CSV data
        $csvData = $this->parseCSVData($job['source_file'], $job['header_row']);

        $mapping = json_decode($job['field_mapping'], true) ?? [];
        $defaultValues = json_decode($job['default_values'], true) ?? [];

        $stats = [
            'processed' => 0,
            'success' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
            'created_ids' => [],
            'updated_ids' => []
        ];

        $rowNumber = 1;

        foreach ($csvData as $rawRow) {
            $rowNumber++;
            $stats['processed']++;

            try {
                // Map fields
                $mappedData = $this->mapRowData($rawRow, $mapping, $defaultValues);

                // Validate
                $validation = $this->validateProductData($mappedData, $job);

                if ($validation['status'] === 'error') {
                    $stats['failed']++;
                    $stats['errors'][] = "Row {$rowNumber}: " . implode(', ', $validation['messages']);
                    continue;
                }

                // Check if product exists
                $existingProduct = null;
                if (!empty($mappedData[$job['match_field']])) {
                    $existingProduct = $this->findExistingProduct(
                        $job['match_field'],
                        $mappedData[$job['match_field']]
                    );
                }

                if ($existingProduct) {
                    if ($job['update_existing']) {
                        // Update existing product
                        $this->productService->updateProduct($existingProduct['id'], $mappedData);
                        $stats['updated']++;
                        $stats['updated_ids'][] = $existingProduct['id'];
                    } else {
                        $stats['skipped']++;
                    }
                } else {
                    // Create new product
                    // Handle category
                    if (!empty($mappedData['category']) && !is_numeric($mappedData['category'])) {
                        $mappedData['category_id'] = $this->findOrCreateCategory($mappedData['category'], $job['auto_create_categories']);
                        unset($mappedData['category']);
                    }

                    // Handle vendor
                    if (!empty($mappedData['vendor']) && !is_numeric($mappedData['vendor'])) {
                        $mappedData['vendor_id'] = $this->findOrCreateVendor($mappedData['vendor'], $job['auto_create_vendors']);
                        unset($mappedData['vendor']);
                    }

                    $productId = $this->productService->createProduct($mappedData);
                    $stats['success']++;
                    $stats['created_ids'][] = $productId;
                }

            } catch (\Exception $e) {
                $stats['failed']++;
                $stats['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
            }

            // Update progress
            if ($stats['processed'] % 10 === 0) {
                Database::execute(
                    "UPDATE product_import_jobs
                     SET rows_processed = ?, rows_success = ?, rows_updated = ?,
                         rows_skipped = ?, rows_failed = ?
                     WHERE id = ?",
                    [
                        $stats['processed'],
                        $stats['success'],
                        $stats['updated'],
                        $stats['skipped'],
                        $stats['failed'],
                        $jobId
                    ]
                );
            }
        }

        // Final update
        Database::execute(
            "UPDATE product_import_jobs
             SET status = ?, completed_at = NOW(),
                 rows_processed = ?, rows_success = ?, rows_updated = ?,
                 rows_skipped = ?, rows_failed = ?,
                 error_log = ?, imported_product_ids = ?, updated_product_ids = ?
             WHERE id = ?",
            [
                $stats['failed'] === 0 ? 'completed' : 'completed',
                $stats['processed'],
                $stats['success'],
                $stats['updated'],
                $stats['skipped'],
                $stats['failed'],
                json_encode($stats['errors']),
                json_encode($stats['created_ids']),
                json_encode($stats['updated_ids']),
                $jobId
            ]
        );

        // Clear preview data
        Database::execute(
            "DELETE FROM product_import_preview WHERE import_job_id = ?",
            [$jobId]
        );

        return $stats;
    }

    /**
     * Map CSV row data to product fields
     */
    private function mapRowData(array $rawRow, array $mapping, array $defaultValues): array
    {
        $mappedData = [];

        // Map CSV columns to product fields
        foreach ($mapping as $csvColumn => $productField) {
            if (isset($rawRow[$csvColumn])) {
                $mappedData[$productField] = $rawRow[$csvColumn];
            }
        }

        // Add default values for unmapped fields
        foreach ($defaultValues as $field => $value) {
            if (!isset($mappedData[$field])) {
                $mappedData[$field] = $value;
            }
        }

        // Type conversions
        if (isset($mappedData['cost_price'])) {
            $mappedData['cost_price'] = (float)str_replace(['$', ','], '', $mappedData['cost_price']);
        }
        if (isset($mappedData['retail_price'])) {
            $mappedData['retail_price'] = (float)str_replace(['$', ','], '', $mappedData['retail_price']);
        }
        if (isset($mappedData['stock_quantity'])) {
            $mappedData['stock_quantity'] = (int)$mappedData['stock_quantity'];
        }
        if (isset($mappedData['weight'])) {
            $mappedData['weight'] = (float)$mappedData['weight'];
        }

        return $mappedData;
    }

    /**
     * Validate product data
     */
    private function validateProductData(array $data, array $job): array
    {
        $messages = [];
        $status = 'valid';

        // Required fields
        if (empty($data['sku'])) {
            $messages[] = 'SKU is required';
            $status = 'error';
        }
        if (empty($data['name'])) {
            $messages[] = 'Product name is required';
            $status = 'error';
        }
        if (empty($data['retail_price']) || $data['retail_price'] <= 0) {
            $messages[] = 'Valid retail price is required';
            $status = 'error';
        }

        // Warnings
        if (empty($data['cost_price'])) {
            $messages[] = 'Cost price is missing (will use default 0)';
            if ($status !== 'error') $status = 'warning';
        }

        return [
            'status' => $status,
            'messages' => $messages
        ];
    }

    /**
     * Find existing product by field
     */
    private function findExistingProduct(string $field, string $value): ?array
    {
        return Database::fetchOne(
            "SELECT id, name, sku, retail_price FROM products WHERE {$field} = ?",
            [$value]
        );
    }

    /**
     * Find or create category
     */
    private function findOrCreateCategory(string $categoryName, bool $autoCreate): ?int
    {
        $category = Database::fetchOne(
            "SELECT id FROM product_categories WHERE name = ?",
            [$categoryName]
        );

        if ($category) {
            return $category['id'];
        }

        if ($autoCreate) {
            return Database::insert(
                "INSERT INTO product_categories (name, slug, is_active) VALUES (?, ?, 1)",
                [$categoryName, $this->generateSlug($categoryName)]
            );
        }

        return null;
    }

    /**
     * Find or create vendor
     */
    private function findOrCreateVendor(string $vendorName, bool $autoCreate): ?int
    {
        $vendor = Database::fetchOne(
            "SELECT id FROM vendors WHERE name = ?",
            [$vendorName]
        );

        if ($vendor) {
            return $vendor['id'];
        }

        if ($autoCreate) {
            return Database::insert(
                "INSERT INTO vendors (name, is_active) VALUES (?, 1)",
                [$vendorName]
            );
        }

        return null;
    }

    /**
     * Generate URL-friendly slug
     */
    private function generateSlug(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * Get import job by ID
     */
    public function getImportJob(int $jobId): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM product_import_jobs WHERE id = ?",
            [$jobId]
        );
    }

    /**
     * Get all import jobs
     */
    public function getAllImportJobs(int $limit = 50): array
    {
        return Database::fetchAll(
            "SELECT *,
                    (rows_success + rows_updated + rows_skipped + rows_failed) as total_processed
             FROM product_import_jobs
             ORDER BY created_at DESC
             LIMIT ?",
            [$limit]
        ) ?? [];
    }

    /**
     * Delete import job and related data
     */
    public function deleteImportJob(int $jobId): bool
    {
        // Delete preview data
        Database::execute(
            "DELETE FROM product_import_preview WHERE import_job_id = ?",
            [$jobId]
        );

        // Delete job
        return Database::execute(
            "DELETE FROM product_import_jobs WHERE id = ?",
            [$jobId]
        );
    }
}
