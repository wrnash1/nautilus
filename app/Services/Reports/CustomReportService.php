<?php

namespace App\Services\Reports;

use App\Core\Database;
use PDO;
use App\Core\Logger;
use Exception;

/**
 * Custom Report Builder Service
 * Allows users to create, save, and run custom reports
 */
class CustomReportService
{
    private PDO $db;
    private Logger $logger;

    // Available tables for reporting
    private array $availableTables = [
        'transactions' => 'Sales Transactions',
        'customers' => 'Customers',
        'products' => 'Products',
        'rental_reservations' => 'Rental Reservations',
        'course_enrollments' => 'Course Enrollments',
        'trip_bookings' => 'Trip Bookings',
        'work_orders' => 'Work Orders',
        'orders' => 'Online Orders',
        'staff' => 'Staff',
        'air_fills' => 'Air Fills'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new Logger();
    }

    /**
     * Get available tables for reporting
     */
    public function getAvailableTables(): array
    {
        return $this->availableTables;
    }

    /**
     * Get columns for a specific table
     */
    public function getTableColumns(string $table): array
    {
        if (!isset($this->availableTables[$table])) {
            throw new Exception('Invalid table');
        }

        $sql = "DESCRIBE {$table}";
        $stmt = $this->db->query($sql);
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $formatted = [];
        foreach ($columns as $column) {
            $formatted[] = [
                'name' => $column['Field'],
                'type' => $column['Type'],
                'nullable' => $column['Null'] === 'YES',
                'key' => $column['Key'],
                'default' => $column['Default']
            ];
        }

        return $formatted;
    }

    /**
     * Save a custom report definition
     */
    public function saveReport(array $reportData, ?int $userId = null): int
    {
        try {
            $sql = "INSERT INTO custom_reports
                    (name, description, table_name, columns, filters, grouping, sorting,
                     chart_type, is_public, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $reportData['name'],
                $reportData['description'] ?? null,
                $reportData['table_name'],
                json_encode($reportData['columns']),
                json_encode($reportData['filters'] ?? []),
                json_encode($reportData['grouping'] ?? []),
                json_encode($reportData['sorting'] ?? []),
                $reportData['chart_type'] ?? null,
                $reportData['is_public'] ?? false,
                $userId
            ]);

            $reportId = (int)$this->db->lastInsertId();

            $this->logger->info('Custom report saved', [
                'report_id' => $reportId,
                'name' => $reportData['name']
            ]);

            return $reportId;

        } catch (Exception $e) {
            $this->logger->error('Failed to save report', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Run a custom report
     */
    public function runReport(int $reportId, array $parameters = []): array
    {
        try {
            // Get report definition
            $report = $this->getReport($reportId);

            if (!$report) {
                throw new Exception('Report not found');
            }

            // Build and execute query
            $query = $this->buildQuery($report, $parameters);
            $results = $this->executeQuery($query);

            // Log report execution
            $this->logExecution($reportId);

            return [
                'success' => true,
                'report' => $report,
                'results' => $results,
                'row_count' => count($results)
            ];

        } catch (Exception $e) {
            $this->logger->error('Failed to run report', [
                'report_id' => $reportId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build SQL query from report definition
     */
    private function buildQuery(array $report, array $parameters = []): string
    {
        $columns = json_decode($report['columns'], true);
        $filters = json_decode($report['filters'], true) ?? [];
        $grouping = json_decode($report['grouping'], true) ?? [];
        $sorting = json_decode($report['sorting'], true) ?? [];

        // SELECT clause
        $selectColumns = [];
        foreach ($columns as $column) {
            if (isset($column['aggregate'])) {
                $selectColumns[] = "{$column['aggregate']}({$column['name']}) as {$column['alias']}";
            } else {
                $selectColumns[] = "{$column['name']} as {$column['alias']}";
            }
        }

        $sql = "SELECT " . implode(', ', $selectColumns);
        $sql .= " FROM {$report['table_name']}";

        // WHERE clause
        if (!empty($filters)) {
            $whereClauses = [];
            foreach ($filters as $filter) {
                $whereClauses[] = $this->buildFilterClause($filter, $parameters);
            }

            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }
        }

        // GROUP BY clause
        if (!empty($grouping)) {
            $sql .= " GROUP BY " . implode(', ', array_column($grouping, 'column'));
        }

        // ORDER BY clause
        if (!empty($sorting)) {
            $orderClauses = [];
            foreach ($sorting as $sort) {
                $orderClauses[] = "{$sort['column']} {$sort['direction']}";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        }

        // LIMIT clause (default to 1000 rows max)
        $limit = isset($parameters['limit']) ? (int)$parameters['limit'] : 1000;
        $sql .= " LIMIT {$limit}";

        return $sql;
    }

    /**
     * Build filter clause
     */
    private function buildFilterClause(array $filter, array $parameters): string
    {
        $column = $filter['column'];
        $operator = $filter['operator'];
        $value = $parameters[$column] ?? $filter['value'];

        switch ($operator) {
            case 'equals':
                return "{$column} = " . $this->quoteValue($value);
            case 'not_equals':
                return "{$column} != " . $this->quoteValue($value);
            case 'greater_than':
                return "{$column} > " . $this->quoteValue($value);
            case 'less_than':
                return "{$column} < " . $this->quoteValue($value);
            case 'greater_than_equal':
                return "{$column} >= " . $this->quoteValue($value);
            case 'less_than_equal':
                return "{$column} <= " . $this->quoteValue($value);
            case 'contains':
                return "{$column} LIKE " . $this->quoteValue("%{$value}%");
            case 'starts_with':
                return "{$column} LIKE " . $this->quoteValue("{$value}%");
            case 'ends_with':
                return "{$column} LIKE " . $this->quoteValue("%{$value}");
            case 'in':
                $values = is_array($value) ? $value : explode(',', $value);
                $quoted = array_map([$this, 'quoteValue'], $values);
                return "{$column} IN (" . implode(', ', $quoted) . ")";
            case 'between':
                return "{$column} BETWEEN " . $this->quoteValue($value['min']) . " AND " . $this->quoteValue($value['max']);
            case 'is_null':
                return "{$column} IS NULL";
            case 'is_not_null':
                return "{$column} IS NOT NULL";
            default:
                return "1=1";
        }
    }

    /**
     * Quote value for SQL
     */
    private function quoteValue($value): string
    {
        if (is_numeric($value)) {
            return $value;
        }
        return $this->db->quote($value);
    }

    /**
     * Execute query
     */
    private function executeQuery(string $sql): array
    {
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get report by ID
     */
    public function getReport(int $reportId): ?array
    {
        $sql = "SELECT * FROM custom_reports WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reportId]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get all reports (optionally filtered by user)
     */
    public function getAllReports(?int $userId = null, bool $includePublic = true): array
    {
        $sql = "SELECT * FROM custom_reports WHERE 1=1";

        $params = [];

        if ($userId) {
            if ($includePublic) {
                $sql .= " AND (created_by = ? OR is_public = 1)";
                $params[] = $userId;
            } else {
                $sql .= " AND created_by = ?";
                $params[] = $userId;
            }
        } elseif ($includePublic) {
            $sql .= " AND is_public = 1";
        }

        $sql .= " ORDER BY name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Delete report
     */
    public function deleteReport(int $reportId, ?int $userId = null): bool
    {
        try {
            $sql = "DELETE FROM custom_reports WHERE id = ?";

            if ($userId) {
                $sql .= " AND created_by = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$reportId, $userId]);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$reportId]);
            }

            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            $this->logger->error('Failed to delete report', [
                'report_id' => $reportId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Update report
     */
    public function updateReport(int $reportId, array $reportData, ?int $userId = null): bool
    {
        try {
            $sql = "UPDATE custom_reports SET
                    name = ?, description = ?, columns = ?, filters = ?,
                    grouping = ?, sorting = ?, chart_type = ?, is_public = ?,
                    updated_at = NOW()
                    WHERE id = ?";

            $params = [
                $reportData['name'],
                $reportData['description'] ?? null,
                json_encode($reportData['columns']),
                json_encode($reportData['filters'] ?? []),
                json_encode($reportData['grouping'] ?? []),
                json_encode($reportData['sorting'] ?? []),
                $reportData['chart_type'] ?? null,
                $reportData['is_public'] ?? false,
                $reportId
            ];

            if ($userId) {
                $sql .= " AND created_by = ?";
                $params[] = $userId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            $this->logger->error('Failed to update report', [
                'report_id' => $reportId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Log report execution
     */
    private function logExecution(int $reportId): void
    {
        $sql = "INSERT INTO report_executions (report_id, executed_by, executed_at)
                VALUES (?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reportId, $_SESSION['user_id'] ?? null]);
    }

    /**
     * Get report execution history
     */
    public function getExecutionHistory(int $reportId, int $limit = 50): array
    {
        $sql = "SELECT re.*, u.username
                FROM report_executions re
                LEFT JOIN users u ON re.executed_by = u.id
                WHERE re.report_id = ?
                ORDER BY re.executed_at DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reportId, $limit]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Export report results to CSV
     */
    public function exportToCSV(array $results, array $report): string
    {
        $filename = 'report_' . time() . '.csv';
        $filepath = BASE_PATH . '/storage/exports/' . $filename;

        $fp = fopen($filepath, 'w');

        // Write header
        if (!empty($results)) {
            fputcsv($fp, array_keys($results[0]));

            // Write data
            foreach ($results as $row) {
                fputcsv($fp, $row);
            }
        }

        fclose($fp);

        return $filepath;
    }

    /**
     * Schedule report for automatic execution
     */
    public function scheduleReport(int $reportId, array $schedule): int
    {
        $sql = "INSERT INTO scheduled_reports
                (report_id, frequency, schedule_time, recipients, format, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, 1, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $reportId,
            $schedule['frequency'], // daily, weekly, monthly
            $schedule['time'] ?? '09:00:00',
            json_encode($schedule['recipients'] ?? []),
            $schedule['format'] ?? 'csv'
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Get scheduled reports
     */
    public function getScheduledReports(): array
    {
        $sql = "SELECT sr.*, cr.name as report_name
                FROM scheduled_reports sr
                JOIN custom_reports cr ON sr.report_id = cr.id
                WHERE sr.is_active = 1
                ORDER BY sr.schedule_time";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
