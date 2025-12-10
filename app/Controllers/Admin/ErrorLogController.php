<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\ErrorLogService;

/**
 * Error Log Controller
 * View and manage application errors
 */
class ErrorLogController extends Controller
{
    public function index()
    {
        $this->checkPermission('errors.view');

        $errors = ErrorLogService::getRecentErrors(100);
        $stats = ErrorLogService::getErrorStats(7);

        $data = [
            'errors' => $errors,
            'stats' => $stats,
            'pageTitle' => 'Error Logs',
            'activeMenu' => 'errors'
        ];

        $this->view('admin/errors/index', $data);
    }

    public function show(int $id)
    {
        $this->checkPermission('errors.view');

        $error = ErrorLogService::getError($id);

        if (!$error) {
            $_SESSION['flash_error'] = 'Error not found';
            $this->redirect('/store/admin/errors');
            return;
        }

        $data = [
            'error' => $error,
            'pageTitle' => 'Error Details',
            'activeMenu' => 'errors'
        ];

        $this->view('admin/errors/show', $data);
    }

    public function resolve(int $id)
    {
        $this->checkPermission('errors.manage');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/admin/errors');
            return;
        }

        $resolutionNotes = $_POST['resolution_notes'] ?? '';

        if (ErrorLogService::resolveError($id, $resolutionNotes)) {
            $_SESSION['flash_success'] = 'Error marked as resolved';
        } else {
            $_SESSION['flash_error'] = 'Failed to resolve error';
        }

        $this->redirect('/store/admin/errors/' . $id);
    }
}
