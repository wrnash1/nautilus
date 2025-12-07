<?php

namespace App\Services\AI;

use App\Core\Database;
use Phpml\Classification\KNearestNeighbors;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;

/**
 * AI-Powered Product Data Enrichment Service
 * 
 * Uses machine learning to:
 * - Suggest product categories based on name/description
 * - Auto-fill missing product information
 * - Extract product attributes from descriptions
 * - Suggest related products
 */
class ProductEnrichmentService
{
    private $classifier;
    private $vectorizer;

    public function __construct()
    {
        $this->vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
    }

    /**
     * Enrich product data using AI
     */
    public function enrichProduct(int $productId): array
    {
        $startTime = microtime(true);
        
        $product = Database::fetchOne("SELECT * FROM products WHERE id = ?", [$productId]);
        if (!$product) {
            throw new \Exception('Product not found');
        }

        $enrichments = [];
        $confidence = 0;

        // 1. Suggest category if missing
        if (empty($product['category_id'])) {
            $categoryPrediction = $this->suggestCategory($product);
            if ($categoryPrediction) {
                $enrichments['suggested_category'] = $categoryPrediction['category_id'];
                $enrichments['category_name'] = $categoryPrediction['category_name'];
                $confidence += $categoryPrediction['confidence'];
            }
        }

        // 2. Extract and suggest product attributes
        $attributes = $this->extractAttributes($product);
        if (!empty($attributes)) {
            $enrichments['suggested_attributes'] = $attributes;
            $confidence += 0.15;
        }

        // 3. Suggest shipping class
        $shippingClass = $this->suggestShippingClass($product);
        if ($shippingClass) {
            $enrichments['suggested_shipping_class'] = $shippingClass;
            $confidence += 0.10;
        }

        // 4. Detect hazmat items
        $isHazmat = $this->detectHazmat($product);
        if ($isHazmat) {
            $enrichments['is_hazmat'] = true;
            $enrichments['hazmat_warning'] = 'This product may contain hazardous materials (compressed air/gas)';
            $confidence += 0.20;
        }

        // 5. Suggest meta description
        if (empty($product['meta_description'])) {
            $metaDescription = $this->generateMetaDescription($product);
            if ($metaDescription) {
                $enrichments['suggested_meta_description'] = $metaDescription;
                $confidence += 0.10;
            }
        }

        // Calculate final confidence score
        $finalConfidence = min($confidence, 1.0);

        // Update product with AI enrichment data
        Database::update('products', [
            'ai_enriched' => true,
            'ai_enriched_at' => date('Y-m-d H:i:s'),
            'ai_confidence_score' => $finalConfidence,
            'ai_suggested_category' => $enrichments['suggested_category'] ?? null
        ], ['id' => $productId]);

        $processingTime = round((microtime(true) - $startTime) * 1000);

        return [
            'product_id' => $productId,
            'enrichments' => $enrichments,
            'confidence_score' => $finalConfidence,
            'processing_time_ms' => $processingTime
        ];
    }

    /**
     * Suggest product category using ML classification
     */
    private function suggestCategory(array $product): ?array
    {
        try {
            // Get training data from existing categorized products
            $trainingProducts = Database::fetchAll(
                "SELECT p.name, p.description, p.sku, pc.id as category_id, pc.name as category_name
                 FROM products p
                 INNER JOIN product_categories pc ON p.category_id = pc.id
                 WHERE p.category_id IS NOT NULL
                 LIMIT 1000"
            );

            if (empty($trainingProducts)) {
                return null;
            }

            // Prepare training data
            $samples = [];
            $labels = [];
            $categoryMap = [];

            foreach ($trainingProducts as $tp) {
                $text = strtolower($tp['name'] . ' ' . $tp['description'] . ' ' . $tp['sku']);
                $samples[] = $text;
                $labels[] = $tp['category_id'];
                $categoryMap[$tp['category_id']] = $tp['category_name'];
            }

            // Train classifier
            $classifier = new KNearestNeighbors(3);
            $classifier->train($samples, $labels);

            // Predict category
            $testText = strtolower($product['name'] . ' ' . $product['description'] . ' ' . $product['sku']);
            $predictedCategoryId = $classifier->predict($testText);

            return [
                'category_id' => $predictedCategoryId,
                'category_name' => $categoryMap[$predictedCategoryId] ?? 'Unknown',
                'confidence' => 0.30 // KNN doesn't provide confidence, use fixed value
            ];
        } catch (\Exception $e) {
            error_log("Category prediction error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract product attributes from description
     */
    private function extractAttributes(array $product): array
    {
        $attributes = [];
        $text = strtolower($product['name'] . ' ' . $product['description']);

        // Diving-specific attribute extraction patterns
        $patterns = [
            'size' => '/\b(small|medium|large|xl|xxl|x-large|xx-large|xs|x-small|\d+["\']|\d+cm|\d+mm)\b/i',
            'color' => '/\b(black|blue|red|yellow|pink|green|white|gray|grey|orange|purple|clear|neon)\b/i',
            'material' => '/\b(neoprene|aluminum|steel|titanium|silicone|rubber|plastic|carbon fiber|stainless steel)\b/i',
            'brand' => '/\b(scubapro|mares|cressi|aqualung|oceanic|suunto|shearwater|atomic)\b/i',
            'pressure' => '/\b(\d+\s?psi|\d+\s?bar)\b/i',
            'depth_rating' => '/\b(\d+\s?(?:feet|ft|meters|m))\b/i'
        ];

        foreach ($patterns as $attrName => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $attributes[$attrName] = trim($matches[1]);
            }
        }

        return $attributes;
    }

    /**
     * Suggest shipping class based on product characteristics
     */
    private function suggestShippingClass(array $product): string
    {
        $name = strtolower($product['name']);
        $weight = (float)($product['weight'] ?? 0);

        // Heavy items
        if ($weight > 50 || stripos($name, 'tank') !== false || stripos($name, 'cylinder') !== false) {
            return 'freight';
        }

        // Fragile items
        if (stripos($name, 'mask') !== false || stripos($name, 'computer') !== false || 
            stripos($name, 'gauge') !== false || stripos($name, 'dive watch') !== false) {
            return 'fragile';
        }

        // Standard items
        return 'standard';
    }

    /**
     * Detect hazardous materials
     */
    private function detectHazmat(array $product): bool
    {
        $text = strtolower($product['name'] . ' ' . $product['description']);
        
        $hazmatKeywords = [
            'compressed air',
            'compressed gas',
            'scuba tank',
            'dive tank',
            'cylinder',
            'high pressure',
            'oxygen',
            'nitrox',
            'trimix',
            'enriched air'
        ];

        foreach ($hazmatKeywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate SEO-friendly meta description
     */
    private function generateMetaDescription(array $product): string
    {
        $name = $product['name'];
        $price = $product['retail_price'];
        $category = $product['category_name'] ?? 'diving equipment';

        $templates = [
            "Shop {$name} at competitive prices. High-quality {$category} for diving enthusiasts.",
            "Get the best deals on {$name}. Premium {$category} starting at $" . number_format($price, 2) . ".",
            "{$name} - Professional-grade {$category}. Free shipping on orders over $100.",
        ];

        return $templates[array_rand($templates)];
    }

    /**
     * Bulk enrich all products missing data
     */
    public function enrichAllProducts(int $limit = 100): array
    {
        $products = Database::fetchAll(
            "SELECT id FROM products 
             WHERE (ai_enriched = FALSE OR ai_enriched IS NULL)
             AND is_active = TRUE
             LIMIT ?",
            [$limit]
        );

        $results = [
            'total_processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($products as $product) {
            try {
                $this->enrichProduct($product['id']);
                $results['successful']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'product_id' => $product['id'],
                    'error' => $e->getMessage()
                ];
            }
            $results['total_processed']++;
        }

        return $results;
    }
}
