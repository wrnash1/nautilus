<?php

namespace App\Controllers\Admin;

use App\Services\System\BackupService;
use App\Services\Audit\AuditService;
use App\Helpers\Auth;

/**
 * Backup Controller
 *
 * Manages database backups and restoration
 */
class BackupController
{
    private BackupService $backupService;
    private AuditService $auditService;

    public function __construct()
    {
        $this->backupService = new BackupService();
        $this->auditService = new AuditService();
    }

    /**
     * Display backup management page
     */
    public function index()
    {
        if (!Auth::check() || !Auth::hasPermission('backups.manage')) {
            redirect('/login');
            return;
        }

        $backups = $this->backupService->getBackups();
        $statistics = $this->backupService->getStatistics();

        require __DIR__ . '/../../Views/admin/backups/index.php';
    }

    /**
     * Create a new backup
     */
    public function create()
    {
        if (!Auth::check() || !Auth::hasPermission('backups.create')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $description = $_POST['description'] ?? '';
        $includeDocuments = isset($_POST['include_documents']) && $_POST['include_documents'] === '1';

        $filename = $this->backupService->createBackup($description, $includeDocuments);

        if ($filename) {
            // Log backup creation
            $this->auditService->log(
                Auth::userId(),
                'create',
                'backup',
                null,
                ['filename' => $filename, 'description' => $description]
            );

            if ($this->isAjaxRequest()) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Backup created successfully',
                    'filename' => $filename
                ]);
            } else {
                $_SESSION['success'] = 'Backup created successfully';
                redirect('/admin/backups');
            }
        } else {
            if ($this->isAjaxRequest()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Failed to create backup'], 500);
            } else {
                $_SESSION['error'] = 'Failed to create backup';
                redirect('/admin/backups');
            }
        }
    }

    /**
     * Restore from backup
     */
    public function restore(int $backupId)
    {
        if (!Auth::check() || !Auth::hasPermission('backups.restore')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        // Get backup filename
        $backups = $this->backupService->getBackups();
        $backup = array_filter($backups, fn($b) => $b['id'] == $backupId);
        $backup = reset($backup);

        if (!$backup) {
            return $this->jsonResponse(['success' => false, 'error' => 'Backup not found'], 404);
        }

        $success = $this->backupService->restoreBackup($backup['filename']);

        if ($success) {
            // Log restoration
            $this->auditService->log(
                Auth::userId(),
                'restore',
                'backup',
                $backupId,
                ['filename' => $backup['filename']]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Database restored successfully. Please refresh the page.'
            ]);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to restore backup'], 500);
        }
    }

    /**
     * Download backup
     */
    public function download(int $backupId)
    {
        if (!Auth::check() || !Auth::hasPermission('backups.download')) {
            http_response_code(403);
            echo "Unauthorized";
            exit;
        }

        // Get backup filename
        $backups = $this->backupService->getBackups();
        $backup = array_filter($backups, fn($b) => $b['id'] == $backupId);
        $backup = reset($backup);

        if (!$backup) {
            http_response_code(404);
            echo "Backup not found";
            exit;
        }

        // Log download
        $this->auditService->log(
            Auth::userId(),
            'download',
            'backup',
            $backupId,
            ['filename' => $backup['filename']]
        );

        $this->backupService->downloadBackup($backup['filename']);
    }

    /**
     * Delete backup
     */
    public function delete(int $backupId)
    {
        if (!Auth::check() || !Auth::hasPermission('backups.delete')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $success = $this->backupService->deleteBackup($backupId);

        if ($success) {
            // Log deletion
            $this->auditService->log(
                Auth::userId(),
                'delete',
                'backup',
                $backupId
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Backup deleted successfully'
            ]);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to delete backup'], 500);
        }
    }

    /**
     * Clean old backups
     */
    public function cleanOld()
    {
        if (!Auth::check() || !Auth::hasPermission('backups.manage')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $keepCount = (int)($_POST['keep_count'] ?? 10);
        $deletedCount = $this->backupService->cleanOldBackups($keepCount);

        // Log cleanup
        $this->auditService->log(
            Auth::userId(),
            'cleanup',
            'backup',
            null,
            ['deleted_count' => $deletedCount, 'kept' => $keepCount]
        );

        return $this->jsonResponse([
            'success' => true,
            'message' => "Deleted {$deletedCount} old backup(s)",
            'deleted_count' => $deletedCount
        ]);
    }

    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
