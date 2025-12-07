<?php

namespace App\Models;

use App\Core\Database;

class User
{
    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT u.*, r.id as role_id, r.name as role_name 
             FROM users u
             LEFT JOIN user_roles ur ON u.id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.id
             WHERE u.id = ? AND u.is_active = 1
             LIMIT 1",
            [$id]
        );
    }
    
    public static function findByEmail(string $email): ?array
    {
        return Database::fetchOne(
            "SELECT u.*, r.id as role_id, r.name as role_name 
             FROM users u
             LEFT JOIN user_roles ur ON u.id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.id
             WHERE u.email = ? AND u.is_active = 1
             LIMIT 1",
            [$email]
        );
    }
    
    public static function hasPermission(int $userId, string $permission): bool
    {
        $sql = "SELECT COUNT(*) as count FROM role_permissions rp
                INNER JOIN permissions p ON rp.permission_id = p.id
                INNER JOIN user_roles ur ON ur.role_id = rp.role_id
                WHERE ur.user_id = ? AND p.name = ?";
        
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
