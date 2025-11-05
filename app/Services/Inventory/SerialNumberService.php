<?php

namespace App\Services\Inventory;

use App\Core\Database;
use PDO;
use App\Core\Logger;
use Exception;

/**
 * Serial Number & Barcode Tracking Service
 * Manages individual item tracking by serial number and barcode
 */
class SerialNumberService
{
    private PDO $db;
    private Logger $logger;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new Logger();
    }

    /**
     * Create new serial number entry
     */
    public function createSerial(array $data): int
    {
        try {
            $sql = "INSERT INTO serial_numbers
                    (product_id, serial_number, barcode, status, condition_rating,
                     purchase_date, purchase_cost, warranty_expiry, location, notes, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['product_id'],
                $data['serial_number'],
                $data['barcode'] ?? null,
                $data['status'] ?? 'available',
                $data['condition_rating'] ?? 10,
                $data['purchase_date'] ?? null,
                $data['purchase_cost'] ?? null,
                $data['warranty_expiry'] ?? null,
                $data['location'] ?? null,
                $data['notes'] ?? null
            ]);

            $serialId = (int)$this->db->lastInsertId();

            // Log creation
            $this->logEvent($serialId, 'created', null, 'available',
                'Serial number created', $data['performed_by'] ?? null);

            $this->logger->info('Serial number created', [
                'serial_id' => $serialId,
                'serial_number' => $data['serial_number']
            ]);

            return $serialId;

        } catch (Exception $e) {
            $this->logger->error('Failed to create serial number', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Get all serial numbers with filters
     */
    public function getAllWithFilters(array $filters = []): array
    {
        $sql = "SELECT sn.*, p.name as product_name, p.sku
                FROM serial_numbers sn
                LEFT JOIN products p ON sn.product_id = p.id
                WHERE 1=1";

        $params = [];

        // Filter by serial number
        if (!empty($filters['serial'])) {
            $sql .= " AND sn.serial_number LIKE ?";
            $params[] = '%' . $filters['serial'] . '%';
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $sql .= " AND sn.status = ?";
            $params[] = $filters['status'];
        }

        // Filter by service due
        if (!empty($filters['service_due'])) {
            switch ($filters['service_due']) {
                case 'overdue':
                    $sql .= " AND sn.next_service_due < CURDATE()";
                    break;
                case '30days':
                    $sql .= " AND sn.next_service_due BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
                    break;
                case '90days':
                    $sql .= " AND sn.next_service_due BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)";
                    break;
            }
        }

        $sql .= " ORDER BY sn.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Find by serial number
     */
    public function findBySerial(string $serialNumber): ?array
    {
        $sql = "SELECT sn.*, p.name as product_name, p.sku, p.category_id
                FROM serial_numbers sn
                JOIN products p ON sn.product_id = p.id
                WHERE sn.serial_number = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$serialNumber]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Find by barcode
     */
    public function findByBarcode(string $barcode): ?array
    {
        // Log the scan
        $this->logBarcodeToken($barcode, 'search');

        $sql = "SELECT sn.*, p.name as product_name, p.sku, p.category_id
                FROM serial_numbers sn
                JOIN products p ON sn.product_id = p.id
                WHERE sn.barcode = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$barcode]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            $this->updateBarcodeScanLog($barcode, $result['id'], $result['product_id'], 'success');
        } else {
            $this->updateBarcodeScanLog($barcode, null, null, 'not_found');
        }

        return $result ?: null;
    }

    /**
     * Get all serial numbers for a product
     */
    public function getByProduct(int $productId): array
    {
        $sql = "SELECT * FROM serial_numbers
                WHERE product_id = ?
                ORDER BY serial_number";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get available serial numbers for a product
     */
    public function getAvailableByProduct(int $productId): array
    {
        $sql = "SELECT * FROM serial_numbers
                WHERE product_id = ? AND status = 'available'
                ORDER BY serial_number";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Update serial number status
     */
    public function updateStatus(int $serialId, string $newStatus, ?array $options = []): bool
    {
        try {
            // Get current status
            $sql = "SELECT status, location FROM serial_numbers WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$serialId]);
            $current = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$current) {
                throw new Exception('Serial number not found');
            }

            $oldStatus = $current['status'];

            // Update status
            $sql = "UPDATE serial_numbers SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newStatus, $serialId]);

            // Log the status change
            $this->logEvent(
                $serialId,
                'status_change',
                $oldStatus,
                $newStatus,
                $options['notes'] ?? "Status changed from {$oldStatus} to {$newStatus}",
                $options['performed_by'] ?? null,
                $options
            );

            return true;

        } catch (Exception $e) {
            $this->logger->error('Failed to update status', [
                'serial_id' => $serialId,
                'new_status' => $newStatus,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Update location
     */
    public function updateLocation(int $serialId, string $newLocation, ?int $userId = null): bool
    {
        try {
            // Get current location
            $sql = "SELECT location FROM serial_numbers WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$serialId]);
            $oldLocation = $stmt->fetchColumn();

            // Update location
            $sql = "UPDATE serial_numbers SET location = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newLocation, $serialId]);

            // Log the location change
            $this->logEvent($serialId, 'location_change', $oldLocation, $newLocation,
                "Location changed from {$oldLocation} to {$newLocation}", $userId);

            return true;

        } catch (Exception $e) {
            $this->logger->error('Failed to update location', [
                'serial_id' => $serialId,
                'new_location' => $newLocation,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Mark as sold
     */
    public function markAsSold(int $serialId, int $transactionId, ?int $userId = null): bool
    {
        return $this->updateStatus($serialId, 'sold', [
            'transaction_id' => $transactionId,
            'performed_by' => $userId,
            'notes' => "Item sold via transaction #{$transactionId}"
        ]);
    }

    /**
     * Mark as rented
     */
    public function markAsRented(int $serialId, int $rentalId, ?int $userId = null): bool
    {
        return $this->updateStatus($serialId, 'rented', [
            'rental_id' => $rentalId,
            'performed_by' => $userId,
            'notes' => "Item rented via rental #{$rentalId}"
        ]);
    }

    /**
     * Mark as returned from rental
     */
    public function markAsReturned(int $serialId, int $rentalId, int $conditionRating, ?int $userId = null): bool
    {
        try {
            // Update status to available
            $this->updateStatus($serialId, 'available', [
                'rental_id' => $rentalId,
                'performed_by' => $userId,
                'notes' => "Item returned from rental #{$rentalId}"
            ]);

            // Update condition rating
            $sql = "UPDATE serial_numbers SET condition_rating = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$conditionRating, $serialId]);

            // Log return event
            $this->logEvent($serialId, 'returned', 'rented', 'available',
                "Item returned with condition rating {$conditionRating}/10", $userId, [
                    'rental_id' => $rentalId,
                    'condition_rating' => $conditionRating
                ]);

            return true;

        } catch (Exception $e) {
            $this->logger->error('Failed to mark as returned', [
                'serial_id' => $serialId,
                'rental_id' => $rentalId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Mark for service
     */
    public function markForService(int $serialId, int $workOrderId, ?int $userId = null): bool
    {
        return $this->updateStatus($serialId, 'service', [
            'work_order_id' => $workOrderId,
            'performed_by' => $userId,
            'notes' => "Item sent for service via work order #{$workOrderId}"
        ]);
    }

    /**
     * Complete service
     */
    public function completeService(int $serialId, int $workOrderId, ?int $userId = null): bool
    {
        try {
            // Update status to available
            $this->updateStatus($serialId, 'available', [
                'work_order_id' => $workOrderId,
                'performed_by' => $userId,
                'notes' => "Service completed via work order #{$workOrderId}"
            ]);

            // Update last service date and calculate next service due (180 days)
            $nextServiceDue = date('Y-m-d', strtotime('+180 days'));
            $sql = "UPDATE serial_numbers
                    SET last_service_date = CURDATE(),
                        next_service_due = ?,
                        updated_at = NOW()
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nextServiceDue, $serialId]);

            return true;

        } catch (Exception $e) {
            $this->logger->error('Failed to complete service', [
                'serial_id' => $serialId,
                'work_order_id' => $workOrderId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get items due for service
     */
    public function getItemsDueForService(): array
    {
        $sql = "SELECT sn.*, p.name as product_name, p.sku
                FROM serial_numbers sn
                JOIN products p ON sn.product_id = p.id
                WHERE sn.next_service_due <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND sn.status = 'available'
                ORDER BY sn.next_service_due";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get serial number history
     */
    public function getHistory(int $serialId, int $limit = 50): array
    {
        $sql = "SELECT snh.*, u.username as performed_by_name
                FROM serial_number_history snh
                LEFT JOIN users u ON snh.performed_by = u.id
                WHERE snh.serial_number_id = ?
                ORDER BY snh.event_date DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$serialId, $limit]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Log event in history
     */
    private function logEvent(
        int $serialId,
        string $eventType,
        ?string $oldStatus,
        ?string $newStatus,
        ?string $notes = null,
        ?int $userId = null,
        array $additionalData = []
    ): void {
        $sql = "INSERT INTO serial_number_history
                (serial_number_id, event_type, old_status, new_status, old_location, new_location,
                 transaction_id, rental_id, work_order_id, performed_by, notes, event_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $serialId,
            $eventType,
            $oldStatus,
            $newStatus,
            $additionalData['old_location'] ?? null,
            $additionalData['new_location'] ?? null,
            $additionalData['transaction_id'] ?? null,
            $additionalData['rental_id'] ?? null,
            $additionalData['work_order_id'] ?? null,
            $userId,
            $notes
        ]);
    }

    /**
     * Log barcode scan
     */
    private function logBarcodeToken(string $barcode, string $scanType, ?int $userId = null): int
    {
        $sql = "INSERT INTO barcode_scans
                (barcode, scan_type, scanned_by, scan_location, scanned_at)
                VALUES (?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $barcode,
            $scanType,
            $userId ?? $_SESSION['user_id'] ?? null,
            $_SESSION['scan_location'] ?? 'web'
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update barcode scan log with results
     */
    private function updateBarcodeScanLog(string $barcode, ?int $serialId, ?int $productId, string $result): void
    {
        $sql = "UPDATE barcode_scans
                SET serial_number_id = ?, product_id = ?, result = ?
                WHERE barcode = ?
                ORDER BY scanned_at DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$serialId, $productId, $result, $barcode]);
    }

    /**
     * Get scan statistics
     */
    public function getScanStatistics(int $days = 30): array
    {
        $sql = "SELECT
                    DATE(scanned_at) as scan_date,
                    scan_type,
                    result,
                    COUNT(*) as count
                FROM barcode_scans
                WHERE scanned_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(scanned_at), scan_type, result
                ORDER BY scan_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Generate barcode for product
     * Uses EAN-13 format
     */
    public function generateBarcode(int $productId): string
    {
        // Simple barcode generation - can be customized
        // Format: 200 (prefix) + 8-digit product ID + check digit
        $prefix = '200';
        $productCode = str_pad($productId, 8, '0', STR_PAD_LEFT);
        $code = $prefix . $productCode;

        // Calculate EAN-13 check digit
        $sum = 0;
        for ($i = 0; $i < 11; $i++) {
            $sum += (int)$code[$i] * (($i % 2 === 0) ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;

        return $code . $checkDigit;
    }
}
