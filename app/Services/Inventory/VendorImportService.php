<?php

namespace App\Services\Inventory;

use App\Core\Database;
use App\Core\Logger;
use Exception;

/**
 * Vendor Product Catalog Import Service
 * Handles CSV/Excel imports from vendor catalogs
 */
class VendorImportService
{
    private Database $db;
    private Logger $logger;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new Logger();
    }

    /**
     * Parse uploaded file (CSV or Excel)
     */
    public function parseFile(string $filepath): array
    {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        try {
            switch ($extension) {
                case 'csv':
                    return $this->parseCSV($filepath);
                case 'xlsx':
                case 'xls':
                    return $this->parseExcel($filepath);
                default:
                    throw new Exception("Unsupported file format: {$extension}");
            }
        } catch (Exception $e) {
            $this->logger->error('File parsing failed', [
                'filepath' => $filepath,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Parse CSV file
     */
    private function parseCSV(string $filepath): array
    {
        $data = [];
        $headers = [];

        if (($handle = fopen($filepath, 'r')) !== false) {
            // Read header row
            $headers = fgetcsv($handle);

            // Clean headers
            $headers = array_map(function($header) {
                return trim(strtolower($header));
            }, $headers);

            // Read data rows
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === count($headers)) {
                    $data[] = array_combine($headers, $row);
                }
            }

            fclose($handle);
        }

        return [
            'headers' => $headers,
            'data' => $data,
            'row_count' => count($data)
        ];
    }

    /**
     * Parse Excel file (requires PhpSpreadsheet)
     */
    private function parseExcel(string $filepath): array
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            throw new Exception('PhpSpreadsheet library not installed. Run: composer require phpoffice/phpspreadsheet');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filepath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // First row as headers
        $headers = array_map(function($header) {
            return trim(strtolower($header));
        }, $rows[0]);

        // Rest as data
        $data = [];
        for ($i = 1; $i < count($rows); $i++) {
            if (count($rows[$i]) === count($headers)) {
                $data[] = array_combine($headers, $rows[$i]);
            }
        }

        return [
            'headers' => $headers,
            'data' => $data,
            'row_count' => count($data)
        ];
    }

    /**
     * Auto-detect column mappings
     */
    public function detectColumnMappings(array $headers): array
    {
        $mappings = [];

        // Define possible column name variations
        $columnPatterns = [
            'sku' => ['sku', 'item', 'item #', 'item number', 'product code', 'part number', 'part #'],
            'name' => ['name', 'product name', 'description', 'item name', 'title'],
            'price' => ['price', 'retail price', 'msrp', 'selling price', 'list price'],
            'cost' => ['cost', 'wholesale', 'dealer price', 'your cost', 'net price'],
            'category' => ['category', 'product category', 'type', 'product type'],
            'description' => ['description', 'long description', 'details', 'product description'],
            'manufacturer' => ['manufacturer', 'brand', 'make'],
            'upc' => ['upc', 'barcode', 'ean', 'gtin'],
            'weight' => ['weight', 'product weight', 'shipping weight'],
            'stock' => ['stock', 'qty', 'quantity', 'available', 'in stock', 'inventory']
        ];

        foreach ($headers as $index => $header) {
            $header = strtolower(trim($header));

            foreach ($columnPatterns as $field => $patterns) {
                foreach ($patterns as $pattern) {
                    if (strpos($header, $pattern) !== false) {
                        $mappings[$field] = $index;
                        break 2; // Move to next header
                    }
                }
            }
        }

        return $mappings;
    }

    /**
     * Validate import data
     */
    public function validateData(array $data, array $mappings): array
    {
        $errors = [];
        $warnings = [];

        foreach ($data as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // +2 because header is row 1, and array is 0-indexed

            // Check required fields
            if (isset($mappings['sku'])) {
                $sku = trim($row[$mappings['sku']] ?? '');
                if (empty($sku)) {
                    $errors[] = "Row {$rowNumber}: SKU is required";
                }
            } else {
                $errors[] = "SKU column not mapped";
                break; // Critical error
            }

            if (isset($mappings['name'])) {
                $name = trim($row[$mappings['name']] ?? '');
                if (empty($name)) {
                    $errors[] = "Row {$rowNumber}: Product name is required";
                }
            }

            if (isset($mappings['price'])) {
                $price = $row[$mappings['price']] ?? '';
                if (!is_numeric($price) || $price < 0) {
                    $warnings[] = "Row {$rowNumber}: Invalid price '{$price}'";
                }
            }

            if (isset($mappings['cost'])) {
                $cost = $row[$mappings['cost']] ?? '';
                if (!empty($cost) && (!is_numeric($cost) || $cost < 0)) {
                    $warnings[] = "Row {$rowNumber}: Invalid cost '{$cost}'";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Stage products to vendor_catalog_items table
     */
    public function stageProducts(int $vendorId, array $data, array $mappings, ?int $userId = null): int
    {
        try {
            $this->db->getConnection()->beginTransaction();

            // Create catalog record
            $catalogId = $this->createCatalog($vendorId, $userId);

            $staged = 0;

            foreach ($data as $row) {
                $productData = $this->mapRowToProduct($row, $mappings);

                if ($this->stageProduct($catalogId, $productData)) {
                    $staged++;
                }
            }

            $this->db->getConnection()->commit();

            $this->logger->info('Products staged for import', [
                'catalog_id' => $catalogId,
                'vendor_id' => $vendorId,
                'count' => $staged
            ]);

            return $catalogId;

        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            $this->logger->error('Product staging failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create catalog record
     */
    private function createCatalog(int $vendorId, ?int $userId): int
    {
        $sql = "INSERT INTO vendor_catalogs (vendor_id, imported_by, status, created_at)
                VALUES (?, ?, 'staged', NOW())";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$vendorId, $userId]);

        return (int)$this->db->getConnection()->lastInsertId();
    }

    /**
     * Map row data to product fields
     */
    private function mapRowToProduct(array $row, array $mappings): array
    {
        $product = [];

        $fieldMap = [
            'sku' => 'sku',
            'name' => 'name',
            'price' => 'price',
            'cost' => 'cost',
            'category' => 'category',
            'description' => 'description',
            'manufacturer' => 'manufacturer',
            'upc' => 'upc',
            'weight' => 'weight',
            'stock' => 'stock_quantity'
        ];

        foreach ($fieldMap as $source => $target) {
            if (isset($mappings[$source]) && isset($row[$mappings[$source]])) {
                $product[$target] = trim($row[$mappings[$source]]);
            }
        }

        return $product;
    }

    /**
     * Stage individual product
     */
    private function stageProduct(int $catalogId, array $productData): bool
    {
        $sql = "INSERT INTO vendor_catalog_items
                (catalog_id, sku, name, description, price, cost, category, manufacturer, upc, weight, stock_quantity, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->getConnection()->prepare($sql);

        return $stmt->execute([
            $catalogId,
            $productData['sku'] ?? null,
            $productData['name'] ?? null,
            $productData['description'] ?? null,
            $productData['price'] ?? null,
            $productData['cost'] ?? null,
            $productData['category'] ?? null,
            $productData['manufacturer'] ?? null,
            $productData['upc'] ?? null,
            $productData['weight'] ?? null,
            $productData['stock_quantity'] ?? null
        ]);
    }

    /**
     * Get staged products for preview
     */
    public function getStagedProducts(int $catalogId, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM vendor_catalog_items
                WHERE catalog_id = ?
                ORDER BY id
                LIMIT ? OFFSET ?";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$catalogId, $limit, $offset]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get staged product count
     */
    public function getStagedCount(int $catalogId): int
    {
        $sql = "SELECT COUNT(*) as count FROM vendor_catalog_items WHERE catalog_id = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$catalogId]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Commit import - move staged products to main products table
     */
    public function commitImport(int $catalogId, array $options = []): array
    {
        try {
            $this->db->getConnection()->beginTransaction();

            $sql = "SELECT * FROM vendor_catalog_items WHERE catalog_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$catalogId]);
            $stagedProducts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $created = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($stagedProducts as $staged) {
                $existing = $this->findExistingProduct($staged['sku']);

                if ($existing) {
                    if ($options['update_existing'] ?? false) {
                        $this->updateProduct($existing['id'], $staged);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    $this->createProduct($staged);
                    $created++;
                }
            }

            // Update catalog status
            $this->updateCatalogStatus($catalogId, 'imported');

            $this->db->getConnection()->commit();

            $this->logger->info('Import committed', [
                'catalog_id' => $catalogId,
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped
            ]);

            return [
                'success' => true,
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
                'total' => count($stagedProducts)
            ];

        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            $this->logger->error('Import commit failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Find existing product by SKU
     */
    private function findExistingProduct(string $sku): ?array
    {
        $sql = "SELECT id FROM products WHERE sku = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$sku]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Create new product
     */
    private function createProduct(array $data): int
    {
        $sql = "INSERT INTO products
                (sku, name, description, price, cost, category_id, manufacturer, barcode, weight, stock_quantity, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";

        // Map category name to category_id
        $categoryId = $this->findOrCreateCategory($data['category'] ?? 'Imported');

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([
            $data['sku'],
            $data['name'],
            $data['description'],
            $data['price'],
            $data['cost'],
            $categoryId,
            $data['manufacturer'],
            $data['upc'],
            $data['weight'],
            $data['stock_quantity'] ?? 0
        ]);

        return (int)$this->db->getConnection()->lastInsertId();
    }

    /**
     * Update existing product
     */
    private function updateProduct(int $productId, array $data): bool
    {
        $sql = "UPDATE products SET
                name = ?, description = ?, price = ?, cost = ?,
                manufacturer = ?, barcode = ?, weight = ?,
                updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->db->getConnection()->prepare($sql);

        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['cost'],
            $data['manufacturer'],
            $data['upc'],
            $data['weight'],
            $productId
        ]);
    }

    /**
     * Find or create category
     */
    private function findOrCreateCategory(string $categoryName): int
    {
        // Try to find existing category
        $sql = "SELECT id FROM categories WHERE name = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$categoryName]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            return (int)$result['id'];
        }

        // Create new category
        $sql = "INSERT INTO categories (name, created_at) VALUES (?, NOW())";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$categoryName]);

        return (int)$this->db->getConnection()->lastInsertId();
    }

    /**
     * Update catalog status
     */
    private function updateCatalogStatus(int $catalogId, string $status): void
    {
        $sql = "UPDATE vendor_catalogs SET status = ?, imported_at = NOW() WHERE id = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$status, $catalogId]);
    }

    /**
     * Delete staged catalog
     */
    public function deleteCatalog(int $catalogId): bool
    {
        try {
            $this->db->getConnection()->beginTransaction();

            // Delete items
            $sql = "DELETE FROM vendor_catalog_items WHERE catalog_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$catalogId]);

            // Delete catalog
            $sql = "DELETE FROM vendor_catalogs WHERE id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$catalogId]);

            $this->db->getConnection()->commit();

            return true;
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            $this->logger->error('Failed to delete catalog', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get vendor catalog templates
     */
    public function getVendorTemplates(): array
    {
        return [
            'scubapro' => [
                'name' => 'Scubapro',
                'mappings' => [
                    'sku' => 'Item Number',
                    'name' => 'Description',
                    'price' => 'MSRP',
                    'cost' => 'Dealer Price'
                ]
            ],
            'aqualung' => [
                'name' => 'Aqua Lung',
                'mappings' => [
                    'sku' => 'SKU',
                    'name' => 'Product Name',
                    'price' => 'Retail Price',
                    'cost' => 'Wholesale Price'
                ]
            ],
            'mares' => [
                'name' => 'Mares',
                'mappings' => [
                    'sku' => 'Code',
                    'name' => 'Name',
                    'price' => 'Price',
                    'cost' => 'Cost'
                ]
            ],
            'generic' => [
                'name' => 'Generic CSV',
                'mappings' => []
            ]
        ];
    }
}
