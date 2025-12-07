<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Services\Audit\AuditService;

class AuditLogController
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    /**
     * Display audit logs
     */
    public function index()
    {
        // Check admin permission
        if (!Auth::hasPermission('admin.audit')) {
            $_SESSION['flash_error'] = 'You do not have permission to view audit logs';
            header('Location: /store/dashboard');
            exit;
        }

        // Get filters
        $filters = [
            'user_id' => !empty($_GET['user_id']) ? (int)$_GET['user_id'] : null,
            'action' => $_GET['action'] ?? null,
            'entity_type' => $_GET['entity_type'] ?? null,
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-t')
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $logs = $this->auditService->getLogs($filters, $perPage, $offset);
        $totalLogs = $this->auditService->getCount($filters);
        $totalPages = ceil($totalLogs / $perPage);

        // Get filter options
        $actions = $this->auditService->getActions();
        $entityTypes = $this->auditService->getEntityTypes();
        $users = $this->getAllUsers();

        require __DIR__ . '/../../Views/admin/audit/index.php';
    }

    /**
     * Show single audit log detail
     */
    public function show(int $id)
    {
        // Check admin permission
        if (!Auth::hasPermission('admin.audit')) {
            $_SESSION['flash_error'] = 'You do not have permission to view audit logs';
            header('Location: /store/dashboard');
            exit;
        }

        $log = $this->auditService->getById($id);

        if (!$log) {
            $_SESSION['flash_error'] = 'Audit log not found';
            header('Location: /store/admin/audit');
            exit;
        }

        require __DIR__ . '/../../Views/admin/audit/show.php';
    }

    /**
     * Show entity audit history
     */
    public function entityHistory(string $entityType, int $entityId)
    {
        // Check admin permission
        if (!Auth::hasPermission('admin.audit')) {
            $_SESSION['flash_error'] = 'You do not have permission to view audit logs';
            header('Location: /store/dashboard');
            exit;
        }

        $history = $this->auditService->getEntityHistory($entityType, $entityId);

        require __DIR__ . '/../../Views/admin/audit/entity_history.php';
    }

    /**
     * Show activity dashboard
     */
    public function activity()
    {
        // Check admin permission
        if (!Auth::hasPermission('admin.audit')) {
            $_SESSION['flash_error'] = 'You do not have permission to view activity reports';
            header('Location: /store/dashboard');
            exit;
        }

        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $userActivity = $this->auditService->getUserActivity($startDate, $endDate);
        $actionSummary = $this->auditService->getActionSummary($startDate, $endDate);

        require __DIR__ . '/../../Views/admin/audit/activity.php';
    }

    /**
     * Export audit logs as CSV
     */
    public function export()
    {
        // Check admin permission
        if (!Auth::hasPermission('admin.audit')) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }

        $filters = [
            'user_id' => !empty($_GET['user_id']) ? (int)$_GET['user_id'] : null,
            'action' => $_GET['action'] ?? null,
            'entity_type' => $_GET['entity_type'] ?? null,
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-t')
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        $logs = $this->auditService->getLogs($filters, 10000, 0); // Max 10k records

        $filename = 'audit_logs_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // CSV header
        fputcsv($output, ['ID', 'Date/Time', 'User', 'Action', 'Entity Type', 'Entity ID', 'IP Address', 'User Agent']);

        // CSV data
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['created_at'],
                $log['user_name'] ?? 'Unknown',
                $log['action'],
                $log['entity_type'] ?? '',
                $log['entity_id'] ?? '',
                $log['ip_address'] ?? '',
                $log['user_agent'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Cleanup old logs (admin only)
     */
    public function cleanup()
    {
        // Check admin permission
        if (!Auth::hasPermission('admin.audit')) {
            $_SESSION['flash_error'] = 'You do not have permission to perform this action';
            header('Location: /store/admin/audit');
            exit;
        }

        $daysOld = isset($_GET['days']) ? (int)$_GET['days'] : 365;

        if ($daysOld < 90) {
            $_SESSION['flash_error'] = 'Cannot delete logs less than 90 days old';
            header('Location: /store/admin/audit');
            exit;
        }

        $count = $this->auditService->deleteOldLogs($daysOld);

        $_SESSION['flash_success'] = "Deleted $count audit logs older than $daysOld days";
        header('Location: /store/admin/audit');
    }

    /**
     * Helper: Get all users
     */
    private function getAllUsers(): array
    {
        return Database::fetchAll(
            "SELECT id, CONCAT(first_name, ' ', last_name) as name, email
             FROM users
             WHERE is_active = 1
             ORDER BY first_name, last_name"
        ) ?? [];
    }
}
