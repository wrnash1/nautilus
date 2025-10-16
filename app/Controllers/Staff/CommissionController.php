<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Services\Staff\CommissionService;

class CommissionController
{
    private $commissionService;

    public function __construct()
    {
        $this->commissionService = new CommissionService();
    }

    /**
     * Display commissions list
     */
    public function index()
    {
        if (!Auth::hasPermission('staff.view')) {
            redirect('/dashboard');
            return;
        }

        $commissions = $this->commissionService->getAllCommissions();
        $pageTitle = 'Commissions';

        ob_start();
        require __DIR__ . '/../../Views/staff/commissions/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Display staff member's commissions
     */
    public function staff($staffId)
    {
        if (!Auth::hasPermission('staff.view')) {
            redirect('/dashboard');
            return;
        }

        $commissions = $this->commissionService->getStaffCommissions($staffId);
        $summary = $this->commissionService->getCommissionSummary($staffId);
        $pageTitle = 'Staff Commissions';

        ob_start();
        require __DIR__ . '/../../Views/staff/commissions/staff.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Display commission reports
     */
    public function reports()
    {
        if (!Auth::hasPermission('staff.view')) {
            redirect('/dashboard');
            return;
        }

        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $report = $this->commissionService->getCommissionReport($startDate, $endDate);
        $pageTitle = 'Commission Reports';

        ob_start();
        require __DIR__ . '/../../Views/staff/commissions/reports.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }
}
