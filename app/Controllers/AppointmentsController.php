<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Services\Appointments\AppointmentService;

class AppointmentsController
{
    private AppointmentService $appointmentService;

    public function __construct()
    {
        $this->appointmentService = new AppointmentService();
    }

    /**
     * Display all appointments
     */
    public function index()
    {
        // Get filter parameters
        $filters = [
            'status' => $_GET['status'] ?? null,
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'appointment_type' => $_GET['type'] ?? null
        ];

        // Remove null values
        $filters = array_filter($filters, fn($value) => $value !== null);

        $appointments = $this->appointmentService->getAll($filters);
        $staff = $this->getStaffList();

        require __DIR__ . '/../Views/appointments/index.php';
    }

    /**
     * Show calendar view
     */
    public function calendar()
    {
        $staff = $this->getStaffList();
        require __DIR__ . '/../Views/appointments/calendar.php';
    }

    /**
     * Get appointments for calendar (AJAX)
     */
    public function getCalendarData()
    {
        $startDate = $_GET['start'] ?? date('Y-m-01');
        $endDate = $_GET['end'] ?? date('Y-m-t');

        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if (!empty($_GET['assigned_to'])) {
            $filters['assigned_to'] = (int)$_GET['assigned_to'];
        }

        $appointments = $this->appointmentService->getAll($filters);

        // Format for calendar
        $events = array_map(function($apt) {
            $colors = [
                'scheduled' => '#007bff',
                'confirmed' => '#28a745',
                'completed' => '#6c757d',
                'cancelled' => '#dc3545',
                'no_show' => '#ffc107'
            ];

            return [
                'id' => $apt['id'],
                'title' => $apt['customer_name'] . ' - ' . ucfirst($apt['appointment_type']),
                'start' => $apt['start_time'],
                'end' => $apt['end_time'],
                'color' => $colors[$apt['status']] ?? '#6c757d',
                'extendedProps' => [
                    'customer_name' => $apt['customer_name'],
                    'status' => $apt['status'],
                    'type' => $apt['appointment_type'],
                    'assigned_to' => $apt['assigned_to_name'],
                    'location' => $apt['location']
                ]
            ];
        }, $appointments);

        header('Content-Type: application/json');
        echo json_encode($events);
    }

    /**
     * Show create appointment form
     */
    public function create()
    {
        $customers = $this->getCustomersList();
        $staff = $this->getStaffList();

        require __DIR__ . '/../Views/appointments/create.php';
    }

    /**
     * Store a new appointment
     */
    public function store()
    {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['flash_error'] = 'Invalid CSRF token';
            header('Location: /store/appointments/create');
            exit;
        }

        try {
            // Validate required fields
            $required = ['customer_id', 'appointment_type', 'start_time', 'end_time'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new \Exception("Field '$field' is required");
                }
            }

            // Check for conflicts
            $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
            if ($this->appointmentService->hasConflict($_POST['start_time'], $_POST['end_time'], $assignedTo)) {
                throw new \Exception('Time slot conflict detected');
            }

            $data = [
                'customer_id' => (int)$_POST['customer_id'],
                'appointment_type' => $_POST['appointment_type'],
                'start_time' => $_POST['start_time'],
                'end_time' => $_POST['end_time'],
                'assigned_to' => $assignedTo,
                'location' => $_POST['location'] ?? null,
                'status' => $_POST['status'] ?? 'scheduled',
                'notes' => $_POST['notes'] ?? null
            ];

            $id = $this->appointmentService->create($data);

            $_SESSION['flash_success'] = 'Appointment created successfully';
            header("Location: /store/appointments/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            header('Location: /store/appointments/create');
        }
    }

    /**
     * Show appointment details
     */
    public function show(int $id)
    {
        $appointment = $this->appointmentService->getById($id);

        if (!$appointment) {
            $_SESSION['flash_error'] = 'Appointment not found';
            header('Location: /store/appointments');
            exit;
        }

        require __DIR__ . '/../Views/appointments/show.php';
    }

    /**
     * Show edit form
     */
    public function edit(int $id)
    {
        $appointment = $this->appointmentService->getById($id);

        if (!$appointment) {
            $_SESSION['flash_error'] = 'Appointment not found';
            header('Location: /store/appointments');
            exit;
        }

        $customers = $this->getCustomersList();
        $staff = $this->getStaffList();

        require __DIR__ . '/../Views/appointments/edit.php';
    }

    /**
     * Update appointment
     */
    public function update(int $id)
    {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['flash_error'] = 'Invalid CSRF token';
            header("Location: /store/appointments/{$id}/edit");
            exit;
        }

        try {
            $data = [];

            if (isset($_POST['appointment_type'])) $data['appointment_type'] = $_POST['appointment_type'];
            if (isset($_POST['start_time'])) $data['start_time'] = $_POST['start_time'];
            if (isset($_POST['end_time'])) $data['end_time'] = $_POST['end_time'];
            if (isset($_POST['assigned_to'])) $data['assigned_to'] = (int)$_POST['assigned_to'] ?: null;
            if (isset($_POST['location'])) $data['location'] = $_POST['location'];
            if (isset($_POST['status'])) $data['status'] = $_POST['status'];
            if (isset($_POST['notes'])) $data['notes'] = $_POST['notes'];

            // Check for conflicts if times changed
            if (isset($data['start_time']) && isset($data['end_time'])) {
                $assignedTo = $data['assigned_to'] ?? null;
                if ($this->appointmentService->hasConflict($data['start_time'], $data['end_time'], $assignedTo, $id)) {
                    throw new \Exception('Time slot conflict detected');
                }
            }

            $this->appointmentService->update($id, $data);

            $_SESSION['flash_success'] = 'Appointment updated successfully';
            header("Location: /store/appointments/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            header("Location: /store/appointments/{$id}/edit");
        }
    }

    /**
     * Cancel appointment
     */
    public function cancel(int $id)
    {
        try {
            $this->appointmentService->cancel($id);
            $_SESSION['flash_success'] = 'Appointment cancelled';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
        }

        header("Location: /store/appointments/{$id}");
    }

    /**
     * Confirm appointment
     */
    public function confirm(int $id)
    {
        try {
            $this->appointmentService->confirm($id);
            $_SESSION['flash_success'] = 'Appointment confirmed';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
        }

        header("Location: /store/appointments/{$id}");
    }

    /**
     * Complete appointment
     */
    public function complete(int $id)
    {
        try {
            $this->appointmentService->complete($id);
            $_SESSION['flash_success'] = 'Appointment marked as completed';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
        }

        header("Location: /store/appointments/{$id}");
    }

    /**
     * Delete appointment
     */
    public function delete(int $id)
    {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['flash_error'] = 'Invalid CSRF token';
            header("Location: /store/appointments/{$id}");
            exit;
        }

        try {
            $this->appointmentService->delete($id);
            $_SESSION['flash_success'] = 'Appointment deleted';
            header('Location: /store/appointments');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            header("Location: /store/appointments/{$id}");
        }
    }

    /**
     * Helper: Get staff list
     */
    private function getStaffList(): array
    {
        return Database::fetchAll(
            "SELECT id, CONCAT(first_name, ' ', last_name) as name
             FROM users
             WHERE is_active = 1
             ORDER BY first_name, last_name"
        ) ?? [];
    }

    /**
     * Helper: Get customers list
     */
    private function getCustomersList(): array
    {
        return Database::fetchAll(
            "SELECT id, CONCAT(first_name, ' ', last_name) as name, email, phone
             FROM customers
             WHERE is_active = 1
             ORDER BY first_name, last_name
             LIMIT 1000"
        ) ?? [];
    }
}
