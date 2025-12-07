<?php

namespace App\Services\AI;

use App\Core\Database;

/**
 * AI-Powered Image Recognition for POS Scanning
 * 
 * Handles:
 * - Product image scanning at POS
 * - Barcode detection from images
 * - Product matching by visual similarity
 * - Automatic cart addition
 */
class ImageRecognitionService
{
    /**
     * Scan product image and identify product
     */
    public function scanProductImage(string $imagePath): array
    {
        $startTime = microtime(true);
        
        try {
            // 1. Extract features from image
            $features = $this->extractImageFeatures($imagePath);
            
            // 2. Try to detect barcode in image first
            $barcode = $this->detectBarcodeInImage($imagePath);
            if ($barcode) {
                $product = $this->findProductByBarcode($barcode);
                if ($product) {
                    $this->logScan('barcode', $barcode, $product['id'], 0.95, microtime(true) - $startTime);
                    return [
                        'success' => true,
                        'method' => 'barcode',
                        'product' => $product,
                        'confidence' => 0.95
                    ];
                }
            }

            // 3. Try visual matching against product images
            $match = $this->findProductByVisualMatch($features);
            if ($match) {
                $this->logScan('image', json_encode($features), $match['product_id'], $match['confidence'], microtime(true) - $startTime);
                return [
                    'success' => true,
                    'method' => 'visual_match',
                    'product' => $match['product'],
                    'confidence' => $match['confidence']
                ];
            }

            return [
                'success' => false,
                'error' => 'No matching product found'
            ];

        } catch (\Exception $e) {
            error_log("Image scan error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract visual features from image
     */
    private function extractImageFeatures(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new \Exception('Image file not found');
        }

        // Load image
        $image = imagecreatefromstring(file_get_contents($imagePath));
        if (!$image) {
            throw new \Exception('Failed to load image');
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Extract color histogram (simplified)
        $histogram = [
            'red' => [],
            'green' => [],
            'blue' => []
        ];

        $sampleSize = 20; // Sample every 20 pixels for performance
        
        for ($y = 0; $y < $height; $y += $sampleSize) {
            for ($x = 0; $x < $width; $x += $sampleSize) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $histogram['red'][] = $r;
                $histogram['green'][] = $g;
                $histogram['blue'][] = $b;
            }
        }

        imagedestroy($image);

        // Calculate average colors
        return [
            'avg_red' => array_sum($histogram['red']) / count($histogram['red']),
            'avg_green' => array_sum($histogram['green']) / count($histogram['green']),
            'avg_blue' => array_sum($histogram['blue']) / count($histogram['blue']),
            'width' => $width,
            'height' => $height,
            'aspect_ratio' => $width / $height
        ];
    }

    /**
     * Detect barcode in image using pattern matching
     */
    private function detectBarcodeInImage(string $imagePath): ?string
    {
        // This is a simplified implementation
        // In production, use a proper barcode library like zxing-php or similar
        
        try {
            // For now, check if filename contains a barcode pattern
            $filename = basename($imagePath);
            if (preg_match('/\d{8,14}/', $filename, $matches)) {
                return $matches[0];
            }

            // TODO: Implement actual barcode detection from image pixels
            // This would require libraries like:
            // - zxing-php for barcode reading
            // - opencv for image processing

            return null;
        } catch (\Exception $e) {
            error_log("Barcode detection error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find product by barcode
     */
    private function findProductByBarcode(string $barcode): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM products WHERE barcode = ? OR sku = ? AND is_active = TRUE",
            [$barcode, $barcode]
        );
    }

    /**
     * Find product by visual similarity
     */
    private function findProductByVisualMatch(array $features): ?array
    {
        // Get all products with images
        $products = Database::fetchAll(
            "SELECT DISTINCT p.*, pi.file_path
             FROM products p
             INNER JOIN product_images pi ON p.id = pi.product_id
             WHERE p.is_active = TRUE
             AND pi.is_primary = TRUE
             LIMIT 100"
        );

        $bestMatch = null;
        $bestScore = 0;

        foreach ($products as $product) {
            $productImagePath = $_SERVER['DOCUMENT_ROOT'] . $product['file_path'];
            
            if (!file_exists($productImagePath)) {
                continue;
            }

            try {
                $productFeatures = $this->extractImageFeatures($productImagePath);
                $similarity = $this->calculateSimilarity($features, $productFeatures);

                if ($similarity > $bestScore) {
                    $bestScore = $similarity;
                    $bestMatch = $product;
                }
            } catch (\Exception $e) {
                continue; // Skip this product
            }
        }

        // Only return if confidence is above threshold
        if ($bestScore > 0.70) {
            return [
                'product' => $bestMatch,
                'product_id' => $bestMatch['id'],
                'confidence' => $bestScore
            ];
        }

        return null;
    }

    /**
     * Calculate similarity between two feature sets
     */
    private function calculateSimilarity(array $features1, array $features2): float
    {
        // Euclidean distance for color similarity
        $colorDistance = sqrt(
            pow($features1['avg_red'] - $features2['avg_red'], 2) +
            pow($features1['avg_green'] - $features2['avg_green'], 2) +
            pow($features1['avg_blue'] - $features2['avg_blue'], 2)
        );

        // Normalize to 0-1 (max distance is ~441 for RGB)
        $colorSimilarity = 1 - ($colorDistance / 441);

        // Aspect ratio similarity
        $aspectDiff = abs($features1['aspect_ratio'] - $features2['aspect_ratio']);
        $aspectSimilarity = 1 / (1 + $aspectDiff);

        // Combined score (weighted average)
        return ($colorSimilarity * 0.7) + ($aspectSimilarity * 0.3);
    }

    /**
     * Log scan attempt
     */
    private function logScan(string $type, string $data, ?int $productId, float $confidence, float $time): void
    {
        Database::insert('ai_scan_log', [
            'scan_type' => $type === 'barcode' ? 'barcode' : 'image',
            'scan_data' => substr($data, 0, 1000),
            'product_id' => $productId,
            'confidence_score' => $confidence,
            'ai_model_used' => 'custom_visual_match_v1',
            'processing_time_ms' => round($time * 1000),
            'user_id' => currentUser()['id'] ?? null
        ]);
    }

    /**
     * Add product to cart after successful scan
     */
    public function addToCartFromScan(int $productId, int $quantity = 1): array
    {
        try {
            $product = Database::fetchOne("SELECT * FROM products WHERE id = ?", [$productId]);
            
            if (!$product) {
                throw new \Exception('Product not found');
            }

            // Check stock
            if ($product['track_inventory'] && $product['stock_quantity'] < $quantity) {
                throw new \Exception('Insufficient stock');
            }

            // Add to session cart or database cart
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$productId] = [
                    'product_id' => $productId,
                    'name' => $product['name'],
                    'price' => $product['sale_price'] ?? $product['retail_price'],
                    'quantity' => $quantity,
                    'sku' => $product['sku']
                ];
            }

            return [
                'success' => true,
                'cart_item' => $_SESSION['cart'][$productId],
                'cart_total_items' => array_sum(array_column($_SESSION['cart'], 'quantity'))
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
