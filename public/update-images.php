<?php
/**
 * API endpoint to update product images
 * Access via browser: http://localhost:8000/update-images.php
 */

header('Content-Type: text/plain');
echo "Product Image Updater\n";
echo "=====================\n\n";

// Use same connection as app - include config
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = get_database_connection();

    echo "Connected to database.\n\n";

    // Image mapping
    $images = [
        '/assets/img/products/regulator.png',
        '/assets/img/products/mask.png',
        '/assets/img/products/fins.png',
        '/assets/img/products/wetsuit.png',
        '/assets/img/products/bcd.png',
        '/assets/img/products/computer.png',
        '/assets/img/products/snorkel.png',
        '/assets/img/products/tank.png',
        '/assets/img/products/light.png',
    ];

    // Get all products
    $stmt = $pdo->query("SELECT id, name FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;
    foreach ($products as $product) {
        // Assign image based on ID rotation
        $imageUrl = $images[$product['id'] % count($images)];

        $update = $pdo->prepare("UPDATE products SET image_url = ? WHERE id = ?");
        $update->execute([$imageUrl, $product['id']]);

        echo "Updated: #{$product['id']} {$product['name']} => {$imageUrl}\n";
        $count++;
    }

    echo "\n✅ Updated $count products with images.\n";
    echo "\nRefresh POS to see the changes!\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
