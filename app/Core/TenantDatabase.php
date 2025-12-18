<?php

namespace App\Core;

use App\Middleware\TenantMiddleware;

/**
 * Tenant-Aware Database Class
 *
 * Extends the base Database class to automatically scope queries by tenant_id
 * Ensures data isolation between tenants
 */
class TenantDatabase extends Database
{
    /**
     * Tables that should be scoped by tenant
     */
    private static array $tenantScopedTables = [
        'users',
        'customers',
        'products',
        'product_categories',
        'transactions',
        'transaction_items',
        'courses',
        'course_enrollments',
        'equipment',
        'equipment_rentals',
        'inventory_adjustments',
        'notification_log',
        'scheduled_notifications',
        'report_schedules'
    ];

    /**
     * Execute a query with automatic tenant scoping
     */
    public static function queryTenantScoped(string $sql, array $params = []): \PDOStatement
    {
        $tenantId = TenantMiddleware::getCurrentTenantId();

        if (!$tenantId) {
            throw new \Exception('Tenant context required for this operation');
        }

        // Add tenant_id condition to WHERE clause
        $sql = self::addTenantScope($sql, $tenantId);

        return parent::query($sql, $params);
    }

    /**
     * Fetch one record with tenant scoping
     */
    public static function fetchOneTenant(string $sql, array $params = []): ?array
    {
        $tenantId = TenantMiddleware::getCurrentTenantId();

        if (!$tenantId) {
            throw new \Exception('Tenant context required for this operation');
        }

        $sql = self::addTenantScope($sql, $tenantId);

        return parent::fetchOne($sql, $params);
    }

    /**
     * Fetch all records with tenant scoping
     */
    public static function fetchAllTenant(string $sql, array $params = []): ?array
    {
        $tenantId = TenantMiddleware::getCurrentTenantId();

        if (!$tenantId) {
            throw new \Exception('Tenant context required for this operation');
        }

        $sql = self::addTenantScope($sql, $tenantId);

        return parent::fetchAll($sql, $params);
    }

    /**
     * Insert with automatic tenant_id
     */
    public static function insertTenant(string $table, array $data): int
    {
        $tenantId = TenantMiddleware::getCurrentTenantId();

        if (!$tenantId) {
            throw new \Exception('Tenant context required for this operation');
        }

        // Add tenant_id to data if table is tenant-scoped
        if (in_array($table, self::$tenantScopedTables)) {
            $data['tenant_id'] = $tenantId;
        }

        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        parent::query($sql, array_values($data));

        return parent::lastInsertId();
    }

    /**
     * Update with tenant verification
     */
    public static function updateTenant(string $table, array $data, string $where, array $whereParams = []): int
    {
        $tenantId = TenantMiddleware::getCurrentTenantId();

        if (!$tenantId) {
            throw new \Exception('Tenant context required for this operation');
        }

        $sets = [];
        $params = [];

        foreach ($data as $column => $value) {
            $sets[] = "{$column} = ?";
            $params[] = $value;
        }

        // Add tenant_id to WHERE clause for tenant-scoped tables
        if (in_array($table, self::$tenantScopedTables)) {
            $where .= " AND tenant_id = ?";
            $whereParams[] = $tenantId;
        }

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $table,
            implode(', ', $sets),
            $where
        );

        $params = array_merge($params, $whereParams);

        $stmt = parent::query($sql, $params);

        return $stmt->rowCount();
    }

    /**
     * Delete with tenant verification
     */
    public static function deleteTenant(string $table, string $where, array $whereParams = []): int
    {
        $tenantId = TenantMiddleware::getCurrentTenantId();

        if (!$tenantId) {
            throw new \Exception('Tenant context required for this operation');
        }

        // Add tenant_id to WHERE clause for tenant-scoped tables
        if (in_array($table, self::$tenantScopedTables)) {
            $where .= " AND tenant_id = ?";
            $whereParams[] = $tenantId;
        }

        $sql = sprintf("DELETE FROM %s WHERE %s", $table, $where);

        $stmt = parent::query($sql, $whereParams);

        return $stmt->rowCount();
    }

    /**
     * Add tenant scope to SQL query
     */
    private static function addTenantScope(string $sql, int $tenantId): string
    {
        // This is a simplified version
        // In production, you'd want a more sophisticated SQL parser

        // Detect which table is being queried
        $table = self::extractTableName($sql);

        if (!$table || !in_array($table, self::$tenantScopedTables)) {
            return $sql;
        }

        // Check if WHERE clause exists
        if (stripos($sql, 'WHERE') !== false) {
            // Add tenant_id to existing WHERE
            $sql = preg_replace(
                '/WHERE/i',
                "WHERE {$table}.tenant_id = {$tenantId} AND",
                $sql,
                1
            );
        } else {
            // Add WHERE clause with tenant_id
            // Find the position to insert (before ORDER BY, GROUP BY, LIMIT, etc.)
            $keywords = ['ORDER BY', 'GROUP BY', 'HAVING', 'LIMIT', 'OFFSET'];
            $insertPosition = strlen($sql);

            foreach ($keywords as $keyword) {
                $pos = stripos($sql, $keyword);
                if ($pos !== false && $pos < $insertPosition) {
                    $insertPosition = $pos;
                }
            }

            if ($insertPosition < strlen($sql)) {
                $sql = substr_replace(
                    $sql,
                    " WHERE {$table}.tenant_id = {$tenantId} ",
                    $insertPosition,
                    0
                );
            } else {
                $sql .= " WHERE {$table}.tenant_id = {$tenantId}";
            }
        }

        return $sql;
    }

    /**
     * Extract table name from SQL query
     */
    private static function extractTableName(string $sql): ?string
    {
        // Match SELECT ... FROM table or INSERT INTO table or UPDATE table
        if (preg_match('/FROM\s+([a-z_]+)/i', $sql, $matches)) {
            return $matches[1];
        }

        if (preg_match('/UPDATE\s+([a-z_]+)/i', $sql, $matches)) {
            return $matches[1];
        }

        if (preg_match('/INSERT\s+INTO\s+([a-z_]+)/i', $sql, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Check if current user can access tenant resource
     */
    public static function canAccessTenantResource(int $tenantId): bool
    {
        $currentTenantId = TenantMiddleware::getCurrentTenantId();

        return $currentTenantId === $tenantId;
    }

    /**
     * Get tables that are tenant-scoped
     */
    public static function getTenantScopedTables(): array
    {
        return self::$tenantScopedTables;
    }

    /**
     * Add a table to tenant scoping
     */
    public static function addTenantScopedTable(string $table): void
    {
        if (!in_array($table, self::$tenantScopedTables)) {
            self::$tenantScopedTables[] = $table;
        }
    }
}
