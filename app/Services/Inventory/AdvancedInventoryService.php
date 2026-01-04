<?php

namespace App\Services\Inventory;

use PDO;

/**
 * Advanced Inventory Management Service
 *
 * Handles reorder automation, stock forecasting, and inventory optimization
 */
class AdvancedInventoryService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \App\Core\Database::getPdo()->getConnection();
    }

    /**
     * Get products requiring reorder
     */
    public function getProductsNeedingReorder(): array
    {
        $stmt = $this->db->query(
            "SELECT p.*, pc.name as category_name, v.name as vendor_name,
                    p.stock_quantity,
                    p.low_stock_threshold,
                    (p.low_stock_threshold - p.stock_quantity) as shortage_quantity,
                    COALESCE(pr.suggested_reorder_quantity, p.low_stock_threshold * 2) as suggested_quantity,
                    COALESCE(pr.reorder_point, p.low_stock_threshold) as reorder_point,
                    pr.lead_time_days
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             LEFT JOIN vendors v ON p.vendor_id = v.id
             LEFT JOIN product_reorder_rules pr ON p.id = pr.product_id
             WHERE p.track_inventory = 1
             AND p.is_active = 1
             AND p.stock_quantity <= COALESCE(pr.reorder_point, p.low_stock_threshold)
             ORDER BY (p.low_stock_threshold - p.stock_quantity) DESC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate suggested reorder quantity based on sales velocity
     */
    public function calculateReorderQuantity(int $productId, int $days = 30): int
    {
        // Get average daily sales for the product
        $stmt = $this->db->prepare(
            "SELECT AVG(daily_quantity) as avg_daily_sales
             FROM (
                 SELECT DATE(it.created_at) as sale_date,
                        SUM(ABS(it.quantity_change)) as daily_quantity
                 FROM inventory_transactions it
                 WHERE it.product_id = ?
                 AND it.transaction_type = 'sale'
                 AND it.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                 GROUP BY DATE(it.created_at)
             ) daily_sales"
        );
        $stmt->execute([$productId, $days]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $avgDailySales = (float)($result['avg_daily_sales'] ?? 0);

        // Get lead time
        $stmt = $this->db->prepare(
            "SELECT lead_time_days, safety_stock_days
             FROM product_reorder_rules
             WHERE product_id = ?"
        );
        $stmt->execute([$productId]);
        $rule = $stmt->fetch(PDO::FETCH_ASSOC);

        $leadTimeDays = (int)($rule['lead_time_days'] ?? 7);
        $safetyStockDays = (int)($rule['safety_stock_days'] ?? 7);

        // Calculate: (Lead Time + Safety Stock) * Average Daily Sales
        $suggestedQuantity = ceil(($leadTimeDays + $safetyStockDays) * $avgDailySales);

        // Minimum reorder of 5 units
        return max($suggestedQuantity, 5);
    }

    /**
     * Create automatic purchase order for low stock items
     */
    public function createAutomaticPurchaseOrder(int $vendorId, array $productIds): ?int
    {
        try {
            $this->db->beginTransaction();

            // Generate PO number
            $poNumber = $this->generatePONumber();

            // Create purchase order
            $stmt = $this->db->prepare(
                "INSERT INTO purchase_orders
                 (vendor_id, po_number, order_date, status, created_by, created_at)
                 VALUES (?, ?, CURDATE(), 'draft', NULL, NOW())"
            );
            $stmt->execute([$vendorId, $poNumber]);
            $poId = (int)$this->db->lastInsertId();

            $subtotal = 0;

            // Add items to purchase order
            foreach ($productIds as $productId) {
                $stmt = $this->db->prepare(
                    "SELECT p.*, COALESCE(pr.suggested_reorder_quantity, p.low_stock_threshold * 2) as quantity
                     FROM products p
                     LEFT JOIN product_reorder_rules pr ON p.id = pr.product_id
                     WHERE p.id = ? AND p.vendor_id = ?"
                );
                $stmt->execute([$productId, $vendorId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) continue;

                $quantity = $this->calculateReorderQuantity($productId);
                $unitCost = (float)$product['cost_price'];
                $totalCost = $quantity * $unitCost;
                $subtotal += $totalCost;

                $stmt = $this->db->prepare(
                    "INSERT INTO purchase_order_items
                     (purchase_order_id, product_id, quantity_ordered, unit_cost, total_cost)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([$poId, $productId, $quantity, $unitCost, $totalCost]);
            }

            // Update PO totals
            $stmt = $this->db->prepare(
                "UPDATE purchase_orders SET subtotal = ?, total = ? WHERE id = ?"
            );
            $stmt->execute([$subtotal, $subtotal, $poId]);

            $this->db->commit();
            return $poId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Failed to create automatic PO: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Set reorder rules for a product
     */
    public function setReorderRule(int $productId, array $ruleData): bool
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO product_reorder_rules
                 (product_id, reorder_point, suggested_reorder_quantity, lead_time_days,
                  safety_stock_days, auto_reorder_enabled, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                     reorder_point = VALUES(reorder_point),
                     suggested_reorder_quantity = VALUES(suggested_reorder_quantity),
                     lead_time_days = VALUES(lead_time_days),
                     safety_stock_days = VALUES(safety_stock_days),
                     auto_reorder_enabled = VALUES(auto_reorder_enabled),
                     updated_at = NOW()"
            );

            return $stmt->execute([
                $productId,
                $ruleData['reorder_point'],
                $ruleData['suggested_reorder_quantity'],
                $ruleData['lead_time_days'] ?? 7,
                $ruleData['safety_stock_days'] ?? 7,
                $ruleData['auto_reorder_enabled'] ?? 0
            ]);
        } catch (\Exception $e) {
            error_log("Failed to set reorder rule: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get inventory turnover rate
     */
    public function getInventoryTurnover(int $productId, int $days = 90): float
    {
        // Total units sold
        $stmt = $this->db->prepare(
            "SELECT SUM(ABS(quantity_change)) as total_sold
             FROM inventory_transactions
             WHERE product_id = ?
             AND transaction_type = 'sale'
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $stmt->execute([$productId, $days]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalSold = (float)($result['total_sold'] ?? 0);

        // Average inventory
        $stmt = $this->db->prepare(
            "SELECT AVG(stock_quantity) as avg_stock
             FROM products
             WHERE id = ?"
        );
        $stmt->execute([$productId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $avgStock = (float)($result['avg_stock'] ?? 1);

        if ($avgStock == 0) return 0;

        // Turnover = Total Sold / Average Inventory
        return $totalSold / $avgStock;
    }

    /**
     * Get inventory valuation
     */
    public function getInventoryValuation(): array
    {
        $stmt = $this->db->query(
            "SELECT
                SUM(stock_quantity * cost_price) as total_cost_value,
                SUM(stock_quantity * retail_price) as total_retail_value,
                COUNT(*) as total_products,
                SUM(stock_quantity) as total_units
             FROM products
             WHERE track_inventory = 1 AND is_active = 1"
        );

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_cost_value' => (float)($result['total_cost_value'] ?? 0),
            'total_retail_value' => (float)($result['total_retail_value'] ?? 0),
            'total_products' => (int)($result['total_products'] ?? 0),
            'total_units' => (int)($result['total_units'] ?? 0),
            'potential_profit' => (float)($result['total_retail_value'] ?? 0) - (float)($result['total_cost_value'] ?? 0)
        ];
    }

    /**
     * Get slow-moving inventory
     */
    public function getSlowMovingInventory(int $days = 90, int $maxSales = 2): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, pc.name as category_name,
                    COALESCE(sales.total_sold, 0) as units_sold,
                    p.stock_quantity,
                    (p.stock_quantity * p.cost_price) as tied_up_capital
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             LEFT JOIN (
                 SELECT product_id, SUM(ABS(quantity_change)) as total_sold
                 FROM inventory_transactions
                 WHERE transaction_type = 'sale'
                 AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                 GROUP BY product_id
             ) sales ON p.id = sales.product_id
             WHERE p.track_inventory = 1
             AND p.is_active = 1
             AND p.stock_quantity > 0
             AND COALESCE(sales.total_sold, 0) <= ?
             ORDER BY tied_up_capital DESC"
        );
        $stmt->execute([$days, $maxSales]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get fast-moving inventory
     */
    public function getFastMovingInventory(int $days = 30, int $minSales = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, pc.name as category_name,
                    sales.total_sold as units_sold,
                    ROUND(sales.total_sold / ?, 2) as avg_daily_sales,
                    p.stock_quantity,
                    ROUND(p.stock_quantity / (sales.total_sold / ?), 0) as days_of_stock
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             INNER JOIN (
                 SELECT product_id, SUM(ABS(quantity_change)) as total_sold
                 FROM inventory_transactions
                 WHERE transaction_type = 'sale'
                 AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                 GROUP BY product_id
             ) sales ON p.id = sales.product_id
             WHERE p.track_inventory = 1
             AND p.is_active = 1
             AND sales.total_sold >= ?
             ORDER BY sales.total_sold DESC"
        );
        $stmt->execute([$days, $days, $days, $minSales]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Perform inventory audit/cycle count
     */
    public function recordCycleCount(int $productId, int $countedQuantity, string $notes = ''): bool
    {
        try {
            $this->db->beginTransaction();

            // Get current quantity
            $stmt = $this->db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new \Exception("Product not found");
            }

            $currentQuantity = (int)$product['stock_quantity'];
            $difference = $countedQuantity - $currentQuantity;

            if ($difference != 0) {
                // Update product quantity
                $stmt = $this->db->prepare(
                    "UPDATE products SET stock_quantity = ? WHERE id = ?"
                );
                $stmt->execute([$countedQuantity, $productId]);

                // Record adjustment transaction
                $stmt = $this->db->prepare(
                    "INSERT INTO inventory_transactions
                     (product_id, transaction_type, quantity_change, quantity_before,
                      quantity_after, notes, created_at)
                     VALUES (?, 'adjustment', ?, ?, ?, ?, NOW())"
                );
                $stmt->execute([
                    $productId,
                    $difference,
                    $currentQuantity,
                    $countedQuantity,
                    "Cycle count: " . $notes
                ]);
            }

            // Record cycle count
            $stmt = $this->db->prepare(
                "INSERT INTO inventory_cycle_counts
                 (product_id, expected_quantity, counted_quantity, difference, notes, counted_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $productId,
                $currentQuantity,
                $countedQuantity,
                $difference,
                $notes
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Failed to record cycle count: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get inventory forecasting data
     */
    public function getForecast(int $productId, int $forecastDays = 30): array
    {
        // Get historical sales data (last 90 days)
        $stmt = $this->db->prepare(
            "SELECT DATE(created_at) as date, SUM(ABS(quantity_change)) as quantity
             FROM inventory_transactions
             WHERE product_id = ?
             AND transaction_type = 'sale'
             AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
             GROUP BY DATE(created_at)
             ORDER BY date"
        );
        $stmt->execute([$productId]);
        $historicalData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate average daily sales
        $totalSales = array_sum(array_column($historicalData, 'quantity'));
        $daysWithSales = count($historicalData);
        $avgDailySales = $daysWithSales > 0 ? $totalSales / $daysWithSales : 0;

        // Get current stock
        $stmt = $this->db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentStock = (int)($product['stock_quantity'] ?? 0);

        // Calculate forecast
        $forecast = [];
        $remainingStock = $currentStock;

        for ($day = 1; $day <= $forecastDays; $day++) {
            $remainingStock -= $avgDailySales;

            $forecast[] = [
                'day' => $day,
                'date' => date('Y-m-d', strtotime("+$day days")),
                'projected_stock' => max(0, round($remainingStock)),
                'stockout_risk' => $remainingStock <= 0
            ];
        }

        return [
            'current_stock' => $currentStock,
            'avg_daily_sales' => round($avgDailySales, 2),
            'days_until_stockout' => $avgDailySales > 0 ? ceil($currentStock / $avgDailySales) : 999,
            'forecast' => $forecast
        ];
    }

    /**
     * Generate unique PO number
     */
    private function generatePONumber(): string
    {
        $prefix = 'PO';
        $date = date('Ymd');

        // Get last PO number for today
        $stmt = $this->db->prepare(
            "SELECT po_number FROM purchase_orders
             WHERE po_number LIKE ?
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$prefix . $date . '%']);
        $lastPO = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lastPO) {
            $lastSequence = (int)substr($lastPO['po_number'], -4);
            $sequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }

        return $prefix . $date . $sequence;
    }

    /**
     * Get inventory statistics
     */
    public function getStatistics(): array
    {
        $valuation = $this->getInventoryValuation();

        $stmt = $this->db->query(
            "SELECT COUNT(*) as count FROM products
             WHERE track_inventory = 1 AND is_active = 1
             AND stock_quantity <= low_stock_threshold"
        );
        $lowStock = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        $stmt = $this->db->query(
            "SELECT COUNT(*) as count FROM products
             WHERE track_inventory = 1 AND is_active = 1 AND stock_quantity = 0"
        );
        $outOfStock = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        return array_merge($valuation, [
            'low_stock_count' => $lowStock,
            'out_of_stock_count' => $outOfStock
        ]);
    }
}
