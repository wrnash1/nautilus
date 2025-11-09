<?php

namespace App\Services\Inventory;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;

/**
 * Stock Management Service
 *
 * Advanced inventory and stock management functionality
 */
class StockManagementService
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Perform stock count/audit
     */
    public function createStockCount(array $data): array
    {
        try {
            $tenantId = TenantMiddleware::getCurrentTenantId();

            // Create stock count record
            $stockCountId = TenantDatabase::insertTenant('stock_counts', [
                'count_date' => $data['count_date'] ?? date('Y-m-d'),
                'counted_by' => $data['counted_by'] ?? $_SESSION['user_id'],
                'status' => 'in_progress',
                'notes' => $data['notes'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'stock_count_id' => $stockCountId,
                'message' => 'Stock count created successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Create stock count failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Record individual product count
     */
    public function recordProductCount(int $stockCountId, int $productId, int $countedQuantity): array
    {
        try {
            // Get current system quantity
            $product = TenantDatabase::fetchOneTenant(
                "SELECT stock_quantity, name FROM products WHERE id = ?",
                [$productId]
            );

            if (!$product) {
                return ['success' => false, 'error' => 'Product not found'];
            }

            $variance = $countedQuantity - $product['stock_quantity'];

            // Record the count
            TenantDatabase::insertTenant('stock_count_items', [
                'stock_count_id' => $stockCountId,
                'product_id' => $productId,
                'system_quantity' => $product['stock_quantity'],
                'counted_quantity' => $countedQuantity,
                'variance' => $variance,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'product_name' => $product['name'],
                'system_quantity' => $product['stock_quantity'],
                'counted_quantity' => $countedQuantity,
                'variance' => $variance
            ];

        } catch (\Exception $e) {
            $this->logger->error('Record product count failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Complete stock count and apply adjustments
     */
    public function completeStockCount(int $stockCountId, bool $applyAdjustments = true): array
    {
        try {
            // Get all items in the count
            $items = TenantDatabase::fetchAllTenant(
                "SELECT * FROM stock_count_items WHERE stock_count_id = ?",
                [$stockCountId]
            );

            $adjustmentsMade = 0;

            if ($applyAdjustments) {
                foreach ($items as $item) {
                    if ($item['variance'] != 0) {
                        // Update product stock
                        TenantDatabase::updateTenant('products', [
                            'stock_quantity' => $item['counted_quantity'],
                            'updated_at' => date('Y-m-d H:i:s')
                        ], 'id = ?', [$item['product_id']]);

                        // Log the adjustment
                        TenantDatabase::insertTenant('inventory_adjustments', [
                            'product_id' => $item['product_id'],
                            'adjustment_type' => 'stock_count',
                            'quantity_change' => $item['variance'],
                            'quantity_before' => $item['system_quantity'],
                            'quantity_after' => $item['counted_quantity'],
                            'reason' => 'Stock count adjustment - Count ID: ' . $stockCountId,
                            'adjusted_by' => $_SESSION['user_id'] ?? null,
                            'adjusted_at' => date('Y-m-d H:i:s')
                        ]);

                        $adjustmentsMade++;
                    }
                }
            }

            // Mark stock count as complete
            TenantDatabase::updateTenant('stock_counts', [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$stockCountId]);

            return [
                'success' => true,
                'items_counted' => count($items),
                'adjustments_made' => $adjustmentsMade,
                'message' => 'Stock count completed successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Complete stock count failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Transfer stock between locations
     */
    public function transferStock(array $data): array
    {
        try {
            // Validate required fields
            if (empty($data['product_id']) || empty($data['from_location']) ||
                empty($data['to_location']) || empty($data['quantity'])) {
                return ['success' => false, 'error' => 'Missing required fields'];
            }

            // Create transfer record
            $transferId = TenantDatabase::insertTenant('stock_transfers', [
                'product_id' => $data['product_id'],
                'from_location' => $data['from_location'],
                'to_location' => $data['to_location'],
                'quantity' => $data['quantity'],
                'transfer_date' => $data['transfer_date'] ?? date('Y-m-d'),
                'transferred_by' => $data['transferred_by'] ?? $_SESSION['user_id'],
                'notes' => $data['notes'] ?? null,
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Log the transfer as inventory adjustments
            TenantDatabase::insertTenant('inventory_adjustments', [
                'product_id' => $data['product_id'],
                'adjustment_type' => 'transfer_out',
                'quantity_change' => -$data['quantity'],
                'reason' => "Transfer to {$data['to_location']} - Transfer ID: {$transferId}",
                'adjusted_by' => $_SESSION['user_id'] ?? null,
                'adjusted_at' => date('Y-m-d H:i:s')
            ]);

            TenantDatabase::insertTenant('inventory_adjustments', [
                'product_id' => $data['product_id'],
                'adjustment_type' => 'transfer_in',
                'quantity_change' => $data['quantity'],
                'reason' => "Transfer from {$data['from_location']} - Transfer ID: {$transferId}",
                'adjusted_by' => $_SESSION['user_id'] ?? null,
                'adjusted_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'transfer_id' => $transferId,
                'message' => 'Stock transferred successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Stock transfer failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get reorder suggestions based on sales velocity
     */
    public function getReorderSuggestions(int $days = 30): array
    {
        try {
            $startDate = date('Y-m-d', strtotime("-{$days} days"));
            $endDate = date('Y-m-d');

            $suggestions = TenantDatabase::fetchAllTenant(
                "SELECT
                    p.id,
                    p.sku,
                    p.name,
                    p.stock_quantity,
                    p.low_stock_threshold,
                    p.cost,
                    COALESCE(SUM(ti.quantity), 0) as units_sold_period,
                    COALESCE(SUM(ti.quantity) / ?, 0) as daily_velocity,
                    CEIL(? * (COALESCE(SUM(ti.quantity) / ?, 0))) as suggested_reorder_qty,
                    CEIL(? * (COALESCE(SUM(ti.quantity) / ?, 0)) * p.cost) as estimated_cost
                FROM products p
                LEFT JOIN pos_transaction_items ti ON p.id = ti.product_id
                LEFT JOIN pos_transactions t ON ti.transaction_id = t.id
                    AND t.transaction_date BETWEEN ? AND ?
                    AND t.status = 'completed'
                WHERE p.is_active = 1
                AND p.stock_quantity <= p.low_stock_threshold
                GROUP BY p.id
                HAVING daily_velocity > 0
                ORDER BY daily_velocity DESC",
                [$days, $days, $days, $days, $days, $startDate, $endDate]
            );

            $totalEstimatedCost = array_sum(array_column($suggestions, 'estimated_cost'));

            return [
                'success' => true,
                'analysis_period_days' => $days,
                'suggestions_count' => count($suggestions),
                'total_estimated_cost' => round($totalEstimatedCost, 2),
                'suggestions' => $suggestions
            ];

        } catch (\Exception $e) {
            $this->logger->error('Reorder suggestions failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get slow-moving stock report
     */
    public function getSlowMovingStock(int $days = 90): array
    {
        try {
            $startDate = date('Y-m-d', strtotime("-{$days} days"));
            $endDate = date('Y-m-d');

            $slowMovers = TenantDatabase::fetchAllTenant(
                "SELECT
                    p.id,
                    p.sku,
                    p.name,
                    p.stock_quantity,
                    p.cost,
                    (p.stock_quantity * p.cost) as inventory_value,
                    COALESCE(SUM(ti.quantity), 0) as units_sold,
                    DATEDIFF(NOW(), MAX(t.transaction_date)) as days_since_last_sale
                FROM products p
                LEFT JOIN pos_transaction_items ti ON p.id = ti.product_id
                LEFT JOIN pos_transactions t ON ti.transaction_id = t.id
                    AND t.transaction_date BETWEEN ? AND ?
                    AND t.status = 'completed'
                WHERE p.is_active = 1
                AND p.stock_quantity > 0
                GROUP BY p.id
                HAVING units_sold < 3 OR days_since_last_sale > ?
                ORDER BY inventory_value DESC",
                [$startDate, $endDate, $days]
            );

            $totalInventoryValue = array_sum(array_column($slowMovers, 'inventory_value'));

            return [
                'success' => true,
                'analysis_period_days' => $days,
                'slow_moving_items' => count($slowMovers),
                'total_inventory_value' => round($totalInventoryValue, 2),
                'items' => $slowMovers
            ];

        } catch (\Exception $e) {
            $this->logger->error('Slow moving stock failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Forecast stock requirements
     */
    public function forecastStockRequirements(int $productId, int $forecastDays = 30): array
    {
        try {
            $historicalDays = 90;
            $startDate = date('Y-m-d', strtotime("-{$historicalDays} days"));
            $endDate = date('Y-m-d');

            // Get sales history
            $salesData = TenantDatabase::fetchOneTenant(
                "SELECT
                    p.id,
                    p.name,
                    p.sku,
                    p.stock_quantity,
                    p.low_stock_threshold,
                    COALESCE(SUM(ti.quantity), 0) as total_sold,
                    COALESCE(SUM(ti.quantity) / ?, 0) as daily_average
                FROM products p
                LEFT JOIN pos_transaction_items ti ON p.id = ti.product_id
                LEFT JOIN pos_transactions t ON ti.transaction_id = t.id
                    AND t.transaction_date BETWEEN ? AND ?
                    AND t.status = 'completed'
                WHERE p.id = ?
                GROUP BY p.id",
                [$historicalDays, $startDate, $endDate, $productId]
            );

            if (!$salesData) {
                return ['success' => false, 'error' => 'Product not found'];
            }

            $forecastedNeed = ceil($salesData['daily_average'] * $forecastDays);
            $currentStock = $salesData['stock_quantity'];
            $daysUntilStockout = $salesData['daily_average'] > 0
                ? floor($currentStock / $salesData['daily_average'])
                : 999;

            $reorderNeeded = max(0, $forecastedNeed - $currentStock);

            return [
                'success' => true,
                'product' => [
                    'id' => $salesData['id'],
                    'name' => $salesData['name'],
                    'sku' => $salesData['sku']
                ],
                'current_stock' => $currentStock,
                'analysis' => [
                    'historical_period_days' => $historicalDays,
                    'total_sold' => $salesData['total_sold'],
                    'daily_average_sales' => round($salesData['daily_average'], 2),
                    'forecast_period_days' => $forecastDays,
                    'forecasted_demand' => $forecastedNeed,
                    'days_until_stockout' => $daysUntilStockout,
                    'reorder_recommended' => $reorderNeeded > 0,
                    'suggested_reorder_quantity' => $reorderNeeded
                ]
            ];

        } catch (\Exception $e) {
            $this->logger->error('Stock forecast failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get inventory turnover report
     */
    public function getInventoryTurnover(string $startDate, string $endDate): array
    {
        try {
            $turnover = TenantDatabase::fetchAllTenant(
                "SELECT
                    p.id,
                    p.sku,
                    p.name,
                    pc.name as category,
                    p.stock_quantity,
                    (p.stock_quantity * p.cost) as current_inventory_value,
                    COALESCE(SUM(ti.quantity), 0) as units_sold,
                    COALESCE(SUM(ti.quantity * p.cost), 0) as cogs,
                    CASE
                        WHEN (p.stock_quantity * p.cost) > 0 THEN
                            COALESCE(SUM(ti.quantity * p.cost), 0) / (p.stock_quantity * p.cost)
                        ELSE 0
                    END as turnover_ratio
                FROM products p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                LEFT JOIN pos_transaction_items ti ON p.id = ti.product_id
                LEFT JOIN pos_transactions t ON ti.transaction_id = t.id
                    AND t.transaction_date BETWEEN ? AND ?
                    AND t.status = 'completed'
                WHERE p.is_active = 1
                GROUP BY p.id
                ORDER BY turnover_ratio DESC",
                [$startDate, $endDate]
            );

            return [
                'success' => true,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'items' => $turnover
            ];

        } catch (\Exception $e) {
            $this->logger->error('Inventory turnover failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
