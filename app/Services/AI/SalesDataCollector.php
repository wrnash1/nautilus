<?php

namespace App\Services\AI;

use App\Core\TenantDatabase;
use DateTime;

/**
 * Sales Data Collector for AI Training
 * 
 * Collects and prepares historical sales data for machine learning
 * Ensures multi-tenant data isolation
 */
class SalesDataCollector
{
    private int $tenantId;
    private TenantDatabase $db;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->db = TenantDatabase::getInstance();
    }

    /**
     * Get sales history for a product
     * 
     * @param int $productId
     * @param int $daysBack How many days of history to retrieve
     * @return array Array of [date => quantity_sold]
     */
    public function getProductSalesHistory(int $productId, int $daysBack = 365): array
    {
        $sql = "
            SELECT 
                DATE(t.created_at) as sale_date,
                SUM(ti.quantity) as quantity_sold
            FROM transactions t
            JOIN transaction_items ti ON t.id = ti.transaction_id
            WHERE ti.product_id = ?
                AND t.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND t.status = 'completed'
            GROUP BY DATE(t.created_at)
            ORDER BY sale_date ASC
        ";

        $results = $this->db->fetchAll($sql, [$productId, $daysBack]);
        
        // Convert to [date => quantity] format
        $salesData = [];
        foreach ($results as $row) {
            $salesData[$row['sale_date']] = (int)$row['quantity_sold'];
        }

        return $salesData;
    }

    /**
     * Get sales by category
     */
    public function getCategorySalesHistory(int $categoryId, int $daysBack = 365): array
    {
        $sql = "
            SELECT 
                DATE(t.created_at) as sale_date,
                SUM(ti.quantity) as quantity_sold,
                SUM(ti.quantity * ti.price) as revenue
            FROM transactions t
            JOIN transaction_items ti ON t.id = ti.transaction_id
            JOIN products p ON ti.product_id = p.id
            WHERE p.category_id = ?
                AND t.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND t.status = 'completed'
            GROUP BY DATE(t.created_at)
            ORDER BY sale_date ASC
        ";

        return $this->db->fetchAll($sql, [$categoryId, $daysBack]);
    }

    /**
     * Get top selling products
     */
    public function getTopSellingProducts(int $limit = 20, int $daysBack = 90): array
    {
        $sql = "
            SELECT 
                p.id,
                p.name,
                p.sku,
                SUM(ti.quantity) as total_sold,
                COUNT(DISTINCT DATE(t.created_at)) as days_sold,
                SUM(ti.quantity * ti.price) as total_revenue
            FROM products p
            JOIN transaction_items ti ON p.id = ti.product_id
            JOIN transactions t ON ti.transaction_id = t.id
            WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND t.status = 'completed'
            GROUP BY p.id, p.name, p.sku
            ORDER BY total_sold DESC
            LIMIT ?
        ";

        return $this->db->fetchAll($sql, [$daysBack, $limit]);
    }

    /**
     * Get seasonal patterns (sales by month)
     */
    public function getSeasonalPatterns(int $productId = null): array
    {
        if ($productId) {
            $sql = "
                SELECT 
                    MONTH(t.created_at) as month,
                    YEAR(t.created_at) as year,
                    SUM(ti.quantity) as quantity_sold
                FROM transactions t
                JOIN transaction_items ti ON t.id = ti.transaction_id
                WHERE ti.product_id = ?
                    AND t.status = 'completed'
                GROUP BY YEAR(t.created_at), MONTH(t.created_at)
                ORDER BY year, month
            ";
            return $this->db->fetchAll($sql, [$productId]);
        } else {
            // All products
            $sql = "
                SELECT 
                    MONTH(t.created_at) as month,
                    YEAR(t.created_at) as year,
                    COUNT(DISTINCT ti.product_id) as products_sold,
                    SUM(ti.quantity) as total_quantity,
                    SUM(ti.quantity * ti.price) as total_revenue
                FROM transactions t
                JOIN transaction_items ti ON t.id = ti.transaction_id
                WHERE t.status = 'completed'
                GROUP BY YEAR(t.created_at), MONTH(t.created_at)
                ORDER BY year, month
            ";
            return $this->db->fetchAll($sql);
        }
    }

    /**
     * Get current inventory level for product
     */
    public function getCurrentInventory(int $productId): int
    {
        $sql = "SELECT stock_quantity FROM products WHERE id = ? LIMIT 1";
        $result = $this->db->fetchOne($sql, [$productId]);
        return $result ? (int)$result['stock_quantity'] : 0;
    }

    /**
     * Calculate average daily sales
     */
    public function getAverageDailySales(int $productId, int $daysBack = 30): float
    {
        $salesHistory = $this->getProductSalesHistory($productId, $daysBack);
        
        if (empty($salesHistory)) {
            return 0.0;
        }

        $totalSold = array_sum($salesHistory);
        $daysCounted = count($salesHistory);
        
        return $daysCounted > 0 ? $totalSold / $daysCounted : 0.0;
    }

    /**
     * Detect trends (increasing/decreasing/stable)
     */
    public function detectTrend(int $productId, int $daysBack = 90): string
    {
        $salesHistory = $this->getProductSalesHistory($productId, $daysBack);
        
        if (count($salesHistory) < 7) {
            return 'insufficient_data';
        }

        // Compare recent vs older sales
        $recentDays = array_slice($salesHistory, -30, 30, true);
        $olderDays = array_slice($salesHistory, 0, 30, true);

        $recentAvg = count($recentDays) > 0 ? array_sum($recentDays) / count($recentDays) : 0;
        $olderAvg = count($olderDays) > 0 ? array_sum($olderDays) / count($olderDays) : 0;

        if ($olderAvg == 0) {
            return 'new_product';
        }

        $change = (($recentAvg - $olderAvg) / $olderAvg) * 100;

        if ($change > 20) {
            return 'increasing';
        } elseif ($change < -20) {
            return 'decreasing';
        } else {
            return 'stable';
        }
    }

    /**
     * Export training data for ML model
     * Returns [features, labels] format
     */
    public function exportTrainingData(int $productId): array
    {
        $salesHistory = $this->getProductSalesHistory($productId, 365);
        
        $features = [];
        $labels = [];

        // Create sliding window: use past 7 days to predict next day
        $dates = array_keys($salesHistory);
        $windowSize = 7;

        for ($i = $windowSize; $i < count($dates); $i++) {
            $window = [];
            
            // Get previous 7 days as features
            for ($j = $i - $windowSize; $j < $i; $j++) {
                $window[] = $salesHistory[$dates[$j]] ?? 0;
            }
            
            $features[] = $window;
            $labels[] = $salesHistory[$dates[$i]]; // Next day is the label
        }

        return [
            'features' => $features,
            'labels' => $labels,
            'product_id' => $productId
        ];
    }
}
