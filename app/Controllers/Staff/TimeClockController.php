<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Services\Staff\TimeClockService;

class TimeClockController
{
    private $timeClockService;

    public function __construct()
    {
        $this->timeClockService = new TimeClockService();
    }

    /**
     * Display time clock dashboard
     */
    public function index()
    {
        if (!Auth::hasPermission('staff.view')) {
            redirect('/store');
            return;
        }

        $currentStaffId = Auth::id();
        $currentShift = $this->timeClockService->getCurrentShift($currentStaffId);
        $recentEntries = $this->timeClockService->getRecentEntries($currentStaffId);
        $pageTitle = 'Time Clock';

        ob_start();
        require __DIR__ . '/../../Views/staff/timeclock/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Get current clock status
     */
    public function getStatus()
    {
        $staffId = Auth::id();
        $currentShift = $this->timeClockService->getCurrentShift($staffId);
        
        // Return JSON directly as this is primarily for AJAX
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $currentShift ? 'clocked_in' : 'clocked_out',
            'shift' => $currentShift
        ]);
        exit;
    }

    /**
     * Clock in
     */
    public function clockIn()
    {
        $staffId = Auth::id();
        $success = $this->timeClockService->clockIn($staffId);

        if ($this->wantsJson()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $success, 
                'message' => $success ? 'Clocked in successfully.' : 'Failed to clock in. You may already be clocked in.',
                'status' => 'clocked_in'
            ]);
            exit;
        }

        if ($success) {
            $_SESSION['success'] = 'Clocked in successfully.';
        } else {
            $_SESSION['error'] = 'Failed to clock in. You may already be clocked in.';
        }

        redirect('/store/staff/timeclock');
    }

    /**
     * Clock out
     */
    public function clockOut()
    {
        $staffId = Auth::id();
        $success = $this->timeClockService->clockOut($staffId);

        if ($this->wantsJson()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $success, 
                'message' => $success ? 'Clocked out successfully.' : 'Failed to clock out. You may not be clocked in.',
                'status' => 'clocked_out'
            ]);
            exit;
        }

        if ($success) {
            $_SESSION['success'] = 'Clocked out successfully.';
        } else {
            $_SESSION['error'] = 'Failed to clock out. You may not be clocked in.';
        }

        redirect('/store/staff/timeclock');
    }

    private function wantsJson()
    {
        return isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
            || isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * Display timesheet reports
     */
    public function reports()
    {
        if (!Auth::hasPermission('staff.view')) {
            redirect('/store');
            return;
        }

        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        $staffId = $_GET['staff_id'] ?? null;

        $timesheets = $this->timeClockService->getTimesheetReport($startDate, $endDate, $staffId);
        $pageTitle = 'Timesheet Reports';

        ob_start();
        require __DIR__ . '/../../Views/staff/timeclock/reports.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }
}
