<?php

namespace App\Services\Admin;

use App\Core\Database;

class UserService
{
    public function getUsers(string $search = '', string $role = '', string $status = ''): array
    {
        $params = [];
        $where = [];

        if ($search) {
            $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if ($role) {
            $where[] = "u.role_id = ?";
            $params[] = $role;
        }

        if ($status !== '') {
            $where[] = "u.is_active = ?";
            $params[] = $status;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT u.*, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                $whereClause
                ORDER BY u.created_at DESC";

        return Database::fetchAll($sql, $params) ?? [];
    }

    public function getUserById(int $id): ?array
    {
        $sql = "SELECT u.*, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = ?";

        return Database::fetchOne($sql, [$id]);
    }

    public function createUser(array $data): int
    {
        // Validate email unique
        $exists = Database::fetchOne("SELECT id FROM users WHERE email = ?", [$data['email']]);
        if ($exists) {
            throw new \Exception('Email address already exists');
        }

        // Hash password
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (
                    email, password_hash, first_name, last_name, phone,
                    role_id, is_active, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        Database::execute($sql, [
            $data['email'],
            $passwordHash,
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? '',
            $data['role_id'],
            $data['is_active'] ?? 1
        ]);

        $userId = Database::lastInsertId();

        logAudit('user', 'create', $userId, ['email' => $data['email']]);

        return $userId;
    }

    public function updateUser(int $id, array $data): bool
    {
        // Validate email unique (except current user)
        $exists = Database::fetchOne(
            "SELECT id FROM users WHERE email = ? AND id != ?",
            [$data['email'], $id]
        );
        if ($exists) {
            throw new \Exception('Email address already exists');
        }

        $params = [
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? '',
            $data['role_id'],
            $data['is_active'] ?? 1
        ];

        $sql = "UPDATE users SET
                email = ?, first_name = ?, last_name = ?, phone = ?,
                role_id = ?, is_active = ?";

        if (!empty($data['password'])) {
            $sql .= ", password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $result = Database::execute($sql, $params);

        logAudit('user', 'update', $id, $data);

        return $result;
    }

    public function deleteUser(int $id): bool
    {
        // Soft delete by deactivating
        $result = Database::execute("UPDATE users SET is_active = 0 WHERE id = ?", [$id]);

        logAudit('user', 'delete', $id);

        return $result;
    }

    public function resetUserPassword(int $id): string
    {
        // Generate random password
        $newPassword = bin2hex(random_bytes(8));
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

        Database::execute(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            [$passwordHash, $id]
        );

        logAudit('user', 'password_reset', $id);

        return $newPassword;
    }

    public function toggleUserStatus(int $id): bool
    {
        $user = $this->getUserById($id);
        $newStatus = $user['is_active'] ? 0 : 1;

        $result = Database::execute(
            "UPDATE users SET is_active = ? WHERE id = ?",
            [$newStatus, $id]
        );

        logAudit('user', 'toggle_status', $id, ['new_status' => $newStatus]);

        return $result;
    }

    public function getUserActivity(int $id, int $limit = 20): array
    {
        $sql = "SELECT * FROM audit_logs
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?";

        return Database::fetchAll($sql, [$id, $limit]) ?? [];
    }
}
