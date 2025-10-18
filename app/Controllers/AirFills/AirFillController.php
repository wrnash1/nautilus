<?php

namespace App\Controllers\AirFills;

use App\Core\Database;
use App\Services\AirFills\AirFillService;

class AirFillController
{
    private AirFillService $service;

    public function __construct()
    {
        $this->service = new AirFillService();
    }

    /**
     * Display list of air fills
     */
    public function index()
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 25;
        $search = $_GET['search'] ?? '';
        $fillType = $_GET['fill_type'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';

        $filters = [
            'search' => $search,
            'fill_type' => $fillType,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];

        $result = $this->service->getAirFills($page, $perPage, $filters);
        $airFills = $result['data'];
        $totalPages = $result['totalPages'];
        $totalRecords = $result['totalRecords'];

        // Get summary statistics
        $stats = $this->service->getStatistics($filters);

        require __DIR__ . '/../../Views/air-fills/index.php';
    }

    /**
     * Show create form
     */
    public function create()
    {
        // Get customers for dropdown
        $customers = Database::fetchAll(
            "SELECT id, CONCAT(first_name, ' ', last_name) as name, email
             FROM customers
             WHERE is_active = 1
             ORDER BY first_name, last_name"
        );

        // Get rental equipment (tanks) for dropdown
        $tanks = Database::fetchAll(
            "SELECT re.id, re.name, re.equipment_code, rc.name as category_name
             FROM rental_equipment re
             LEFT JOIN rental_categories rc ON re.category_id = rc.id
             WHERE re.status = 'available'
             AND rc.name LIKE '%tank%'
             ORDER BY re.name"
        );

        // Get staff members for "filled_by" dropdown
        $staff = Database::fetchAll(
            "SELECT id, CONCAT(first_name, ' ', last_name) as name
             FROM users
             WHERE is_active = 1
             ORDER BY first_name, last_name"
        );

        require __DIR__ . '/../../Views/air-fills/create.php';
    }

    /**
     * Store new air fill
     */
    public function store()
    {
        try {
            $data = [
                'customer_id' => $_POST['customer_id'] ?? null,
                'equipment_id' => $_POST['equipment_id'] ?? null,
                'fill_type' => $_POST['fill_type'] ?? 'air',
                'fill_pressure' => $_POST['fill_pressure'] ?? 3000,
                'nitrox_percentage' => $_POST['nitrox_percentage'] ?? null,
                'cost' => $_POST['cost'] ?? 0,
                'notes' => $_POST['notes'] ?? '',
                'filled_by' => currentUser()['id'],
                'create_transaction' => isset($_POST['create_transaction'])
            ];

            $airFillId = $this->service->createAirFill($data);

            setFlashMessage('success', 'Air fill recorded successfully!');

            if ($data['create_transaction']) {
                header('Location: /air-fills/' . $airFillId);
            } else {
                header('Location: /air-fills');
            }
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Failed to record air fill: ' . $e->getMessage());
            header('Location: /air-fills/create');
            exit;
        }
    }

    /**
     * Show air fill details
     */
    public function show(int $id)
    {
        $airFill = $this->service->getAirFillById($id);

        if (!$airFill) {
            setFlashMessage('error', 'Air fill not found');
            header('Location: /air-fills');
            exit;
        }

        require __DIR__ . '/../../Views/air-fills/show.php';
    }

    /**
     * Show edit form
     */
    public function edit(int $id)
    {
        $airFill = $this->service->getAirFillById($id);

        if (!$airFill) {
            setFlashMessage('error', 'Air fill not found');
            header('Location: /air-fills');
            exit;
        }

        // Check if already linked to transaction (cannot edit)
        if ($airFill['transaction_id']) {
            setFlashMessage('error', 'Cannot edit air fill that is linked to a transaction');
            header('Location: /air-fills/' . $id);
            exit;
        }

        // Get customers for dropdown
        $customers = Database::fetchAll(
            "SELECT id, CONCAT(first_name, ' ', last_name) as name, email
             FROM customers
             WHERE is_active = 1
             ORDER BY first_name, last_name"
        );

        // Get rental equipment (tanks) for dropdown
        $tanks = Database::fetchAll(
            "SELECT re.id, re.name, re.equipment_code, rc.name as category_name
             FROM rental_equipment re
             LEFT JOIN rental_categories rc ON re.category_id = rc.id
             WHERE re.status = 'available'
             OR re.id = ?
             ORDER BY re.name",
            [$airFill['equipment_id']]
        );

        require __DIR__ . '/../../Views/air-fills/edit.php';
    }

    /**
     * Update air fill
     */
    public function update(int $id)
    {
        try {
            $airFill = $this->service->getAirFillById($id);

            if (!$airFill) {
                throw new \Exception('Air fill not found');
            }

            // Check if already linked to transaction
            if ($airFill['transaction_id']) {
                throw new \Exception('Cannot edit air fill that is linked to a transaction');
            }

            $data = [
                'customer_id' => $_POST['customer_id'] ?? null,
                'equipment_id' => $_POST['equipment_id'] ?? null,
                'fill_type' => $_POST['fill_type'] ?? 'air',
                'fill_pressure' => $_POST['fill_pressure'] ?? 3000,
                'nitrox_percentage' => $_POST['nitrox_percentage'] ?? null,
                'cost' => $_POST['cost'] ?? 0,
                'notes' => $_POST['notes'] ?? ''
            ];

            $this->service->updateAirFill($id, $data);

            setFlashMessage('success', 'Air fill updated successfully!');
            header('Location: /air-fills/' . $id);
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Failed to update air fill: ' . $e->getMessage());
            header('Location: /air-fills/' . $id . '/edit');
            exit;
        }
    }

    /**
     * Delete air fill
     */
    public function delete(int $id)
    {
        try {
            $airFill = $this->service->getAirFillById($id);

            if (!$airFill) {
                throw new \Exception('Air fill not found');
            }

            // Check if already linked to transaction
            if ($airFill['transaction_id']) {
                throw new \Exception('Cannot delete air fill that is linked to a transaction. Void the transaction instead.');
            }

            $this->service->deleteAirFill($id);

            setFlashMessage('success', 'Air fill deleted successfully!');
            header('Location: /air-fills');
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Failed to delete air fill: ' . $e->getMessage());
            header('Location: /air-fills/' . $id);
            exit;
        }
    }

    /**
     * Quick fill form (simplified for rapid entry)
     */
    public function quickFill()
    {
        require __DIR__ . '/../../Views/air-fills/quick-fill.php';
    }

    /**
     * Process quick fill
     */
    public function processQuickFill()
    {
        try {
            $data = [
                'customer_id' => $_POST['customer_id'] ?? null,
                'fill_type' => $_POST['fill_type'] ?? 'air',
                'fill_pressure' => $_POST['fill_pressure'] ?? 3000,
                'nitrox_percentage' => ($_POST['fill_type'] === 'nitrox') ? ($_POST['nitrox_percentage'] ?? 32) : null,
                'cost' => $_POST['cost'] ?? 0,
                'filled_by' => currentUser()['id'],
                'create_transaction' => true
            ];

            $this->service->createAirFill($data);

            echo json_encode(['success' => true, 'message' => 'Air fill recorded']);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Export air fills to CSV
     */
    public function export()
    {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'fill_type' => $_GET['fill_type'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        $airFills = $this->service->getAllAirFills($filters);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="air-fills-' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Headers
        fputcsv($output, [
            'ID',
            'Date',
            'Customer',
            'Fill Type',
            'Pressure (PSI)',
            'Nitrox %',
            'Cost',
            'Filled By',
            'Equipment',
            'Transaction ID'
        ]);

        // Data
        foreach ($airFills as $fill) {
            fputcsv($output, [
                $fill['id'],
                $fill['created_at'],
                $fill['customer_name'] ?? 'Walk-in',
                ucfirst($fill['fill_type']),
                $fill['fill_pressure'],
                $fill['nitrox_percentage'] ?? 'N/A',
                $fill['cost'],
                $fill['filled_by_name'],
                $fill['equipment_name'] ?? 'N/A',
                $fill['transaction_id'] ?? 'N/A'
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Get pricing for fill type (AJAX)
     */
    public function getPricing()
    {
        $fillType = $_GET['fill_type'] ?? 'air';
        $pressure = (int)($_GET['pressure'] ?? 3000);

        $pricing = $this->service->calculatePricing($fillType, $pressure);

        header('Content-Type: application/json');
        echo json_encode($pricing);
    }
}
