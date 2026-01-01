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
        // Use Eloquent to check permission
        // Assuming Role model has permissions relationship
        $user = static::with('roles.permissions')->find($userId);
        if (!$user)
            return false;

        foreach ($user->roles as $role) {
            foreach ($role->permissions as $perm) {
                if ($perm->permission_code === $permission) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function updateLastLogin(int $userId): void
    {
        static::where('id', $userId)->update(['last_login_at' => new \DateTime()]);
    }
}
