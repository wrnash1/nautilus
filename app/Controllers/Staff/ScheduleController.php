<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Services\Staff\ScheduleService;

class ScheduleController
{
    private $scheduleService;

    public function __construct()
    {
        $this->scheduleService = new ScheduleService();
    }

    /**
     * Display staff schedules
     */
    public function index()
    {
        if (!Auth::hasPermission('staff.view')) {
            redirect('/dashboard');
            return;
        }

        $schedules = $this->scheduleService->getAllSchedules();
        $pageTitle = 'Staff Schedules';

        ob_start();
        require __DIR__ . '/../../Views/staff/schedules/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Show create schedule form
     */
    public function create()
    {
        if (!Auth::hasPermission('staff.create')) {
            redirect('/dashboard');
            return;
        }

        $pageTitle = 'Create Schedule';

        ob_start();
        require __DIR__ . '/../../Views/staff/schedules/create.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Store new schedule
     */
    public function store()
    {
        if (!Auth::hasPermission('staff.create')) {
            redirect('/dashboard');
            return;
        }

        $data = [
            'staff_id' => $_POST['staff_id'] ?? 0,
            'shift_date' => $_POST['shift_date'] ?? '',
            'shift_start' => $_POST['shift_start'] ?? '',
            'shift_end' => $_POST['shift_end'] ?? '',
            'role' => $_POST['role'] ?? '',
            'notes' => $_POST['notes'] ?? ''
        ];

        $scheduleId = $this->scheduleService->createSchedule($data);

        if ($scheduleId) {
            $_SESSION['success'] = 'Schedule created successfully.';
            redirect('/staff/schedules');
        } else {
            $_SESSION['error'] = 'Failed to create schedule.';
            redirect('/staff/schedules/create');
        }
    }

    /**
     * Delete schedule
     */
    public function delete($id)
    {
        if (!Auth::hasPermission('staff.delete')) {
            redirect('/dashboard');
            return;
        }

        $success = $this->scheduleService->deleteSchedule($id);

        if ($success) {
            $_SESSION['success'] = 'Schedule deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete schedule.';
        }

        redirect('/staff/schedules');
    }
}
