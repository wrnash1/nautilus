<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\Inventory\SerialNumberService;

class SerialNumberController extends Controller
{
    private SerialNumberService $serialService;

    public function __construct()
    {
        parent::__construct();
        $this->serialService = new SerialNumberService();
    }

    /**
     * List all serial numbers with filters
     */
    public function index(): void
    {
        $this->checkPermission('inventory.view');

        $status = $_GET['status'] ?? 'all';
        $productId = $_GET['product_id'] ?? null;
        $search = $_GET['search'] ?? '';

        $sql = "SELECT sn.*, p.name as product_name, p.sku, p.category_id,
                c.name as category_name
                FROM serial_numbers sn
                JOIN products p ON sn.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE 1=1";

        $params = [];

        if ($status !== 'all') {
            $sql .= " AND sn.status = ?";
            $params[] = $status;
        }

        if ($productId) {
            $sql .= " AND sn.product_id = ?";
            $params[] = $productId;
        }

        if (!empty($search)) {
            $sql .= " AND (sn.serial_number LIKE ? OR sn.barcode LIKE ? OR p.name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY sn.created_at DESC";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute($params);
        $serialNumbers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get products for filter dropdown
        $products = $this->db->getConnection()->query(
            "SELECT id, name, sku FROM products WHERE is_serialized = 1 ORDER BY name"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('serial_numbers/index', [
            'title' => 'Serial Number Tracking',
            'serial_numbers' => $serialNumbers,
            'products' => $products,
            'current_status' => $status,
            'current_product' => $productId,
            'search' => $search
        ]);
    }

    /**
     * Show individual serial number details
     */
    public function show(int $id): void
    {
        $this->checkPermission('inventory.view');

        $sql = "SELECT sn.*, p.name as product_name, p.sku, p.category_id,
                p.image_url, c.name as category_name
                FROM serial_numbers sn
                JOIN products p ON sn.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE sn.id = ?";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$id]);
        $serial = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$serial) {
            $_SESSION['error'] = 'Serial number not found';
            $this->redirect('/serial-numbers');
            return;
        }

        // Get history
        $history = $this->serialService->getHistory($id);

        // Get current transaction/rental if applicable
        $currentAssignment = $this->getCurrentAssignment($id, $serial['status']);

        $this->view('serial_numbers/show', [
            'title' => 'Serial Number: ' . $serial['serial_number'],
            'serial' => $serial,
            'history' => $history,
            'current_assignment' => $currentAssignment
        ]);
    }

    /**
     * Create new serial number
     */
    public function create(): void
    {
        $this->checkPermission('inventory.manage');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }

        // Get serialized products
        $products = $this->db->getConnection()->query(
            "SELECT id, name, sku FROM products WHERE is_serialized = 1 ORDER BY name"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('serial_numbers/create', [
            'title' => 'Add Serial Number',
            'products' => $products
        ]);
    }

    /**
     * Store new serial number
     */
    private function store(): void
    {
        try {
            $data = [
                'product_id' => $_POST['product_id'],
                'serial_number' => $_POST['serial_number'],
                'barcode' => $_POST['barcode'] ?? null,
                'status' => $_POST['status'] ?? 'available',
                'condition_rating' => $_POST['condition_rating'] ?? 10,
                'purchase_date' => $_POST['purchase_date'] ?? null,
                'purchase_cost' => $_POST['purchase_cost'] ?? null,
                'warranty_expiry' => $_POST['warranty_expiry'] ?? null,
                'location' => $_POST['location'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'performed_by' => $_SESSION['user_id'] ?? null
            ];

            // Generate barcode if not provided
            if (empty($data['barcode'])) {
                $data['barcode'] = $this->serialService->generateBarcode($data['product_id']);
            }

            $serialId = $this->serialService->createSerial($data);

            $_SESSION['success'] = 'Serial number added successfully';
            $this->redirect('/serial-numbers/' . $serialId);

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to add serial number: ' . $e->getMessage();
            $this->redirect('/serial-numbers/create');
        }
    }

    /**
     * Bulk import serial numbers from CSV
     */
    public function import(): void
    {
        $this->checkPermission('inventory.manage');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processImport();
            return;
        }

        $this->view('serial_numbers/import', [
            'title' => 'Import Serial Numbers'
        ]);
    }

    /**
     * Process CSV import
     */
    private function processImport(): void
    {
        try {
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('No file uploaded or upload error');
            }

            $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
            $header = fgetcsv($file); // Skip header row

            $imported = 0;
            $errors = [];

            while (($row = fgetcsv($file)) !== false) {
                try {
                    $data = [
                        'product_id' => $row[0],
                        'serial_number' => $row[1],
                        'barcode' => $row[2] ?? null,
                        'purchase_date' => $row[3] ?? null,
                        'purchase_cost' => $row[4] ?? null,
                        'location' => $row[5] ?? null,
                        'performed_by' => $_SESSION['user_id'] ?? null
                    ];

                    $this->serialService->createSerial($data);
                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Row {$imported}: " . $e->getMessage();
                }
            }

            fclose($file);

            $_SESSION['success'] = "Imported {$imported} serial numbers successfully";
            if (!empty($errors)) {
                $_SESSION['warning'] = "Some errors occurred: " . implode(', ', array_slice($errors, 0, 5));
            }

            $this->redirect('/serial-numbers');

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Import failed: ' . $e->getMessage();
            $this->redirect('/serial-numbers/import');
        }
    }

    /**
     * Scan barcode (AJAX)
     */
    public function scan(): void
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $barcode = $data['barcode'] ?? '';

            if (empty($barcode)) {
                echo json_encode(['success' => false, 'error' => 'No barcode provided']);
                return;
            }

            $serial = $this->serialService->findByBarcode($barcode);

            if ($serial) {
                echo json_encode([
                    'success' => true,
                    'data' => $serial
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Barcode not found',
                    'barcode' => $barcode
                ]);
            }

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update status (AJAX)
     */
    public function updateStatus(): void
    {
        header('Content-Type: application/json');

        try {
            $this->checkPermission('inventory.manage');

            $data = json_decode(file_get_contents('php://input'), true);
            $serialId = $data['serial_id'] ?? 0;
            $newStatus = $data['status'] ?? '';

            if (!$serialId || !$newStatus) {
                echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
                return;
            }

            $success = $this->serialService->updateStatus($serialId, $newStatus, [
                'performed_by' => $_SESSION['user_id'] ?? null,
                'notes' => $data['notes'] ?? null
            ]);

            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Status updated successfully' : 'Failed to update status'
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update location (AJAX)
     */
    public function updateLocation(): void
    {
        header('Content-Type: application/json');

        try {
            $this->checkPermission('inventory.manage');

            $data = json_decode(file_get_contents('php://input'), true);
            $serialId = $data['serial_id'] ?? 0;
            $newLocation = $data['location'] ?? '';

            if (!$serialId || !$newLocation) {
                echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
                return;
            }

            $success = $this->serialService->updateLocation(
                $serialId,
                $newLocation,
                $_SESSION['user_id'] ?? null
            );

            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Location updated successfully' : 'Failed to update location'
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Items due for service
     */
    public function serviceReminders(): void
    {
        $this->checkPermission('inventory.view');

        $items = $this->serialService->getItemsDueForService();

        $this->view('serial_numbers/service_reminders', [
            'title' => 'Service Reminders',
            'items' => $items
        ]);
    }

    /**
     * Scan statistics
     */
    public function statistics(): void
    {
        $this->checkPermission('inventory.view');

        $days = $_GET['days'] ?? 30;
        $stats = $this->serialService->getScanStatistics($days);

        // Get status counts
        $sql = "SELECT status, COUNT(*) as count
                FROM serial_numbers
                GROUP BY status";

        $statusCounts = $this->db->getConnection()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('serial_numbers/statistics', [
            'title' => 'Serial Number Statistics',
            'scan_stats' => $stats,
            'status_counts' => $statusCounts,
            'days' => $days
        ]);
    }

    /**
     * Get current assignment (rental or transaction)
     */
    private function getCurrentAssignment(int $serialId, string $status): ?array
    {
        if ($status === 'rented') {
            $sql = "SELECT r.*, c.first_name, c.last_name, c.email
                    FROM rental_reservations r
                    JOIN customers c ON r.customer_id = c.id
                    JOIN rental_items ri ON r.id = ri.rental_reservation_id
                    WHERE ri.serial_number_id = ?
                    AND r.status IN ('active', 'checked_out')
                    LIMIT 1";

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$serialId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        }

        if ($status === 'service') {
            $sql = "SELECT wo.*, c.first_name, c.last_name
                    FROM work_orders wo
                    LEFT JOIN customers c ON wo.customer_id = c.id
                    WHERE wo.serial_number_id = ?
                    AND wo.status IN ('pending', 'in_progress')
                    LIMIT 1";

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$serialId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        }

        return null;
    }
}
