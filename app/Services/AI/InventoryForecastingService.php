<?php

namespace App\Services\AI;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;

/**
 * AI-Powered Inventory Forecasting Service
 *
 * Uses machine learning algorithms to predict inventory needs
 */
class InventoryForecastingService
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Generate demand forecast for product using linear regression
     */
    public function forecastDemand(int $productId, int $forecastDays = 30): array
    {
        try {
            // Get historical sales data (last 90 days)
            $historicalDays = 90;
            $salesData = $this->getHistoricalSales($productId, $historicalDays);

            if (count($salesData) < 7) {
                return [
                    'success' => false,
                    'error' => 'Insufficient historical data for forecasting (minimum 7 days required)',
                    'data_points' => count($salesData)
                ];
            }

            // Prepare data for analysis
            $dataPoints = $this->prepareDat aPoints($salesData);

            // Calculate trend using linear regression
            $trend = $this->calculateLinearRegression($dataPoints);

            // Detect seasonality
            $seasonality = $this->detectSeasonality($dataPoints);

            // Generate forecast
            $forecast = $this->generateForecast($trend, $seasonality, $forecastDays);

            // Calculate confidence intervals
            $confidence = $this->calculateConfidenceIntervals($dataPoints, $forecast);

            // Get product details
            $product = TenantDatabase::fetchOneTenant(
                "SELECT id, name, sku, stock_quantity, low_stock_threshold, cost, price
                 FROM products WHERE id = ?",
                [$productId]
            );

            // Calculate reorder recommendations
            $recommendations = $this->calculateReorderRecommendations(
                $product,
                $forecast,
                $confidence
            );

            return [
                'success' => true,
                'product' => $product,
                'historical_data' => [
                    'days_analyzed' => count($salesData),
                    'total_units_sold' => array_sum(array_column($salesData, 'quantity')),
                    'average_daily_sales' => $trend['average'],
                    'trend_direction' => $trend['slope'] > 0 ? 'increasing' : ($trend['slope'] < 0 ? 'decreasing' : 'stable')
                ],
                'forecast' => [
                    'forecast_days' => $forecastDays,
                    'predicted_demand' => $forecast['total_demand'],
                    'daily_predictions' => $forecast['daily'],
                    'trend_slope' => round($trend['slope'], 4),
                    'seasonality_detected' => $seasonality['detected'],
                    'seasonality_pattern' => $seasonality['pattern']
                ],
                'confidence' => [
                    'level' => $confidence['level'],
                    'lower_bound' => $confidence['lower'],
                    'upper_bound' => $confidence['upper'],
                    'accuracy_score' => $confidence['accuracy']
                ],
                'recommendations' => $recommendations
            ];

        } catch (\Exception $e) {
            $this->logger->error('Demand forecast failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get historical sales data
     */
    private function getHistoricalSales(int $productId, int $days): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        return TenantDatabase::fetchAllTenant(
            "SELECT
                DATE(t.transaction_date) as date,
                SUM(ti.quantity) as quantity,
                COUNT(DISTINCT t.id) as transaction_count,
                AVG(ti.price) as avg_price
             FROM pos_transaction_items ti
             JOIN pos_transactions t ON ti.transaction_id = t.id
             WHERE ti.product_id = ?
             AND t.transaction_date >= ?
             AND t.status = 'completed'
             GROUP BY DATE(t.transaction_date)
             ORDER BY date",
            [$productId, $startDate]
        ) ?? [];
    }

    /**
     * Prepare data points for analysis
     */
    private function prepareDataPoints(array $salesData): array
    {
        $points = [];
        $dayIndex = 0;

        foreach ($salesData as $sale) {
            $points[] = [
                'x' => $dayIndex,
                'y' => (float)$sale['quantity'],
                'date' => $sale['date']
            ];
            $dayIndex++;
        }

        return $points;
    }

    /**
     * Calculate linear regression for trend analysis
     */
    private function calculateLinearRegression(array $dataPoints): array
    {
        $n = count($dataPoints);
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($dataPoints as $point) {
            $sumX += $point['x'];
            $sumY += $point['y'];
            $sumXY += $point['x'] * $point['y'];
            $sumX2 += $point['x'] * $point['x'];
        }

        // Calculate slope (m) and intercept (b) for y = mx + b
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        $average = $sumY / $n;

        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'average' => $average
        ];
    }

    /**
     * Detect seasonality patterns
     */
    private function detectSeasonality(array $dataPoints): array
    {
        if (count($dataPoints) < 14) {
            return ['detected' => false, 'pattern' => null];
        }

        // Check for weekly patterns
        $weeklyPattern = $this->checkWeeklyPattern($dataPoints);

        return [
            'detected' => $weeklyPattern['detected'],
            'pattern' => $weeklyPattern['detected'] ? 'weekly' : null,
            'peak_day' => $weeklyPattern['peak_day'] ?? null,
            'variation' => $weeklyPattern['variation'] ?? 0
        ];
    }

    /**
     * Check for weekly seasonality
     */
    private function checkWeeklyPattern(array $dataPoints): array
    {
        $weekdaySales = array_fill(0, 7, []);

        foreach ($dataPoints as $point) {
            $weekday = date('w', strtotime($point['date']));
            $weekdaySales[$weekday][] = $point['y'];
        }

        $weekdayAverages = [];
        foreach ($weekdaySales as $day => $sales) {
            if (count($sales) > 0) {
                $weekdayAverages[$day] = array_sum($sales) / count($sales);
            }
        }

        if (count($weekdayAverages) < 7) {
            return ['detected' => false];
        }

        $avgSales = array_sum($weekdayAverages) / count($weekdayAverages);
        $maxDay = array_search(max($weekdayAverages), $weekdayAverages);
        $variation = (max($weekdayAverages) - min($weekdayAverages)) / $avgSales;

        return [
            'detected' => $variation > 0.3, // 30% variation threshold
            'peak_day' => $maxDay,
            'variation' => $variation,
            'averages' => $weekdayAverages
        ];
    }

    /**
     * Generate forecast using trend and seasonality
     */
    private function generateForecast(array $trend, array $seasonality, int $days): array
    {
        $daily = [];
        $totalDemand = 0;

        for ($i = 0; $i < $days; $i++) {
            // Base prediction from linear trend
            $basePrediction = $trend['slope'] * ($i + count($trend)) + $trend['intercept'];

            // Apply seasonality if detected
            if ($seasonality['detected'] && isset($seasonality['averages'])) {
                $futureDate = date('Y-m-d', strtotime("+{$i} days"));
                $weekday = date('w', strtotime($futureDate));
                $seasonalFactor = $seasonality['averages'][$weekday] / $trend['average'];
                $prediction = $basePrediction * $seasonalFactor;
            } else {
                $prediction = $basePrediction;
            }

            // Ensure non-negative prediction
            $prediction = max(0, round($prediction, 2));

            $daily[] = [
                'date' => date('Y-m-d', strtotime("+{$i} days")),
                'predicted_demand' => $prediction
            ];

            $totalDemand += $prediction;
        }

        return [
            'daily' => $daily,
            'total_demand' => round($totalDemand, 2)
        ];
    }

    /**
     * Calculate confidence intervals
     */
    private function calculateConfidenceIntervals(array $dataPoints, array $forecast): array
    {
        // Calculate standard deviation of historical data
        $values = array_column($dataPoints, 'y');
        $mean = array_sum($values) / count($values);
        $variance = 0;

        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }

        $stdDev = sqrt($variance / count($values));

        // 95% confidence interval (±1.96 standard deviations)
        $marginOfError = 1.96 * $stdDev;

        $totalDemand = $forecast['total_demand'];
        $lowerBound = max(0, $totalDemand - $marginOfError * sqrt(count($forecast['daily'])));
        $upperBound = $totalDemand + $marginOfError * sqrt(count($forecast['daily']));

        // Calculate accuracy score based on coefficient of variation
        $coefficientOfVariation = $mean > 0 ? ($stdDev / $mean) : 1;
        $accuracyScore = max(0, min(100, (1 - $coefficientOfVariation) * 100));

        return [
            'level' => 0.95,
            'lower' => round($lowerBound, 2),
            'upper' => round($upperBound, 2),
            'accuracy' => round($accuracyScore, 1),
            'confidence_description' => $this->getConfidenceDescription($accuracyScore)
        ];
    }

    /**
     * Get confidence description
     */
    private function getConfidenceDescription(float $accuracy): string
    {
        if ($accuracy >= 80) return 'High confidence';
        if ($accuracy >= 60) return 'Moderate confidence';
        if ($accuracy >= 40) return 'Low confidence';
        return 'Very low confidence';
    }

    /**
     * Calculate reorder recommendations
     */
    private function calculateReorderRecommendations(array $product, array $forecast, array $confidence): array
    {
        $currentStock = (int)$product['stock_quantity'];
        $predictedDemand = $forecast['total_demand'];
        $safetyStockMultiplier = 1.2; // 20% safety stock

        // Calculate reorder point
        $reorderPoint = ceil($predictedDemand * $safetyStockMultiplier);
        $reorderNeeded = max(0, $reorderPoint - $currentStock);

        // Calculate optimal order quantity (Economic Order Quantity approximation)
        $annualDemand = $predictedDemand * (365 / count($forecast['daily']));
        $orderingCost = 50; // Estimated ordering cost
        $holdingCost = $product['cost'] * 0.25; // 25% of item cost
        $eoq = $holdingCost > 0 ? sqrt((2 * $annualDemand * $orderingCost) / $holdingCost) : $reorderNeeded;

        $recommendations = [
            'action_needed' => $currentStock < $reorderPoint,
            'current_stock' => $currentStock,
            'predicted_demand' => round($predictedDemand, 2),
            'reorder_point' => $reorderPoint,
            'reorder_quantity' => max($reorderNeeded, ceil($eoq)),
            'safety_stock' => ceil($predictedDemand * 0.2),
            'stockout_risk' => $this->calculateStockoutRisk($currentStock, $predictedDemand, $confidence),
            'days_of_stock_remaining' => $predictedDemand > 0 ? round($currentStock / ($predictedDemand / count($forecast['daily'])), 1) : 999,
            'estimated_cost' => round(max($reorderNeeded, ceil($eoq)) * $product['cost'], 2)
        ];

        // Add urgency level
        if ($recommendations['stockout_risk'] > 70) {
            $recommendations['urgency'] = 'urgent';
            $recommendations['message'] = 'Order immediately - high stockout risk';
        } elseif ($recommendations['stockout_risk'] > 40) {
            $recommendations['urgency'] = 'high';
            $recommendations['message'] = 'Order soon - moderate stockout risk';
        } elseif ($recommendations['action_needed']) {
            $recommendations['urgency'] = 'normal';
            $recommendations['message'] = 'Plan order for optimal inventory levels';
        } else {
            $recommendations['urgency'] = 'low';
            $recommendations['message'] = 'Stock levels adequate';
        }

        return $recommendations;
    }

    /**
     * Calculate stockout risk percentage
     */
    private function calculateStockoutRisk(int $currentStock, float $predictedDemand, array $confidence): float
    {
        if ($predictedDemand == 0) return 0;

        $coverage = $currentStock / $predictedDemand;
        $accuracyFactor = $confidence['accuracy'] / 100;

        // Risk increases as coverage decreases and accuracy decreases
        $baseRisk = max(0, min(100, (1 - $coverage) * 100));
        $adjustedRisk = $baseRisk * (2 - $accuracyFactor);

        return round(min(100, $adjustedRisk), 1);
    }

    /**
     * Bulk forecast for all products
     */
    public function bulkForecast(int $forecastDays = 30, array $filters = []): array
    {
        try {
            // Get products that need forecasting
            $where = ["p.is_active = 1"];
            $params = [];

            if (!empty($filters['category_id'])) {
                $where[] = "p.category_id = ?";
                $params[] = $filters['category_id'];
            }

            if (!empty($filters['low_stock_only'])) {
                $where[] = "p.stock_quantity <= p.low_stock_threshold";
            }

            $whereClause = implode(' AND ', $where);
            $limit = $filters['limit'] ?? 100;

            $products = TenantDatabase::fetchAllTenant(
                "SELECT id, name, sku FROM products p
                 WHERE {$whereClause}
                 ORDER BY p.name
                 LIMIT ?",
                array_merge($params, [$limit])
            );

            $forecasts = [];
            $highRiskProducts = [];

            foreach ($products as $product) {
                $forecast = $this->forecastDemand($product['id'], $forecastDays);

                if ($forecast['success']) {
                    $forecasts[] = [
                        'product_id' => $product['id'],
                        'product_name' => $product['name'],
                        'sku' => $product['sku'],
                        'predicted_demand' => $forecast['forecast']['predicted_demand'],
                        'stockout_risk' => $forecast['recommendations']['stockout_risk'],
                        'action_needed' => $forecast['recommendations']['action_needed'],
                        'urgency' => $forecast['recommendations']['urgency']
                    ];

                    if ($forecast['recommendations']['stockout_risk'] > 50) {
                        $highRiskProducts[] = $product;
                    }
                }
            }

            return [
                'success' => true,
                'forecast_days' => $forecastDays,
                'products_analyzed' => count($forecasts),
                'high_risk_count' => count($highRiskProducts),
                'forecasts' => $forecasts,
                'high_risk_products' => $highRiskProducts
            ];

        } catch (\Exception $e) {
            $this->logger->error('Bulk forecast failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
