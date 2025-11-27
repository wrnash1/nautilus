<?php

namespace App\Controllers\API;

use App\Services\AI\InventoryForecaster;
use App\Services\AI\SalesDataCollector;

/**
 * AI API Controller
 * 
 * Provides API endpoints for AI-powered inventory forecasting
 */
class AIController
{
    private int $tenantId;
    private InventoryForecaster $forecaster;
    private SalesDataCollector $dataCollector;

    public function __construct()
    {
        $this->tenantId = $_SESSION['tenant_id'] ?? 1;
        $this->forecaster = new InventoryForecaster($this->tenantId);
        $this->dataCollector = new SalesDataCollector($this->tenantId);
    }

    /**
     * GET /api/ai/forecast/{productId}
     * Get demand forecast for a product
     */
    public function getForecast(int $productId): void
    {
        $daysAhead = $_GET['days'] ?? 30;
        $forecast = $this->forecaster->getDetailedForecast($productId, (int)$daysAhead);
        
        header('Content-Type: application/json');
        echo json_encode($forecast);
    }

    /**
     * GET /api/ai/recommendations
     * Get inventory purchase recommendations
     */
    public function getRecommendations(): void
    {
        $limit = $_GET['limit'] ?? 20;
        $recommendations = $this->forecaster->getInventoryRecommendations((int)$limit);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'count' => count($recommendations),
            'recommendations' => $recommendations
        ]);
    }

    /**
     * GET /api/ai/reorder/{productId}
     * Get reorder point information
     */
    public function getReorderInfo(int $productId): void
    {
        $leadTime = $_GET['lead_time'] ?? 7;
        $reorder = $this->forecaster->calculateReorderPoint($productId, (int)$leadTime);
        
        header('Content-Type: application/json');
        echo json_encode($reorder);
    }

    /**
     * GET /api/ai/seasonal
     * Get seasonal insights
     */
    public function getSeasonalInsights(): void
    {
        $productId = $_GET['product_id'] ?? null;
        $insights = $this->forecaster->getSeasonalInsights($productId);
        
        header('Content-Type: application/json');
        echo json_encode($insights);
    }

    /**
     * GET /api/ai/top-products
     * Get top selling products
     */
    public function getTopProducts(): void
    {
        $limit = $_GET['limit'] ?? 20;
        $daysBack = $_GET['days'] ?? 90;
        
        $products = $this->dataCollector->getTopSellingProducts((int)$limit, (int)$daysBack);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'count' => count($products),
            'products' => $products
        ]);
    }

    /**
     * GET /api/ai/insights
     * Get comprehensive AI insights dashboard data
     */
    public function getInsights(): void
    {
        $topProducts = $this->dataCollector->getTopSellingProducts(10, 30);
        $recommendations = $this->forecaster->getInventoryRecommendations(10);
        $seasonal = $this->forecaster->getSeasonalInsights();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => [
                'top_products' => $topProducts,
                'reorder_recommendations' => $recommendations,
                'seasonal_insights' => $seasonal,
                'summary' => [
                    'products_needing_restock' => count($recommendations),
                    'urgent_items' => count(array_filter($recommendations, fn($r) => $r['urgency'] === 'high')),
                    'peak_season_month' => $seasonal['peak_month'] ?? 'Unknown'
                ]
            ]
        ]);
    }
}
