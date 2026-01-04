<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\DatabaseManager;
use App\Core\Database;

class User extends Model
{
    protected $table = 'users'; // Explicitly defining table
    protected $guarded = ['id']; // Allow mass assignment for everything except ID

    // Define relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    // Static wrappers for backward compatibility, returning Model instance (which implements ArrayAccess)
    public static function find($id)
    {
        return static::with('roles')->find($id);
    }

    public static function findByEmail(string $email)
    {
        return static::with('roles')->where('email', $email)->first();
    }

    public static function findByUsername(string $username)
    {
        return static::with('roles')->where('username', $username)->first();
    }

    // Helper to access flattened role_id for compatibility
    public function getRoleIdAttribute()
    {
        return $this->roles->first()->id ?? null;
    }

    public function getRoleNameAttribute()
    {
        return $this->roles->first()->name ?? null;
    }

    public static function hasPermission(int $userId, string $permission): bool
    {
        try {
            // Check if user is Super Admin (role_id = 1) - they have all permissions
            $adminCheck = Database::fetchOne(
                "SELECT COUNT(*) as is_admin FROM user_roles WHERE user_id = ? AND role_id = 1",
                [$userId]
            );
            if (($adminCheck['is_admin'] ?? 0) > 0) {
                return true; // Super Admin has all permissions
            }
        } catch (\Throwable $e) {
            // Continue with regular permission check
        }

        // Use raw SQL for reliability - avoids Eloquent eager-loading issues
        // Note: permissions table uses 'name' column for the permission code
        $sql = "
            SELECT COUNT(*) as has_perm
            FROM users u
            INNER JOIN user_roles ur ON u.id = ur.user_id
            INNER JOIN role_permissions rp ON ur.role_id = rp.role_id
            INNER JOIN permissions p ON rp.permission_id = p.id
            WHERE u.id = ? AND p.name = ?
        ";

        try {
            $result = Database::fetchOne($sql, [$userId, $permission]);
            return ($result['has_perm'] ?? 0) > 0;
        } catch (\Throwable $e) {
            // Log error but don't crash - fail closed (no permission)
            error_log("Permission check failed: " . $e->getMessage());
            return false;
        }
    }

    public static function updateLastLogin(int $userId): void
    {
        static::where('id', $userId)->update(['last_login_at' => new \DateTime()]);
    }
}
