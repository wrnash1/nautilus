<?php

namespace App\Models;

use App\Core\Database;

class Category
{
    public static function all(): array
    {
        return Database::fetchAll(
            "SELECT * FROM product_categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC"
        ) ?? [];
    }
    
    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM product_categories WHERE id = ? AND is_active = 1",
            [$id]
        );
    }
    
    public static function create(array $data): int
    {
        Database::query(
            "INSERT INTO product_categories (parent_id, name, slug, description, is_active) 
             VALUES (?, ?, ?, ?, ?)",
            [
                $data['parent_id'] ?? null,
                $data['name'],
                $data['slug'],
                $data['description'] ?? null,
                $data['is_active'] ?? 1
            ]
        );
        
        $categoryId = (int)Database::lastInsertId();
        logActivity('create', 'categories', $categoryId);
        
        return $categoryId;
    }
    
    public static function update(int $id, array $data): bool
    {
        Database::query(
            "UPDATE product_categories SET 
                parent_id = ?, name = ?, slug = ?, description = ?, 
                is_active = ?, updated_at = NOW()
             WHERE id = ?",
            [
                $data['parent_id'] ?? null,
                $data['name'],
                $data['slug'],
                $data['description'] ?? null,
                $data['is_active'] ?? 1,
                $id
            ]
        );
        
        logActivity('update', 'categories', $id);
        
        return true;
    }
    
    public static function delete(int $id): bool
    {
        Database::query(
            "UPDATE product_categories SET is_active = 0, updated_at = NOW() WHERE id = ?",
            [$id]
        );
        
        logActivity('delete', 'categories', $id);
        
        return true;
    }
    
    public static function count(): int
    {
        $result = Database::fetchOne("SELECT COUNT(*) as count FROM product_categories WHERE is_active = 1");
        return (int)($result['count'] ?? 0);
    }
}
