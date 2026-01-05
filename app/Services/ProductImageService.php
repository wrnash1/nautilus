<?php

namespace App\Services;

use App\Core\Database;

/**
 * ProductImageService
 * 
 * AI-powered service for finding and populating missing product images
 */
class ProductImageService
{
    private $db;
    private $uploadDir;

    // Common dive equipment manufacturers for image search
    private $brands = [
        'Cressi',
        'Scubapro',
        'Aqualung',
        'Mares',
        'Oceanic',
        'Suunto',
        'Shearwater',
        'Atomic',
        'Hollis',
        'Apeks',
        'Zeagle',
        'Tusa',
        'Poseidon',
        'Halcyon',
        'OMS',
        'Dive Rite',
        'Light & Motion',
        'BigBlue',
        'Sealife'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->uploadDir = BASE_PATH . '/public/assets/img/products/';

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Get all products with missing images
     */
    public function getProductsWithoutImages(): array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, sku, description, category_id, brand
            FROM products
            WHERE (image_url IS NULL OR image_url = '' OR image_url = '/assets/img/product-placeholder.png')
            ORDER BY name
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Count products missing images
     */
    public function countMissingImages(): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM products
            WHERE (image_url IS NULL OR image_url = '' OR image_url = '/assets/img/product-placeholder.png')
        ");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Generate search query for image lookup
     */
    public function buildSearchQuery(array $product): string
    {
        $parts = [];

        // Add brand if present
        if (!empty($product['brand'])) {
            $parts[] = $product['brand'];
        }

        // Add product name
        $parts[] = $product['name'];

        // Add "dive" or "scuba" context if not already present
        $query = implode(' ', $parts);
        if (stripos($query, 'dive') === false && stripos($query, 'scuba') === false) {
            $query .= ' dive equipment';
        }

        return $query;
    }

    /**
     * Fetch product image from web search (using placeholder for demo)
     * In production, integrate with Google Custom Search or similar API
     */
    public function searchProductImage(array $product): ?string
    {
        $query = $this->buildSearchQuery($product);

        // For demo: generate a placeholder image URL
        // In production, use Google Custom Search API, Bing Image Search, etc.
        $placeholderUrl = $this->generatePlaceholder($product);

        return $placeholderUrl;
    }

    /**
     * Generate a branded placeholder image
     */
    private function generatePlaceholder(array $product): string
    {
        // Use a dive-themed placeholder service or generate one
        $name = urlencode(substr($product['name'], 0, 20));
        $sku = urlencode($product['sku'] ?? '');

        // Basic placeholder endpoint (you could create your own)
        return "/assets/img/products/placeholder_{$product['id']}.png";
    }

    /**
     * Download and save image from URL
     */
    public function downloadAndSaveImage(string $imageUrl, int $productId): ?string
    {
        try {
            $imageData = @file_get_contents($imageUrl);
            if (!$imageData) {
                return null;
            }

            $extension = $this->getImageExtension($imageUrl);
            $filename = "product_{$productId}_{$extension}.jpg";
            $filepath = $this->uploadDir . $filename;

            file_put_contents($filepath, $imageData);

            return "/assets/img/products/{$filename}";
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getImageExtension(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return $ext;
        }

        return 'jpg';
    }

    /**
     * Update product with new image URL
     */
    public function updateProductImage(int $productId, string $imageUrl): bool
    {
        $stmt = $this->db->prepare("
            UPDATE products 
            SET image_url = ?, updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$imageUrl, $productId]);
    }

    /**
     * Batch process missing images
     */
    public function processMissingImages(int $limit = 10): array
    {
        $products = $this->getProductsWithoutImages();
        $results = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach (array_slice($products, 0, $limit) as $product) {
            $results['processed']++;

            $imageUrl = $this->searchProductImage($product);

            if ($imageUrl) {
                $this->updateProductImage($product['id'], $imageUrl);
                $results['success']++;
                $results['details'][] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'status' => 'success',
                    'image_url' => $imageUrl
                ];
            } else {
                $results['failed']++;
                $results['details'][] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'status' => 'failed',
                    'error' => 'Image not found'
                ];
            }
        }

        return $results;
    }

    /**
     * Search from shared Nautilus image library
     * (Future feature: central repository of dive gear images)
     */
    public function searchSharedLibrary(string $productName, string $sku = ''): ?string
    {
        // Placeholder for shared library integration
        // In production, this would query a central image repository

        // Simulate checking a shared library
        $sharedImages = [
            'bcd' => '/assets/img/shared/bcd-generic.jpg',
            'regulator' => '/assets/img/shared/regulator-generic.jpg',
            'mask' => '/assets/img/shared/mask-generic.jpg',
            'fins' => '/assets/img/shared/fins-generic.jpg',
            'wetsuit' => '/assets/img/shared/wetsuit-generic.jpg',
            'computer' => '/assets/img/shared/computer-generic.jpg',
            'tank' => '/assets/img/shared/tank-generic.jpg',
            'light' => '/assets/img/shared/light-generic.jpg',
        ];

        $productLower = strtolower($productName);

        foreach ($sharedImages as $keyword => $imagePath) {
            if (strpos($productLower, $keyword) !== false) {
                return $imagePath;
            }
        }

        return null;
    }

    /**
     * Generate AI image for product using description
     * (Placeholder for future DALL-E or similar integration)
     */
    public function generateAIImage(array $product): ?string
    {
        // Placeholder for AI image generation integration
        // Would use OpenAI DALL-E, Stable Diffusion, etc.

        return null;
    }
}
