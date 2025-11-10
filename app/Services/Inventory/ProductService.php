<?php

namespace App\Services\Inventory;

use App\Models\Product;
use App\Core\Database;

class ProductService
{
    public function createProduct(array $data): int
    {
        $this->validateProductData($data);
        
        $data['slug'] = $this->generateSlug($data['name']);
        
        $productId = Product::create($data);
        
        logActivity('create', 'products', $productId);
        
        return $productId;
    }
    
    public function updateProduct(int $id, array $data): bool
    {
        $this->validateProductData($data, $id);
        
        $data['slug'] = $this->generateSlug($data['name']);
        
        Product::update($id, $data);
        
        logActivity('update', 'products', $id);
        
        return true;
    }
    
    public function updateStock(int $productId, int $quantity, string $reason): bool
    {
        if ($quantity == 0) {
            throw new \Exception('Quantity cannot be zero');
        }
        
        $type = 'adjustment';
        
        Product::adjustStock($productId, $quantity, $type, null);
        
        return true;
    }
    
    public function checkLowStock(): array
    {
        return Product::getLowStock();
    }
    
    private function validateProductData(array $data, ??int $id = null): void
    {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'Product name is required';
        }
        
        if (empty($data['sku'])) {
            $errors[] = 'SKU is required';
        } else {
            $existing = Database::fetchOne(
                "SELECT id FROM products WHERE sku = ? AND id != ? AND is_active = 1",
                [$data['sku'], $id ?? 0]
            );
            if ($existing) {
                $errors[] = 'SKU already exists';
            }
        }
        
        if (empty($data['retail_price']) || $data['retail_price'] <= 0) {
            $errors[] = 'Retail price must be greater than 0';
        }
        
        if (!empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }
    }
    
    private function generateSlug(string $name): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }
}
