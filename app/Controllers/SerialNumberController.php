<?php

namespace App\Controllers;

use App\Services\Inventory\SerialNumberService;

class SerialNumberController
{
    private SerialNumberService $serialService;

    public function __construct()
    {
        $this->serialService = new SerialNumberService();
    }

    /**
     * List all serial numbers with filters
     */
    public function index(): void
    {
        require_once __DIR__ . '/../Views/serial_numbers/index.php';
    }

    /**
     * Show individual serial number details
     */
    public function show(int $id): void
    {
        require_once __DIR__ . '/../Views/serial_numbers/show.php';
    }

    /**
     * Create new serial number
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }

        require_once __DIR__ . '/../Views/serial_numbers/create.php';
    }

    /**
     * Store new serial number
     */
    private function store(): void
    {
        try {
            $db = \App\Core\Database::getInstance();

            $sql = "INSERT INTO serial_numbers
                    (product_id, serial_number, barcode, status, condition_rating,
                     purchase_date, purchase_cost, warranty_expiry, location, notes, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $db->getConnection()->prepare($sql);
            $stmt->execute([
                $_POST['product_id'],
                $_POST['serial_number'],
                $_POST['barcode'] ?? null,
                $_POST['status'] ?? 'available',
                $_POST['condition_rating'] ?? null,
                $_POST['purchase_date'] ?? null,
                $_POST['purchase_cost'] ?? null,
                $_POST['warranty_expiry'] ?? null,
                $_POST['location'] ?? null,
                $_POST['notes'] ?? null
            ]);

            $serialId = (int)$db->getConnection()->lastInsertId();

            $_SESSION['success'] = 'Serial number added successfully';
            header('Location: /serial-numbers/' . $serialId);
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to add serial number: ' . $e->getMessage();
            header('Location: /serial-numbers/create');
            exit;
        }
    }

    /**
     * Scan barcode (AJAX)
     */
    public function scan(): void
    {
        header('Content-Type: application/json');

        $barcode = $_POST['barcode'] ?? '';

        try {
            $result = $this->serialService->scanBarcode($barcode);

            echo json_encode([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
