<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Services\Staff\StaffService;

class StaffController
{
    private $staffService;

    public function __construct()
    {
        $this->staffService = new StaffService();
    }

    /**
     * Display staff list
     */
    public function index()
    {
        if (!Auth::hasPermission('staff.view')) {
            redirect('/dashboard');
            return;
        }

        $staff = $this->staffService->getAllStaff();
        $pageTitle = 'Staff Management';

        ob_start();
        require __DIR__ . '/../../Views/staff/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Display staff member details
     */
    public function show($id)
    {
        if (!Auth::hasPermission('staff.view')) {
            redirect('/dashboard');
            return;
        }

        $staffMember = $this->staffService->getStaffById($id);
        if (!$staffMember) {
            $_SESSION['error'] = 'Staff member not found.';
            redirect('/staff');
            return;
        }

        $pageTitle = $staffMember['first_name'] . ' ' . $staffMember['last_name'];

        ob_start();
        require __DIR__ . '/../../Views/staff/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Display staff performance metrics
     */
    public function performance($id)
    {
        if (!Auth::hasPermission('staff.view')) {
            redirect('/dashboard');
            return;
        }

        $staffMember = $this->staffService->getStaffById($id);
        $metrics = $this->staffService->getPerformanceMetrics($id);
        $pageTitle = 'Performance Metrics';

        ob_start();
        require __DIR__ . '/../../Views/staff/performance.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }
}
