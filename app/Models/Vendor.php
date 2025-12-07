<?php

namespace App\Models;

use App\Core\Database;

class Vendor
{
    public static function all(int $limit = 50, int $offset = 0): array
    {
        return Database::fetchAll(
            "SELECT * FROM vendors WHERE is_active = 1 ORDER BY name ASC LIMIT ? OFFSET ?",
            [$limit, $offset]
        ) ?? [];
    }
    
    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM vendors WHERE id = ? AND is_active = 1",
            [$id]
        );
    }
    
    public static function create(array $data): int
    {
        Database::query(
            "INSERT INTO vendors (name, contact_name, email, phone, website, 
                address_line1, address_line2, city, state, postal_code, country,
                payment_terms, notes, is_active) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['name'],
                $data['contact_name'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['website'] ?? null,
                $data['address_line1'] ?? null,
                $data['address_line2'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['postal_code'] ?? null,
                $data['country'] ?? 'US',
                $data['payment_terms'] ?? null,
                $data['notes'] ?? null,
                $data['is_active'] ?? 1
            ]
        );
        
        return (int)Database::lastInsertId();
    }
    
    public static function update(int $id, array $data): bool
    {
        Database::query(
            "UPDATE vendors SET 
                name = ?, contact_name = ?, email = ?, phone = ?, website = ?,
                address_line1 = ?, address_line2 = ?, city = ?, state = ?, 
                postal_code = ?, country = ?, payment_terms = ?, notes = ?,
                is_active = ?, updated_at = NOW()
             WHERE id = ?",
            [
                $data['name'],
                $data['contact_name'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['website'] ?? null,
                $data['address_line1'] ?? null,
                $data['address_line2'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['postal_code'] ?? null,
                $data['country'] ?? 'US',
                $data['payment_terms'] ?? null,
                $data['notes'] ?? null,
                $data['is_active'] ?? 1,
                $id
            ]
        );
        
        return true;
    }
    
    public static function delete(int $id): bool
    {
        Database::query(
            "UPDATE vendors SET is_active = 0, updated_at = NOW() WHERE id = ?",
            [$id]
        );
        
        return true;
    }
    
    public static function count(): int
    {
        $result = Database::fetchOne("SELECT COUNT(*) as count FROM vendors WHERE is_active = 1");
        return (int)($result['count'] ?? 0);
    }
}
