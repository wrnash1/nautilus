<?php

namespace App\Models;

use App\Core\Database;

class Customer
{
    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM customers WHERE id = ?",
            [$id]
        );
    }
    
    public static function search(string $query): array
    {
        $sql = "SELECT * FROM customers 
                WHERE MATCH(first_name, last_name, company_name, email) AGAINST(? IN BOOLEAN MODE)
                OR phone LIKE ? OR email LIKE ?
                LIMIT 50";
        
        $searchTerm = "%{$query}%";
        return Database::fetchAll($sql, [$query, $searchTerm, $searchTerm]);
    }
    
    public static function create(array $data): int
    {
        
        return 0;
    }
}
