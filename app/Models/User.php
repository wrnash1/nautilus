<?php

namespace App\Models;

use App\Core\Database;

class User
{
    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM users WHERE id = ? AND is_active = 1",
            [$id]
        );
    }
    
    public static function findByEmail(string $email): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM users WHERE email = ? AND is_active = 1",
            [$email]
        );
    }
    
    public static function hasPermission(int $userId, string $permission): bool
    {
        $sql = "SELECT COUNT(*) as count FROM role_permissions rp
                INNER JOIN permissions p ON rp.permission_id = p.id
                INNER JOIN users u ON u.role_id = rp.role_id
                WHERE u.id = ? AND p.name = ?";
        
        $result = Database::fetchOne($sql, [$userId, $permission]);
        return $result['count'] > 0;
    }
    
    public static function updateLastLogin(int $userId): void
    {
        Database::query(
            "UPDATE users SET last_login_at = NOW() WHERE id = ?",
            [$userId]
        );
    }
}
