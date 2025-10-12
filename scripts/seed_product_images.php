<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/helpers.php';

use App\Core\Database;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Seeding product images...\n";

$productImages = [
    [1, 'https://placehold.co/600x400/4A90E2/ffffff?text=Cressi+Big+Eyes+Evolution', 'placeholder.jpg', 'Cressi Big Eyes Evolution Dive Mask', 1, 0],
    [2, 'https://placehold.co/600x400/4A90E2/ffffff?text=Scubapro+Crystal+Vu', 'placeholder.jpg', 'Scubapro Crystal Vu Mask', 1, 0],
    [3, 'https://placehold.co/600x400/4A90E2/ffffff?text=Atomic+Venom+Frameless', 'placeholder.jpg', 'Atomic Venom Frameless Mask', 1, 0],
    [4, 'https://placehold.co/600x400/7B68EE/ffffff?text=Mares+Avanti+Quattro+Plus', 'placeholder.jpg', 'Mares Avanti Quattro Plus Fins', 1, 0],
    [5, 'https://placehold.co/600x400/7B68EE/ffffff?text=Scubapro+Jet+Fins', 'placeholder.jpg', 'Scubapro Jet Fins', 1, 0],
    [6, 'https://placehold.co/600x400/FF6B6B/ffffff?text=3mm+Shorty+Wetsuit', 'placeholder.jpg', '3mm Shorty Wetsuit', 1, 0],
    [7, 'https://placehold.co/600x400/FF6B6B/ffffff?text=5mm+Full+Wetsuit', 'placeholder.jpg', '5mm Full Wetsuit', 1, 0],
    [8, 'https://placehold.co/600x400/FF6B6B/ffffff?text=7mm+Semi-Dry+Suit', 'placeholder.jpg', '7mm Semi-Dry Suit', 1, 0],
    [9, 'https://placehold.co/600x400/20B2AA/ffffff?text=Oceanic+Alpha+9+Regulator', 'placeholder.jpg', 'Oceanic Alpha 9 Regulator', 1, 0],
    [10, 'https://placehold.co/600x400/20B2AA/ffffff?text=Apeks+XTX200+Regulator', 'placeholder.jpg', 'Apeks XTX200 Regulator', 1, 0],
    [11, 'https://placehold.co/600x400/FFA500/ffffff?text=Cressi+Start+BCD', 'placeholder.jpg', 'Cressi Start BCD', 1, 0],
    [12, 'https://placehold.co/600x400/FFA500/ffffff?text=Scubapro+Hydros+Pro', 'placeholder.jpg', 'Scubapro Hydros Pro BCD', 1, 0],
    [13, 'https://placehold.co/600x400/9370DB/ffffff?text=Suunto+D5+Computer', 'placeholder.jpg', 'Suunto D5 Dive Computer', 1, 0],
    [14, 'https://placehold.co/600x400/9370DB/ffffff?text=Shearwater+Perdix', 'placeholder.jpg', 'Shearwater Perdix Dive Computer', 1, 0],
    [15, 'https://placehold.co/600x400/32CD32/ffffff?text=Mesh+Gear+Bag', 'placeholder.jpg', 'Mesh Gear Bag', 1, 0],
    [16, 'https://placehold.co/600x400/32CD32/ffffff?text=Dive+Light', 'placeholder.jpg', 'LED Dive Light', 1, 0],
];

try {
    Database::query("DELETE FROM product_images");
    echo "Cleared existing product images\n";
    
    foreach ($productImages as $img) {
        Database::query(
            "INSERT INTO product_images (product_id, file_path, file_name, alt_text, is_primary, sort_order, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            $img
        );
        echo "Inserted image for product ID {$img[0]}\n";
    }
    
    echo "\nSuccessfully seeded " . count($productImages) . " product images!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
