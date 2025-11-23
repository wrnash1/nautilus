<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

class RoleController extends Controller
{
    /**
     * Display list of all roles
     */
    public function index(): void
    {
        // Fetch all roles with permission counts
        $roles = Database::fetchAll("
            SELECT r.*,
                   COUNT(DISTINCT rp.permission_id) as permission_count,
                   COUNT(DISTINCT u.id) as user_count
            FROM roles r
            LEFT JOIN role_permissions rp ON r.id = rp.role_id
            LEFT JOIN users u ON u.role_id = r.id
            WHERE r.tenant_id = ?
            GROUP BY r.id
            ORDER BY r.name ASC
        ", [$_SESSION['tenant_id'] ?? 1]) ?? [];

        $data = [
            'page_title' => 'Role Management',
            'roles' => $roles
        ];

        $this->view('admin/roles/index', $data);
    }

    /**
     * Show form to create new role
     */
    public function create(): void
    {
        // Fetch all available permissions
        $permissions = Database::fetchAll("
            SELECT * FROM permissions
            ORDER BY category, name ASC
        ") ?? [];

        // Group permissions by category
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $category = $permission['category'] ?? 'Other';
            if (!isset($groupedPermissions[$category])) {
                $groupedPermissions[$category] = [];
            }
            $groupedPermissions[$category][] = $permission;
        }

        $data = [
            'page_title' => 'Create Role',
            'permissions' => $groupedPermissions
        ];

        $this->view('admin/roles/create', $data);
    }

    /**
     * Store new role
     */
    public function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];

        // Validation
        if (empty($name)) {
            $_SESSION['error'] = 'Role name is required';
            header('Location: /store/admin/roles/create');
            exit;
        }

        // Check if role name already exists
        $existing = Database::fetchOne("
            SELECT id FROM roles
            WHERE tenant_id = ? AND name = ?
        ", [$_SESSION['tenant_id'] ?? 1, $name]);

        if ($existing) {
            $_SESSION['error'] = 'A role with this name already exists';
            header('Location: /store/admin/roles/create');
            exit;
        }

        // Insert role
        Database::query("
            INSERT INTO roles (tenant_id, name, description, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ", [$_SESSION['tenant_id'] ?? 1, $name, $description]);

        $roleId = Database::lastInsertId();

        // Assign permissions
        if (!empty($permissions) && is_array($permissions)) {
            foreach ($permissions as $permissionId) {
                Database::query("
                    INSERT INTO role_permissions (role_id, permission_id, created_at)
                    VALUES (?, ?, NOW())
                ", [$roleId, (int)$permissionId]);
            }
        }

        $_SESSION['success'] = 'Role created successfully';
        header('Location: /store/admin/roles');
        exit;
    }

    /**
     * Show role details
     */
    public function show(int $id): void
    {
        $role = Database::fetchOne("
            SELECT * FROM roles
            WHERE id = ? AND tenant_id = ?
        ", [$id, $_SESSION['tenant_id'] ?? 1]);

        if (!$role) {
            $_SESSION['error'] = 'Role not found';
            header('Location: /store/admin/roles');
            exit;
        }

        // Fetch assigned permissions
        $permissions = Database::fetchAll("
            SELECT p.*
            FROM permissions p
            INNER JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
            ORDER BY p.category, p.name
        ", [$id]) ?? [];

        // Group permissions by category
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $category = $permission['category'] ?? 'Other';
            if (!isset($groupedPermissions[$category])) {
                $groupedPermissions[$category] = [];
            }
            $groupedPermissions[$category][] = $permission;
        }

        // Fetch users with this role
        $users = Database::fetchAll("
            SELECT id, first_name, last_name, email
            FROM users
            WHERE role_id = ?
            ORDER BY last_name, first_name
        ", [$id]) ?? [];

        $data = [
            'page_title' => 'Role Details: ' . $role['name'],
            'role' => $role,
            'permissions' => $groupedPermissions,
            'users' => $users
        ];

        $this->view('admin/roles/show', $data);
    }

    /**
     * Show form to edit role
     */
    public function edit(int $id): void
    {
        $role = Database::fetchOne("
            SELECT * FROM roles
            WHERE id = ? AND tenant_id = ?
        ", [$id, $_SESSION['tenant_id'] ?? 1]);

        if (!$role) {
            $_SESSION['error'] = 'Role not found';
            header('Location: /store/admin/roles');
            exit;
        }

        // Fetch all available permissions
        $permissions = Database::fetchAll("
            SELECT * FROM permissions
            ORDER BY category, name ASC
        ") ?? [];

        // Fetch currently assigned permissions
        $assignedPermissions = Database::fetchAll("
            SELECT permission_id
            FROM role_permissions
            WHERE role_id = ?
        ", [$id]) ?? [];

        $assignedPermissionIds = array_column($assignedPermissions, 'permission_id');

        // Group permissions by category
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $category = $permission['category'] ?? 'Other';
            if (!isset($groupedPermissions[$category])) {
                $groupedPermissions[$category] = [];
            }
            $permission['is_assigned'] = in_array($permission['id'], $assignedPermissionIds);
            $groupedPermissions[$category][] = $permission;
        }

        $data = [
            'page_title' => 'Edit Role: ' . $role['name'],
            'role' => $role,
            'permissions' => $groupedPermissions
        ];

        $this->view('admin/roles/edit', $data);
    }

    /**
     * Update role
     */
    public function update(int $id): void
    {
        $role = Database::fetchOne("
            SELECT * FROM roles
            WHERE id = ? AND tenant_id = ?
        ", [$id, $_SESSION['tenant_id'] ?? 1]);

        if (!$role) {
            $_SESSION['error'] = 'Role not found';
            header('Location: /store/admin/roles');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];

        // Validation
        if (empty($name)) {
            $_SESSION['error'] = 'Role name is required';
            header('Location: /store/admin/roles/' . $id . '/edit');
            exit;
        }

        // Check if role name already exists (excluding current role)
        $existing = Database::fetchOne("
            SELECT id FROM roles
            WHERE tenant_id = ? AND name = ? AND id != ?
        ", [$_SESSION['tenant_id'] ?? 1, $name, $id]);

        if ($existing) {
            $_SESSION['error'] = 'A role with this name already exists';
            header('Location: /store/admin/roles/' . $id . '/edit');
            exit;
        }

        // Update role
        Database::query("
            UPDATE roles
            SET name = ?, description = ?, updated_at = NOW()
            WHERE id = ?
        ", [$name, $description, $id]);

        // Delete existing permissions
        Database::query("DELETE FROM role_permissions WHERE role_id = ?", [$id]);

        // Assign new permissions
        if (!empty($permissions) && is_array($permissions)) {
            foreach ($permissions as $permissionId) {
                Database::query("
                    INSERT INTO role_permissions (role_id, permission_id, created_at)
                    VALUES (?, ?, NOW())
                ", [$id, (int)$permissionId]);
            }
        }

        $_SESSION['success'] = 'Role updated successfully';
        header('Location: /store/admin/roles/' . $id);
        exit;
    }

    /**
     * Delete role
     */
    public function destroy(int $id): void
    {
        $role = Database::fetchOne("
            SELECT * FROM roles
            WHERE id = ? AND tenant_id = ?
        ", [$id, $_SESSION['tenant_id'] ?? 1]);

        if (!$role) {
            $_SESSION['error'] = 'Role not found';
            header('Location: /store/admin/roles');
            exit;
        }

        // Check if any users have this role
        $userCount = Database::fetchOne("
            SELECT COUNT(*) as count
            FROM users
            WHERE role_id = ?
        ", [$id]);

        if ($userCount && $userCount['count'] > 0) {
            $_SESSION['error'] = 'Cannot delete role: ' . $userCount['count'] . ' user(s) are assigned to this role';
            header('Location: /store/admin/roles/' . $id);
            exit;
        }

        // Delete role permissions first
        Database::query("DELETE FROM role_permissions WHERE role_id = ?", [$id]);

        // Delete role
        Database::query("DELETE FROM roles WHERE id = ?", [$id]);

        $_SESSION['success'] = 'Role deleted successfully';
        header('Location: /store/admin/roles');
        exit;
    }
}
