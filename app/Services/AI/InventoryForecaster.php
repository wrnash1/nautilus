<?php

namespace App\Services\AI;

use Phpml\Regression\LeastSquares;
use Phpml\Math\Statistic\Mean;
use Phpml\Math\Statistic\StandardDeviation;

/**
 * Inventory Forecasting Engine
 * 
 * Predicts future product demand using historical sales data
 * Uses simple linear regression for MVP (can be enhanced later)
 */
class InventoryForecaster
{
    private SalesDataCollector $dataCollector;
    private int $tenantId;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->dataCollector = new SalesDataCollector($tenantId);
    }

    /**
     * Forecast demand for next N days
     * 
     * @param int $productId
     * @param int $daysAhead How many days to forecast
     * @return array [date => predicted_quantity]
     */
    public function forecastDemand(int $productId, int $daysAhead = 30): array
    {
        // Get historical sales
        $salesHistory = $this->dataCollector->getProductSalesHistory($productId, 90);
        
        if (count($salesHistory) < 7) {
            return [
                'error' => 'Insufficient data for forecasting',
                'min_days_required' => 7,
                'days_available' => count($salesHistory)
            ];
        }

        // Prepare data for regression
        $samples = [];
        $targets = [];
        $dayIndex = 0;

        foreach ($salesHistory as $date => $quantity) {
            $samples[] = [$dayIndex]; // X: day number
            $targets[] = $quantity;    // Y: quantity sold
            $dayIndex++;
        }

        // Train linear regression model
        $regression = new LeastSquares();
        $regression->train($samples, $targets);

        // Generate predictions
        $forecasts = [];
        $startDate = strtotime(array_key_last($salesHistory));

        for ($i = 1; $i <= $daysAhead; $i++) {
            $futureDay = $dayIndex + $i;
            $predictedQuantity = $regression->predict([$futureDay]);
            
            // Ensure non-negative predictions
            $predictedQuantity = max(0, round($predictedQuantity));
            
            $forecastDate = date('Y-m-d', strtotime("+$i days", $startDate));
            $forecasts[$forecastDate] = $predictedQuantity;
        }

        return $forecasts;
    }

    /**
     * Get comprehensive forecast with confidence intervals
     */
    public function getDetailedForecast(int $productId, int $daysAhead = 30): array
    {
        $salesHistory = $this->dataCollector->getProductSalesHistory($productId, 90);
        $forecasts = $this->forecastDemand($productId, $daysAhead);

        if (isset($forecasts['error'])) {
            return $forecasts;
        }

        // Calculate statistics
        $historicalValues = array_values($salesHistory);
        $avgSales = Mean::arithmetic($historicalValues);
        $stdDev = StandardDeviation::population($historicalValues);

        // Calculate confidence intervals (assuming normal distribution)
        $confidence95 = 1.96 * $stdDev;

        $detailedForecasts = [];
        foreach ($forecasts as $date => $quantity) {
            $detailedForecasts[] = [
                'date' => $date,
                'predicted_quantity' => $quantity,
                'lower_bound' => max(0, round($quantity - $confidence95)),
                'upper_bound' => round($quantity + $confidence95),
                'confidence_level' => 0.95
            ];
        }

        return [
            'product_id' => $productId,
            'forecasts' => $detailedForecasts,
            'statistics' => [
                'average_daily_sales' => round($avgSales, 2),
                'standard_deviation' => round($stdDev, 2),
                'historical_days' => count($salesHistory),
                'trend' => $this->dataCollector->detectTrend($productId)
            ]
        ];
    }

    /**
     * Calculate reorder point (when to restock)
     */
    public function calculateReorderPoint(int $productId, int $leadTimeDays = 7): array
    {
        $avgDailySales = $this->dataCollector->getAverageDailySales($productId, 30);
        $currentStock = $this->dataCollector->getCurrentInventory($productId);
       
        // Reorder point = (Average daily sales × Lead time) + Safety stock
        $safetyStock = ceil($avgDailySales * 3); // 3 days safety buffer
        $reorderPoint = ceil(($avgDailySales * $leadTimeDays) + $safetyStock);

        // Days until stockout
        $daysUntilStockout = $avgDailySales > 0 
            ? floor($currentStock / $avgDailySales) 
            : 999;

        $urgency = 'normal';
        if ($currentStock <= $reorderPoint) {
            $urgency = 'high';
        } elseif ($daysUntilStockout <= 14) {
            $urgency = 'medium';
        }

        return [
            'product_id' => $productId,
            'current_stock' => $currentStock,
            'reorder_point' => $reorderPoint,
            'should_reorder' => $currentStock <= $reorderPoint,
            'urgency' => $urgency,
            'days_until_stockout' => $daysUntilStockout,
            'recommended_order_quantity' => $this->calculateEOQ($productId, $avgDailySales),
            'metrics' => [
                'avg_daily_sales' => round($avgDailySales, 2),
                'lead_time_days' => $leadTimeDays,
                'safety_stock' => $safetyStock
            ]
        ];
    }

    /**
     * Economic Order Quantity (EOQ) calculation
     */
    private function calculateEOQ(int $productId, float $avgDailySales): int
    {
        // Simplified EOQ for MVP
        // Full EOQ formula: √((2 × D × S) / H)
        // D = annual demand, S = order cost, H = holding cost
        
        // For now, use a simple approach: order for 30 days
        $annualDemand = $avgDailySales * 365;
        $orderCost = 50; // Assumed fixed cost per order
        $holdingCost = 2; // Assumed cost to hold one unit per year

        if ($holdingCost == 0) {
            return ceil($avgDailySales * 30); // Fallback: 30 days supply
        }

        $eoq = sqrt((2 * $annualDemand * $orderCost) / $holdingCost);
        
        return max(1, ceil($eoq));
    }

    /**
     * Get inventory recommendations for all low-stock items
     */
    public function getInventoryRecommendations(int $limit = 20): array
    {
        $topProducts = $this->dataCollector->getTopSellingProducts($limit, 90);
        $recommendations = [];

        foreach ($topProducts as $product) {
            $reorderInfo = $this->calculateReorderPoint($product['id']);
            
            if ($reorderInfo['should_reorder']) {
                $recommendations[] = [
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'sku' => $product['sku'],
                    'current_stock' => $reorderInfo['current_stock'],
                    'reorder_point' => $reorderInfo['reorder_point'],
                    'urgency' => $reorderInfo['urgency'],
                    'recommended_quantity' => $reorderInfo['recommended_order_quantity'],
                    'days_until_stockout' => $reorderInfo['days_until_stockout']
                ];
            }
        }

        // Sort by urgency (high first)
        usort($recommendations, function($a, $b) {
            $urgencyOrder = ['high' => 0, 'medium' => 1, 'normal' => 2];
            return $urgencyOrder[$a['urgency']] - $urgencyOrder[$b['urgency']];
        });

        return $recommendations;
    }

    /**
     * Get seasonal insights
     */
    public function getSeasonalInsights(int $productId = null): array
    {
        $patterns = $this->dataCollector->getSeasonalPatterns($productId);
        
        // Group by month across years
        $monthlyAverages = [];
        $monthlyCounts = [];

        foreach ($patterns as $row) {
            $month = (int)$row['month'];
            $quantity = isset($row['quantity_sold']) ? (int)$row['quantity_sold'] : (int)$row['total_quantity'];
            
            if (!isset($monthlyAverages[$month])) {
                $monthlyAverages[$month] = 0;
                $monthlyCounts[$month] = 0;
            }
            
            $monthlyAverages[$month] += $quantity;
            $monthlyCounts[$month]++;
        }

        // Calculate averages
        $insights = [];
        $monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                       'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        foreach ($monthlyAverages as $month => $total) {
            $count = $monthlyCounts[$month];
            $avg = $count > 0 ? $total / $count : 0;
            
            $insights[] = [
                'month' => $month,
                'month_name' => $monthNames[$month],
                'average_sales' => round($avg, 2),
                'years_of_data' => $count
            ];
        }

        // Find peak month
        $maxSales = 0;
        $peakMonth = null;
        foreach ($insights as $insight) {
            if ($insight['average_sales'] > $maxSales) {
                $maxSales = $insight['average_sales'];
                $peakMonth = $insight['month_name'];
            }
        }

        return [
            'seasonal_data' => $insights,
            'peak_month' => $peakMonth,
            'peak_sales' => $maxSales
        ];
    }
}
