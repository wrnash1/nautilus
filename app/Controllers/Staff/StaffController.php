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
     * Display create staff form
     */
    public function create()
    {
        if (!Auth::hasPermission('staff.create')) {
            $_SESSION['error'] = 'You do not have permission to create staff members.';
            redirect('/staff');
            return;
        }

        $roles = $this->staffService->getAvailableRoles();
        $pageTitle = 'Add Staff Member';

        ob_start();
        require __DIR__ . '/../../Views/staff/create.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Store new staff member
     */
    public function store()
    {
        if (!Auth::hasPermission('staff.create')) {
            $_SESSION['error'] = 'You do not have permission to create staff members.';
            redirect('/staff');
            return;
        }

        // Validate required fields
        $required = ['first_name', 'last_name', 'email', 'password', 'role_id'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = 'Please fill in all required fields.';
                redirect('/staff/create');
                return;
            }
        }

        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address.';
            redirect('/staff/create');
            return;
        }

        // Validate password length
        if (strlen($_POST['password']) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters long.';
            redirect('/staff/create');
            return;
        }

        // Create staff member
        $staffId = $this->staffService->createStaff($_POST);

        if ($staffId) {
            $_SESSION['success'] = 'Staff member created successfully.';
            redirect('/staff/' . $staffId);
        } else {
            $_SESSION['error'] = 'Failed to create staff member. Email may already be in use.';
            redirect('/staff/create');
        }
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
