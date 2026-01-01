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

        $product = Product::create($data);

        logActivity('create', 'products', $product->id);

        return $product->id;
    }

    public function updateProduct(int $id, array $data): bool
    {
        $this->validateProductData($data, $id);

        if (isset($data['name'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }

        $product = Product::findOrFail($id);
        $product->update($data);

        logActivity('update', 'products', $id);

        return true;
    }

    public function updateStock(int $productId, int $quantity, string $reason): bool
    {
        if ($quantity == 0) {
            throw new \Exception('Quantity cannot be zero');
        }

        $type = 'adjustment';

        $product = Product::findOrFail($productId);
        $product->adjustStock($quantity, $type, $reason);

        return true;
    }

    public function checkLowStock(): array // Returns array of Models now, or array of arrays? Original returned arrays.
    {
        // Eloquent returns Collection. For backward compatibility with things expecting array of arrays, we might need toArray().
        // But if callers are updated, Collection is better.
        // Let's assume callers might handle ArrayAccess (which Collection does).
        // But if they access as array $products[0]['id'], Collection supports $products[0]['id'].
        // However, if they loop `foreach ($products as $p) echo $p['name']`, it works.
        // If they do `array_map`, it requires array.
        // "Code Correction" -> let's return Collection or Array. Eloquent returns Collection.
        // I'll return Collection ->toArray() to be safe for now, or just leave as Collection if I check usage.
        // Usage check: checkLowStock() usage is minimal.

        return Product::where('track_stock', 1)
            ->whereColumn('stock_quantity', '<=', 'reorder_level')
            ->orderBy('stock_quantity', 'asc')
            ->get()
            ->toArray();
    }

    private function validateProductData(array $data, ?int $id = null): void
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
