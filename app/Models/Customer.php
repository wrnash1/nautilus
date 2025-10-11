<?php

namespace App\Models;

use App\Core\Database;

class Customer
{
    public static function all(int $limit = 50, int $offset = 0): array
    {
        return Database::fetchAll(
            "SELECT * FROM customers 
             WHERE is_active = 1
             ORDER BY first_name ASC, last_name ASC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        ) ?? [];
    }
    
    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM customers WHERE id = ? AND is_active = 1",
            [$id]
        );
    }
    
    public static function search(string $query, int $limit = 20): array
    {
        $searchTerm = "%{$query}%";
        return Database::fetchAll(
            "SELECT * FROM customers 
             WHERE is_active = 1
             AND (first_name LIKE ? OR last_name LIKE ? OR company_name LIKE ? 
                  OR email LIKE ? OR phone LIKE ?)
             ORDER BY first_name ASC, last_name ASC
             LIMIT ?",
            [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]
        ) ?? [];
    }
    
    public static function create(array $data): int
    {
        Database::query(
            "INSERT INTO customers (
                customer_type, first_name, last_name, email, phone,
                company_name, date_of_birth, address_line1, address_line2,
                city, state_province, postal_code, country, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['customer_type'] ?? 'individual',
                $data['first_name'],
                $data['last_name'],
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['company_name'] ?? null,
                $data['date_of_birth'] ?? null,
                $data['address_line1'] ?? null,
                $data['address_line2'] ?? null,
                $data['city'] ?? null,
                $data['state_province'] ?? null,
                $data['postal_code'] ?? null,
                $data['country'] ?? 'USA',
                $data['is_active'] ?? 1
            ]
        );
        
        return (int)Database::lastInsertId();
    }
    
    public static function update(int $id, array $data): bool
    {
        Database::query(
            "UPDATE customers SET 
                customer_type = ?, first_name = ?, last_name = ?, email = ?,
                phone = ?, company_name = ?, date_of_birth = ?,
                address_line1 = ?, address_line2 = ?, city = ?,
                state_province = ?, postal_code = ?, country = ?,
                is_active = ?, updated_at = NOW()
             WHERE id = ?",
            [
                $data['customer_type'] ?? 'individual',
                $data['first_name'],
                $data['last_name'],
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['company_name'] ?? null,
                $data['date_of_birth'] ?? null,
                $data['address_line1'] ?? null,
                $data['address_line2'] ?? null,
                $data['city'] ?? null,
                $data['state_province'] ?? null,
                $data['postal_code'] ?? null,
                $data['country'] ?? 'USA',
                $data['is_active'] ?? 1,
                $id
            ]
        );
        
        return true;
    }
    
    public static function count(): int
    {
        $result = Database::fetchOne("SELECT COUNT(*) as count FROM customers WHERE is_active = 1");
        return (int)($result['count'] ?? 0);
    }
}
