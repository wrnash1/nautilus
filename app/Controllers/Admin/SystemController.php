<?php

namespace App\Controllers\Admin;

use App\Services\System\UpdateService;
use App\Middleware\AuthMiddleware;

class SystemController
{
    private UpdateService $updateService;

    public function __construct()
    {
        $this->updateService = new UpdateService();
    }

    /**
     * Show update dashboard
     */
    public function index()
    {
        if (!hasPermission('admin.view')) { // Assuming 'admin.view' or similar super-admin permission
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $currentVersion = $this->updateService->getCurrentVersion();
        $isDirty = $this->updateService->isDirty();
        
        // Don't check for updates on every load to retrieve speed, only if requested or asynchronously?
        // For simplicity, we'll check on load but handle timeout risks. 
        // Better: Check on load for now as it's an admin page.
        $updateInfo = $this->updateService->checkForUpdates();

        $logs = $_SESSION['update_logs'] ?? [];
        unset($_SESSION['update_logs']);

        require __DIR__ . '/../../Views/admin/system/update.php';
    }

    /**
     * Perform update
     */
    public function update()
    {
        if (!hasPermission('admin.edit')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/admin/system/update');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/system/update');
        }

        $logs = [];
        $success = $this->updateService->performUpdate($logs);

        $_SESSION['update_logs'] = $logs;
        
        if ($success) {
            $_SESSION['flash_success'] = 'System updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Update failed. Check logs.';
        }

        redirect('/admin/system/update');
    }
}
