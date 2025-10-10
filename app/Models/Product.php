<?php

namespace App\Models;

use App\Core\Database;

class Product
{
    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM products WHERE id = ? AND is_active = 1",
            [$id]
        );
    }
    
    public static function findBySku(string $sku): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM products WHERE sku = ? AND is_active = 1",
            [$sku]
        );
    }
    
    public static function getLowStock(): array
    {
        return Database::fetchAll(
            "SELECT * FROM products 
             WHERE track_inventory = 1 
             AND stock_quantity <= low_stock_threshold 
             AND is_active = 1"
        );
    }
}
