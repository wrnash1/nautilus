<?php

namespace App\Services\Inventory;

use App\Core\Database;

class ProductService
{
    public function createProduct(array $data): int
    {
        
        return 0;
    }
    
    public function updateStock(int $productId, int $quantity, string $reason): bool
    {
        
        return false;
    }
    
    public function checkLowStock(): array
    {
        
        return [];
    }
}
