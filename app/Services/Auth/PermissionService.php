<?php

namespace App\Services\Auth;

use App\Core\Database;
use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;

/**
 * Permission Service
 *
 * Comprehensive RBAC (Role-Based Access Control) management
 */
class PermissionService
{
    private Logger $logger;
    private static ?array $userPermissionsCache = null;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(int $userId, string $permissionCode): bool
    {
        try {
            // Load permissions if not cached
            if (self::$userPermissionsCache === null || !isset(self::$userPermissionsCache[$userId])) {
                $this->loadUserPermissions($userId);
            }

            return in_array($permissionCode, self::$userPermissionsCache[$userId] ?? []);

        } catch (\Exception $e) {
            $this->logger->error('Permission check failed', [
                'user_id' => $userId,
                'permission' => $permissionCode,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(int $userId, array $permissionCodes): bool
    {
        foreach ($permissionCodes as $code) {
            if ($this->hasPermission($userId, $code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(int $userId, array $permissionCodes): bool
    {
        foreach ($permissionCodes as $code) {
            if (!$this->hasPermission($userId, $code)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Require permission (throw exception if not granted)
     */
    public function requirePermission(int $userId, string $permissionCode): void
    {
        if (!$this->hasPermission($userId, $permissionCode)) {
            throw new \Exception("Permission denied: {$permissionCode}");
        }
    }

    /**
     * Load all permissions for a user
     */
    private function loadUserPermissions(int $userId): void
    {
        $permissions = [];

        // Get permissions from roles
        $rolePermissions = Database::fetchAll(
            "SELECT DISTINCT p.permission_code
             FROM permissions p
             JOIN role_permissions rp ON p.id = rp.permission_id
             JOIN roles r ON rp.role_id = r.id
             JOIN user_roles ur ON r.id = ur.role_id
             WHERE ur.user_id = ?
             AND p.is_active = 1
             AND r.is_active = 1
             AND (ur.expires_at IS NULL OR ur.expires_at > NOW())",
            [$userId]
        );

        foreach ($rolePermissions as $perm) {
            $permissions[] = $perm['permission_code'];
        }

        // Get direct user permissions (can override role permissions)
        $userPermissions = Database::fetchAll(
            "SELECT p.permission_code, up.is_granted
             FROM permissions p
             JOIN user_permissions up ON p.id = up.permission_id
             WHERE up.user_id = ?
             AND p.is_active = 1
             AND (up.expires_at IS NULL OR up.expires_at > NOW())",
            [$userId]
        );

        foreach ($userPermissions as $perm) {
            if ($perm['is_granted']) {
                // Add if granted
                if (!in_array($perm['permission_code'], $permissions)) {
                    $permissions[] = $perm['permission_code'];
                }
            } else {
                // Remove if explicitly denied
                $permissions = array_diff($permissions, [$perm['permission_code']]);
            }
        }

        self::$userPermissionsCache[$userId] = $permissions;
    }

    /**
     * Get all permissions for a user
     */
    public function getUserPermissions(int $userId): array
    {
        $this->loadUserPermissions($userId);
        return self::$userPermissionsCache[$userId] ?? [];
    }

    /**
     * Assign role to user
     */
    public function assignRole(int $userId, int $roleId, ??int $assignedBy = null, ??string $expiresAt = null): bool
    {
        try {
            Database::query(
                "INSERT INTO user_roles (user_id, role_id, assigned_by, expires_at)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE expires_at = VALUES(expires_at)",
                [$userId, $roleId, $assignedBy, $expiresAt]
            );

            // Clear cache
            unset(self::$userPermissionsCache[$userId]);

            // Log the action
            $this->logPermissionAction('role_assigned', $userId, null, $roleId, $assignedBy);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Assign role failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Remove role from user
     */
    public function removeRole(int $userId, int $roleId): bool
    {
        try {
            Database::query(
                "DELETE FROM user_roles WHERE user_id = ? AND role_id = ?",
                [$userId, $roleId]
            );

            // Clear cache
            unset(self::$userPermissionsCache[$userId]);

            $this->logPermissionAction('role_removed', $userId, null, $roleId);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Remove role failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Grant direct permission to user
     */
    public function grantPermission(int $userId, string $permissionCode, ??int $grantedBy = null, ?string $reason = null, ??string $expiresAt = null): bool
    {
        try {
            // Get permission ID
            $permission = Database::fetchOne(
                "SELECT id FROM permissions WHERE permission_code = ?",
                [$permissionCode]
            );

            if (!$permission) {
                throw new \Exception("Permission not found: {$permissionCode}");
            }

            Database::query(
                "INSERT INTO user_permissions (user_id, permission_id, is_granted, granted_by, reason, expires_at)
                 VALUES (?, ?, 1, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE is_granted = 1, granted_by = VALUES(granted_by), reason = VALUES(reason), expires_at = VALUES(expires_at)",
                [$userId, $permission['id'], $grantedBy, $reason, $expiresAt]
            );

            // Clear cache
            unset(self::$userPermissionsCache[$userId]);

            $this->logPermissionAction('permission_granted', $userId, $permissionCode, null, $grantedBy);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Grant permission failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Revoke direct permission from user
     */
    public function revokePermission(int $userId, string $permissionCode): bool
    {
        try {
            $permission = Database::fetchOne(
                "SELECT id FROM permissions WHERE permission_code = ?",
                [$permissionCode]
            );

            if (!$permission) {
                return false;
            }

            Database::query(
                "DELETE FROM user_permissions WHERE user_id = ? AND permission_id = ?",
                [$userId, $permission['id']]
            );

            // Clear cache
            unset(self::$userPermissionsCache[$userId]);

            $this->logPermissionAction('permission_revoked', $userId, $permissionCode);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Revoke permission failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Create a new role
     */
    public function createRole(string $roleName, string $roleCode, ??string $description = null, array $permissions = []): array
    {
        try {
            $tenantId = TenantMiddleware::getCurrentTenantId();

            // Create role
            $roleId = TenantDatabase::insertTenant('roles', [
                'role_name' => $roleName,
                'role_code' => $roleCode,
                'description' => $description,
                'is_system_role' => false,
                'is_active' => true
            ]);

            // Assign permissions to role
            foreach ($permissions as $permissionCode) {
                $this->addPermissionToRole($roleId, $permissionCode);
            }

            return [
                'success' => true,
                'role_id' => $roleId,
                'message' => 'Role created successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Create role failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Add permission to role
     */
    public function addPermissionToRole(int $roleId, string $permissionCode): bool
    {
        try {
            $permission = Database::fetchOne(
                "SELECT id FROM permissions WHERE permission_code = ?",
                [$permissionCode]
            );

            if (!$permission) {
                throw new \Exception("Permission not found: {$permissionCode}");
            }

            Database::query(
                "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)",
                [$roleId, $permission['id']]
            );

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Add permission to role failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Remove permission from role
     */
    public function removePermissionFromRole(int $roleId, string $permissionCode): bool
    {
        try {
            $permission = Database::fetchOne(
                "SELECT id FROM permissions WHERE permission_code = ?",
                [$permissionCode]
            );

            if (!$permission) {
                return false;
            }

            Database::query(
                "DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?",
                [$roleId, $permission['id']]
            );

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Remove permission from role failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get all roles for tenant
     */
    public function getTenantRoles(): array
    {
        $tenantId = TenantMiddleware::getCurrentTenantId();

        return TenantDatabase::fetchAllTenant(
            "SELECT r.*, COUNT(DISTINCT ur.user_id) as user_count
             FROM roles r
             LEFT JOIN user_roles ur ON r.id = ur.role_id
             WHERE r.is_active = 1
             GROUP BY r.id
             ORDER BY r.role_name"
        ) ?? [];
    }

    /**
     * Get all permissions by category
     */
    public function getAllPermissions(): array
    {
        $permissions = Database::fetchAll(
            "SELECT * FROM permissions WHERE is_active = 1 ORDER BY category, permission_name"
        ) ?? [];

        // Group by category
        $grouped = [];
        foreach ($permissions as $perm) {
            $category = $perm['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $perm;
        }

        return $grouped;
    }

    /**
     * Get role permissions
     */
    public function getRolePermissions(int $roleId): array
    {
        return Database::fetchAll(
            "SELECT p.* FROM permissions p
             JOIN role_permissions rp ON p.id = rp.permission_id
             WHERE rp.role_id = ?
             AND p.is_active = 1
             ORDER BY p.category, p.permission_name",
            [$roleId]
        ) ?? [];
    }

    /**
     * Log permission action for audit
     */
    private function logPermissionAction(string $action, int $userId, ??string $permissionCode = null, ?int $roleId = null, ??int $grantedBy = null): void
    {
        $tenantId = TenantMiddleware::getCurrentTenantId();

        Database::query(
            "INSERT INTO permission_audit_log (
                tenant_id, user_id, action, permission_code, role_id,
                granted_to_user_id, ip_address, user_agent
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $tenantId,
                $grantedBy ?? $userId,
                $action,
                $permissionCode,
                $roleId,
                $userId,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]
        );
    }

    /**
     * Clear permissions cache for user
     */
    public static function clearCache(??int $userId = null): void
    {
        if ($userId === null) {
            self::$userPermissionsCache = null;
        } else {
            unset(self::$userPermissionsCache[$userId]);
        }
    }
}
