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
             LEFT JOIN product_categories c ON p.category_id = c.id
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
             LEFT JOIN product_categories c ON p.category_id = c.id
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
             LEFT JOIN product_categories c ON p.category_id = c.id
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
             LEFT JOIN product_categories c ON p.category_id = c.id
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
             LEFT JOIN product_categories c ON p.category_id = c.id
             WHERE p.track_inventory = 1 
             AND p.stock_quantity <= p.low_stock_threshold 
             AND p.is_active = 1
             ORDER BY p.stock_quantity ASC"
        ) ?? [];
    }
    
    public static function create(array $data): int
    {
        Database::query(
            "INSERT INTO products (
                category_id, vendor_id, name, slug, sku, barcode, qr_code, description,
                retail_price, cost_price, weight, weight_unit, dimensions,
                color, material, manufacturer, warranty_info, location_in_store,
                supplier_info, expiration_date, track_inventory,
                stock_quantity, low_stock_threshold, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['category_id'] ?? null,
                $data['vendor_id'] ?? null,
                $data['name'],
                $data['slug'],
                $data['sku'],
                $data['barcode'] ?? null,
                $data['qr_code'] ?? null,
                $data['description'] ?? null,
                $data['retail_price'],
                $data['cost_price'] ?? 0,
                $data['weight'] ?? null,
                $data['weight_unit'] ?? 'lb',
                $data['dimensions'] ?? null,
                $data['color'] ?? null,
                $data['material'] ?? null,
                $data['manufacturer'] ?? null,
                $data['warranty_info'] ?? null,
                $data['location_in_store'] ?? null,
                $data['supplier_info'] ?? null,
                $data['expiration_date'] ?? null,
                $data['track_inventory'] ?? 1,
                $data['stock_quantity'] ?? 0,
                $data['low_stock_threshold'] ?? 5,
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
                barcode = ?, qr_code = ?, description = ?, retail_price = ?, cost_price = ?,
                weight = ?, weight_unit = ?, dimensions = ?, color = ?, material = ?,
                manufacturer = ?, warranty_info = ?, location_in_store = ?,
                supplier_info = ?, expiration_date = ?, track_inventory = ?,
                stock_quantity = ?, low_stock_threshold = ?, is_active = ?,
                updated_at = NOW()
             WHERE id = ?",
            [
                $data['category_id'] ?? null,
                $data['vendor_id'] ?? null,
                $data['name'],
                $data['slug'],
                $data['sku'],
                $data['barcode'] ?? null,
                $data['qr_code'] ?? null,
                $data['description'] ?? null,
                $data['retail_price'],
                $data['cost_price'] ?? 0,
                $data['weight'] ?? null,
                $data['weight_unit'] ?? 'lb',
                $data['dimensions'] ?? null,
                $data['color'] ?? null,
                $data['material'] ?? null,
                $data['manufacturer'] ?? null,
                $data['warranty_info'] ?? null,
                $data['location_in_store'] ?? null,
                $data['supplier_info'] ?? null,
                $data['expiration_date'] ?? null,
                $data['track_inventory'] ?? 1,
                $data['stock_quantity'] ?? 0,
                $data['low_stock_threshold'] ?? 5,
                $data['is_active'] ?? 1,
                $id
            ]
        );

        return true;
    }
    
    public static function adjustStock(int $productId, int $quantity, string $type, $reference = null): bool
    {
        $product = self::find($productId);
        $quantityBefore = (int)$product['stock_quantity'];
        $quantityAfter = $quantityBefore + $quantity;
        
        Database::query(
            "UPDATE products SET stock_quantity = stock_quantity + ?, updated_at = NOW() WHERE id = ?",
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
            "SELECT p.id, p.sku, p.name, p.stock_quantity, p.low_stock_threshold,
                    p.cost_price, p.retail_price, c.name as category_name,
                    (p.stock_quantity * p.cost_price) as inventory_value
             FROM products p
             LEFT JOIN product_categories c ON p.category_id = c.id
             WHERE p.track_inventory = 1 AND p.is_active = 1
             ORDER BY p.name ASC"
        ) ?? [];
    }
}
