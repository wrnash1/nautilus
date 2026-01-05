<?php
/**
 * Update products with demo images
 * Run via: php update-product-images.php
 */

require_once __DIR__ . '/app/Core/bootstrap.php';

use App\Core\Database;

echo "Updating product images...\n\n";

// Image mappings - match product names to images
$imageMap = [
    'regulator' => '/assets/img/products/regulator.png',
    'mask' => '/assets/img/products/mask.png',
    'fin' => '/assets/img/products/fins.png',
    'wetsuit' => '/assets/img/products/wetsuit.png',
    'bcd' => '/assets/img/products/bcd.png',
    'computer' => '/assets/img/products/computer.png',
    'snorkel' => '/assets/img/products/snorkel.png',
    'tank' => '/assets/img/products/tank.png',
    'light' => '/assets/img/products/light.png',
    'flashlight' => '/assets/img/products/light.png',
    'torch' => '/assets/img/products/light.png',
];

try {
    // Get all products
    $products = Database::fetchAll("SELECT id, name FROM products");

    $updated = 0;
    foreach ($products as $product) {
        $name = strtolower($product['name']);
        $imageUrl = null;

        // Check if any keyword matches
        foreach ($imageMap as $keyword => $url) {
            if (strpos($name, $keyword) !== false) {
                $imageUrl = $url;
                break;
            }
        }

        // If no match, assign based on ID rotation
        if (!$imageUrl) {
            $images = array_values($imageMap);
            $imageUrl = $images[$product['id'] % count($images)];
        }

        // Update the product
        Database::query(
            "UPDATE products SET image_url = ? WHERE id = ?",
            [$imageUrl, $product['id']]
        );

        echo "Updated: {$product['name']} => {$imageUrl}\n";
        $updated++;
    }

    echo "\n✅ Updated {$updated} products with images.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
