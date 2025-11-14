<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\Inventory\VendorImportService;

class VendorCatalogController extends Controller
{
    private VendorImportService $importService;

    public function __construct()
    {
        parent::__construct();
        $this->importService = new VendorImportService();
    }

    /**
     * Show import upload page
     */
    public function index(): void
    {
        $this->checkPermission('products.import');

        $vendors = $this->getVendors();
        $templates = $this->importService->getVendorTemplates();

        $this->view('vendors/import/index', [
            'title' => 'Import Vendor Catalog',
            'vendors' => $vendors,
            'templates' => $templates
        ]);
    }

    /**
     * Upload and parse file
     */
    public function upload(): void
    {
        $this->checkPermission('products.import');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vendors/import');
            return;
        }

        try {
            // Validate file upload
            if (!isset($_FILES['catalog_file']) || $_FILES['catalog_file']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('File upload failed');
            }

            $file = $_FILES['catalog_file'];
            $vendorId = (int)($_POST['vendor_id'] ?? 0);

            if ($vendorId === 0) {
                throw new \Exception('Please select a vendor');
            }

            // Move uploaded file to temp location
            $uploadDir = BASE_PATH . '/storage/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = uniqid('import_') . '_' . basename($file['name']);
            $filepath = $uploadDir . '/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new \Exception('Failed to save uploaded file');
            }

            // Parse file
            $parsed = $this->importService->parseFile($filepath);

            // Auto-detect column mappings
            $mappings = $this->importService->detectColumnMappings($parsed['headers']);

            // Store parsed data in session for mapping step
            $_SESSION['import_data'] = [
                'filepath' => $filepath,
                'vendor_id' => $vendorId,
                'headers' => $parsed['headers'],
                'data' => $parsed['data'],
                'row_count' => $parsed['row_count'],
                'detected_mappings' => $mappings
            ];

            $this->redirect('/vendors/import/map');

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/vendors/import');
        }
    }

    /**
     * Show column mapping interface
     */
    public function map(): void
    {
        $this->checkPermission('products.import');

        if (!isset($_SESSION['import_data'])) {
            $this->redirect('/vendors/import');
            return;
        }

        $importData = $_SESSION['import_data'];

        $this->view('vendors/import/map', [
            'title' => 'Map Columns',
            'headers' => $importData['headers'],
            'row_count' => $importData['row_count'],
            'detected_mappings' => $importData['detected_mappings'],
            'sample_row' => $importData['data'][0] ?? []
        ]);
    }

    /**
     * Validate and preview import
     */
    public function preview(): void
    {
        $this->checkPermission('products.import');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['import_data'])) {
            $this->redirect('/vendors/import');
            return;
        }

        try {
            $importData = $_SESSION['import_data'];
            $mappings = $_POST['mappings'] ?? [];

            // Validate data
            $validation = $this->importService->validateData($importData['data'], $mappings);

            if (!$validation['valid']) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', array_slice($validation['errors'], 0, 5));
                $_SESSION['import_mappings'] = $mappings;
                $this->redirect('/vendors/import/map');
                return;
            }

            // Stage products
            $catalogId = $this->importService->stageProducts(
                $importData['vendor_id'],
                $importData['data'],
                $mappings,
                $this->auth->getUserId()
            );

            // Store catalog ID
            $_SESSION['import_catalog_id'] = $catalogId;
            $_SESSION['import_mappings'] = $mappings;

            $this->redirect('/vendors/import/preview/' . $catalogId);

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/vendors/import/map');
        }
    }

    /**
     * Show preview of staged products
     */
    public function showPreview(int $catalogId): void
    {
        $this->checkPermission('products.import');

        $stagedProducts = $this->importService->getStagedProducts($catalogId, 50);
        $totalCount = $this->importService->getStagedCount($catalogId);

        $this->view('vendors/import/preview', [
            'title' => 'Preview Import',
            'catalog_id' => $catalogId,
            'products' => $stagedProducts,
            'total_count' => $totalCount,
            'showing_count' => count($stagedProducts)
        ]);
    }

    /**
     * Commit the import
     */
    public function commit(): void
    {
        $this->checkPermission('products.import');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vendors/import');
            return;
        }

        try {
            $catalogId = (int)($_POST['catalog_id'] ?? 0);
            $updateExisting = isset($_POST['update_existing']);

            if ($catalogId === 0) {
                throw new \Exception('Invalid catalog ID');
            }

            $result = $this->importService->commitImport($catalogId, [
                'update_existing' => $updateExisting
            ]);

            if ($result['success']) {
                $_SESSION['success'] = sprintf(
                    'Import completed: %d created, %d updated, %d skipped',
                    $result['created'],
                    $result['updated'],
                    $result['skipped']
                );

                // Clear session data
                unset($_SESSION['import_data']);
                unset($_SESSION['import_catalog_id']);
                unset($_SESSION['import_mappings']);

                $this->redirect('/products');
            } else {
                throw new \Exception($result['error'] ?? 'Import failed');
            }

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/vendors/import');
        }
    }

    /**
     * Cancel import and delete staged data
     */
    public function cancel(): void
    {
        $this->checkPermission('products.import');

        if (isset($_SESSION['import_catalog_id'])) {
            $this->importService->deleteCatalog($_SESSION['import_catalog_id']);
        }

        // Clear session data
        unset($_SESSION['import_data']);
        unset($_SESSION['import_catalog_id']);
        unset($_SESSION['import_mappings']);

        $_SESSION['info'] = 'Import cancelled';
        $this->redirect('/vendors/import');
    }

    /**
     * Get vendors list
     */
    protected function getVendors(): array
    {
        $sql = "SELECT id, name FROM vendors WHERE is_active = 1 ORDER BY name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
