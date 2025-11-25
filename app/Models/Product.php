<?php

namespace App\Models;

use App\Core\Database;

class Product
{
    public static function all(int $limit = 50, int $offset = 0): array
    {
        return Database::fetchAll(
            "SELECT p.*, c.name as category_name,
             pi.file_path as image_url, pi.alt_text as image_alt
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             WHERE p.is_active = 1
             ORDER BY p.name ASC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        ) ?? [];
    }
    
    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT p.*, c.name as category_name,
             pi.file_path as image_url, pi.alt_text as image_alt
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             WHERE p.id = ? AND p.is_active = 1",
            [$id]
        );
    }
    
    public static function findBySku(string $sku): ?array
    {
        return Database::fetchOne(
            "SELECT p.*, c.name as category_name 
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.sku = ? AND p.is_active = 1",
            [$sku]
        );
    }
    
    public static function search(string $query, int $limit = 20): array
    {
        $searchTerm = "%{$query}%";
        return Database::fetchAll(
            "SELECT p.*, c.name as category_name,
             pi.file_path as image_url, pi.alt_text as image_alt
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             WHERE p.is_active = 1 
             AND (p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)
             ORDER BY p.name ASC
             LIMIT ?",
            [$searchTerm, $searchTerm, $searchTerm, $limit]
        ) ?? [];
    }
    
    public static function getLowStock(): array
    {
        return Database::fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.track_inventory = 1 
             AND p.quantity_in_stock <= p.low_stock_threshold 
             AND p.is_active = 1
             ORDER BY p.quantity_in_stock ASC"
        ) ?? [];
    }
    
    public static function create(array $data): int
    {
        Database::query(
            "INSERT INTO products (
                category_id, vendor_id, name, slug, sku, barcode, description,
                price, cost, model, attributes,
                quantity_in_stock, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['category_id'] ?? null,
                $data['vendor_id'] ?? null,
                $data['name'],
                $data['slug'],
                $data['sku'],
                $data['barcode'] ?? null,
                $data['description'] ?? null,
                $data['price'],
                $data['cost'] ?? 0,
                $data['model'] ?? null,
                $data['attributes'] ?? null,
                $data['quantity_in_stock'] ?? 0,
                $data['is_active'] ?? 1
            ]
        );

        return (int)Database::lastInsertId();
    }
    
    public static function update(int $id, array $data): bool
    {
        Database::query(
            "UPDATE products SET
                category_id = ?, vendor_id = ?, name = ?, slug = ?, sku = ?,
                barcode = ?, description = ?, price = ?, cost = ?,
                model = ?, attributes = ?,
                quantity_in_stock = ?, is_active = ?,
                updated_at = NOW()
             WHERE id = ?",
            [
                $data['category_id'] ?? null,
                $data['vendor_id'] ?? null,
                $data['name'],
                $data['slug'],
                $data['sku'],
                $data['barcode'] ?? null,
                $data['description'] ?? null,
                $data['price'],
                $data['cost'] ?? 0,
                $data['model'] ?? null,
                $data['attributes'] ?? null,
                $data['quantity_in_stock'] ?? 0,
                $data['is_active'] ?? 1,
                $id
            ]
        );

        return true;
    }
    
    public static function adjustStock(int $productId, int $quantity, string $type, $reference = null): bool
    {
        $product = self::find($productId);
        $quantityBefore = (int)$product['quantity_in_stock'];
        $quantityAfter = $quantityBefore + $quantity;
        
        Database::query(
            "UPDATE products SET quantity_in_stock = quantity_in_stock + ?, updated_at = NOW() WHERE id = ?",
            [$quantity, $productId]
        );
        
        Database::query(
            "INSERT INTO inventory_transactions (
                product_id, transaction_type, quantity_change, quantity_before, 
                quantity_after, reference_type, reference_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $productId,
                $type,
                $quantity,
                $quantityBefore,
                $quantityAfter,
                $reference ? 'transaction' : null,
                $reference
            ]
        );
        
        return true;
    }
    
    public static function count(): int
    {
        $result = Database::fetchOne("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
        return (int)($result['count'] ?? 0);
    }
    
    public static function delete(int $id): bool
    {
        Database::query(
            "UPDATE products SET is_active = 0, updated_at = NOW() WHERE id = ?",
            [$id]
        );
        
        logActivity('delete', 'products', $id);
        
        return true;
    }
    
    public static function getInventoryTransactions(int $productId, int $limit = 50): array
    {
        return Database::fetchAll(
            "SELECT it.*, u.first_name, u.last_name 
             FROM inventory_transactions it
             LEFT JOIN users u ON it.user_id = u.id
             WHERE it.product_id = ?
             ORDER BY it.created_at DESC
             LIMIT ?",
            [$productId, $limit]
        ) ?? [];
    }
    
    public static function getInventoryReport(): array
    {
        return Database::fetchAll(
            "SELECT p.id, p.sku, p.name, p.quantity_in_stock, p.low_stock_threshold,
                    p.cost, p.price, p.model, p.attributes, c.name as category_name,
                    (p.quantity_in_stock * p.cost) as inventory_value
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.track_inventory = 1 AND p.is_active = 1
             ORDER BY p.name ASC"
        ) ?? [];
    }
}
