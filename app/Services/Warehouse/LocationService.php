<?php

namespace App\Services\Warehouse;

use PDO;

/**
 * Multi-Location/Warehouse Service
 *
 * Manages multiple store locations and warehouse inventory
 */
class LocationService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \App\Core\Database::getPdo()->getConnection();
    }

    /**
     * Get all locations
     */
    public function getAllLocations(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM locations WHERE is_active = 1 ORDER BY name"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get location details
     */
    public function getLocation(int $locationId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM locations WHERE id = ?"
        );
        $stmt->execute([$locationId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get inventory at location
     */
    public function getLocationInventory(int $locationId, ?int $productId = null): array
    {
        if ($productId) {
            $stmt = $this->db->prepare(
                "SELECT li.*, p.name as product_name, p.sku, p.cost_price, p.retail_price
                 FROM location_inventory li
                 JOIN products p ON li.product_id = p.id
                 WHERE li.location_id = ? AND li.product_id = ?"
            );
            $stmt->execute([$locationId, $productId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT li.*, p.name as product_name, p.sku, p.cost_price, p.retail_price,
                        pc.name as category_name
                 FROM location_inventory li
                 JOIN products p ON li.product_id = p.id
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 WHERE li.location_id = ?
                 ORDER BY p.name"
            );
            $stmt->execute([$locationId]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get product availability across all locations
     */
    public function getProductAvailability(int $productId): array
    {
        $stmt = $this->db->prepare(
            "SELECT l.id, l.name, l.code, l.location_type,
                    COALESCE(li.quantity, 0) as quantity,
                    COALESCE(li.reserved_quantity, 0) as reserved,
                    COALESCE(li.available_quantity, 0) as available
             FROM locations l
             LEFT JOIN location_inventory li ON l.id = li.location_id AND li.product_id = ?
             WHERE l.is_active = 1
             ORDER BY l.name"
        );
        $stmt->execute([$productId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Transfer inventory between locations
     */
    public function transferInventory(
        int $productId,
        int $fromLocationId,
        int $toLocationId,
        int $quantity,
        string $notes = ''
    ): bool {
        try {
            $this->db->beginTransaction();

            // Check source inventory
            $sourceInventory = $this->getLocationInventory($fromLocationId, $productId);
            if (empty($sourceInventory) || $sourceInventory[0]['available_quantity'] < $quantity) {
                throw new \Exception("Insufficient inventory at source location");
            }

            // Deduct from source
            $stmt = $this->db->prepare(
                "UPDATE location_inventory
                 SET quantity = quantity - ?,
                     available_quantity = available_quantity - ?,
                     updated_at = NOW()
                 WHERE location_id = ? AND product_id = ?"
            );
            $stmt->execute([$quantity, $quantity, $fromLocationId, $productId]);

            // Add to destination (create if doesn't exist)
            $stmt = $this->db->prepare(
                "INSERT INTO location_inventory
                 (location_id, product_id, quantity, available_quantity, updated_at)
                 VALUES (?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                     quantity = quantity + VALUES(quantity),
                     available_quantity = available_quantity + VALUES(available_quantity),
                     updated_at = NOW()"
            );
            $stmt->execute([$toLocationId, $productId, $quantity, $quantity]);

            // Record transfer
            $stmt = $this->db->prepare(
                "INSERT INTO inventory_transfers
                 (product_id, from_location_id, to_location_id, quantity, notes, status, created_at)
                 VALUES (?, ?, ?, ?, ?, 'completed', NOW())"
            );
            $stmt->execute([$productId, $fromLocationId, $toLocationId, $quantity, $notes]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Transfer failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adjust inventory at location
     */
    public function adjustInventory(
        int $locationId,
        int $productId,
        int $newQuantity,
        string $reason = ''
    ): bool {
        try {
            $this->db->beginTransaction();

            // Get current quantity
            $current = $this->getLocationInventory($locationId, $productId);
            $currentQty = !empty($current) ? (int)$current[0]['quantity'] : 0;
            $difference = $newQuantity - $currentQty;

            // Update inventory
            $stmt = $this->db->prepare(
                "INSERT INTO location_inventory
                 (location_id, product_id, quantity, available_quantity, updated_at)
                 VALUES (?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                     quantity = VALUES(quantity),
                     available_quantity = available_quantity + ?,
                     updated_at = NOW()"
            );
            $stmt->execute([$locationId, $productId, $newQuantity, $newQuantity, $difference]);

            // Record adjustment
            $stmt = $this->db->prepare(
                "INSERT INTO location_inventory_adjustments
                 (location_id, product_id, quantity_before, quantity_after, adjustment, reason, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $locationId,
                $productId,
                $currentQty,
                $newQuantity,
                $difference,
                $reason
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Adjustment failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reserve inventory for order
     */
    public function reserveInventory(int $locationId, int $productId, int $quantity): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE location_inventory
                 SET reserved_quantity = reserved_quantity + ?,
                     available_quantity = available_quantity - ?
                 WHERE location_id = ? AND product_id = ?
                 AND available_quantity >= ?"
            );
            $stmt->execute([$quantity, $quantity, $locationId, $productId, $quantity]);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Reserve failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Release reserved inventory
     */
    public function releaseReservation(int $locationId, int $productId, int $quantity): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE location_inventory
                 SET reserved_quantity = reserved_quantity - ?,
                     available_quantity = available_quantity + ?
                 WHERE location_id = ? AND product_id = ?"
            );
            $stmt->execute([$quantity, $quantity, $locationId, $productId]);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Release failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get transfer history
     */
    public function getTransferHistory(int $limit = 50, ?int $locationId = null): array
    {
        if ($locationId) {
            $stmt = $this->db->prepare(
                "SELECT it.*, p.name as product_name, p.sku,
                        l1.name as from_location, l2.name as to_location
                 FROM inventory_transfers it
                 JOIN products p ON it.product_id = p.id
                 JOIN locations l1 ON it.from_location_id = l1.id
                 JOIN locations l2 ON it.to_location_id = l2.id
                 WHERE it.from_location_id = ? OR it.to_location_id = ?
                 ORDER BY it.created_at DESC
                 LIMIT ?"
            );
            $stmt->execute([$locationId, $locationId, $limit]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT it.*, p.name as product_name, p.sku,
                        l1.name as from_location, l2.name as to_location
                 FROM inventory_transfers it
                 JOIN products p ON it.product_id = p.id
                 JOIN locations l1 ON it.from_location_id = l1.id
                 JOIN locations l2 ON it.to_location_id = l2.id
                 ORDER BY it.created_at DESC
                 LIMIT ?"
            );
            $stmt->execute([$limit]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get location statistics
     */
    public function getLocationStatistics(int $locationId): array
    {
        // Total inventory value
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(DISTINCT li.product_id) as unique_products,
                SUM(li.quantity) as total_units,
                SUM(li.quantity * p.cost_price) as total_cost_value,
                SUM(li.quantity * p.retail_price) as total_retail_value,
                SUM(li.reserved_quantity) as total_reserved,
                SUM(li.available_quantity) as total_available
             FROM location_inventory li
             JOIN products p ON li.product_id = p.id
             WHERE li.location_id = ?"
        );
        $stmt->execute([$locationId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Low stock items
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count
             FROM location_inventory li
             JOIN products p ON li.product_id = p.id
             WHERE li.location_id = ?
             AND li.quantity <= p.low_stock_threshold"
        );
        $stmt->execute([$locationId]);
        $lowStock = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        return [
            'unique_products' => (int)($stats['unique_products'] ?? 0),
            'total_units' => (int)($stats['total_units'] ?? 0),
            'total_cost_value' => (float)($stats['total_cost_value'] ?? 0),
            'total_retail_value' => (float)($stats['total_retail_value'] ?? 0),
            'total_reserved' => (int)($stats['total_reserved'] ?? 0),
            'total_available' => (int)($stats['total_available'] ?? 0),
            'low_stock_count' => $lowStock
        ];
    }

    /**
     * Get consolidated inventory across all locations
     */
    public function getConsolidatedInventory(): array
    {
        $stmt = $this->db->query(
            "SELECT p.id, p.name, p.sku,
                    SUM(li.quantity) as total_quantity,
                    SUM(li.reserved_quantity) as total_reserved,
                    SUM(li.available_quantity) as total_available,
                    COUNT(DISTINCT li.location_id) as locations_count
             FROM products p
             LEFT JOIN location_inventory li ON p.id = li.product_id
             WHERE p.is_active = 1
             GROUP BY p.id, p.name, p.sku
             ORDER BY p.name"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find optimal location for fulfillment
     */
    public function findOptimalLocation(int $productId, int $quantity): ?int
    {
        // Find location with sufficient stock closest to main store
        $stmt = $this->db->prepare(
            "SELECT li.location_id, l.name, li.available_quantity
             FROM location_inventory li
             JOIN locations l ON li.location_id = l.id
             WHERE li.product_id = ?
             AND li.available_quantity >= ?
             AND l.is_active = 1
             ORDER BY l.location_type DESC, li.available_quantity DESC
             LIMIT 1"
        );
        $stmt->execute([$productId, $quantity]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['location_id'] : null;
    }

    /**
     * Get inventory alerts
     */
    public function getInventoryAlerts(): array
    {
        $alerts = [];

        // Low stock alerts
        $stmt = $this->db->query(
            "SELECT l.name as location, p.name as product, p.sku,
                    li.quantity, p.low_stock_threshold
             FROM location_inventory li
             JOIN products p ON li.product_id = p.id
             JOIN locations l ON li.location_id = l.id
             WHERE li.quantity <= p.low_stock_threshold
             AND l.is_active = 1
             ORDER BY l.name, p.name"
        );

        $lowStock = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($lowStock as $item) {
            $alerts[] = [
                'type' => 'low_stock',
                'severity' => 'warning',
                'location' => $item['location'],
                'message' => "{$item['product']} is low at {$item['location']} ({$item['quantity']} units)"
            ];
        }

        // Out of stock alerts
        $stmt = $this->db->query(
            "SELECT l.name as location, p.name as product, p.sku
             FROM products p
             CROSS JOIN locations l
             LEFT JOIN location_inventory li ON p.id = li.product_id AND l.id = li.location_id
             WHERE (li.quantity IS NULL OR li.quantity = 0)
             AND p.is_active = 1 AND l.is_active = 1
             LIMIT 20"
        );

        $outOfStock = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($outOfStock as $item) {
            $alerts[] = [
                'type' => 'out_of_stock',
                'severity' => 'error',
                'location' => $item['location'],
                'message' => "{$item['product']} is out of stock at {$item['location']}"
            ];
        }

        return $alerts;
    }
}
