<?php

namespace App\Controllers\Customer;

use App\Core\CustomerAuth;
use App\Services\Customer\CustomerPortalService;

class PortalDashboardController
{
    private CustomerPortalService $portalService;

    public function __construct()
    {
        $this->portalService = new CustomerPortalService();
    }

    /**
     * Customer dashboard
     */
    public function index()
    {
        $customerId = CustomerAuth::getCustomerId();

        if (!$customerId) {
            header('Location: /login');
            exit;
        }

        $data = $this->portalService->getDashboardData($customerId);

        require __DIR__ . '/../../Views/customer/portal/dashboard.php';
    }

    /**
     * View all orders
     */
    public function orders()
    {
        $customerId = CustomerAuth::getCustomerId();

        if (!$customerId) {
            header('Location: /login');
            exit;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $orders = $this->portalService->getAllOrders($customerId, $perPage, $offset);

        require __DIR__ . '/../../Views/customer/portal/orders.php';
    }

    /**
     * View single order
     */
    public function orderDetail(int $orderId)
    {
        $customerId = CustomerAuth::getCustomerId();

        if (!$customerId) {
            header('Location: /login');
            exit;
        }

        $order = $this->portalService->getOrderDetails($customerId, $orderId);

        if (!$order) {
            $_SESSION['flash_error'] = 'Order not found';
            header('Location: /portal/orders');
            exit;
        }

        require __DIR__ . '/../../Views/customer/portal/order_detail.php';
    }

    /**
     * Profile page
     */
    public function profile()
    {
        $customerId = CustomerAuth::getCustomerId();

        if (!$customerId) {
            header('Location: /login');
            exit;
        }

        $data = $this->portalService->getDashboardData($customerId);

        require __DIR__ . '/../../Views/customer/portal/profile.php';
    }

    /**
     * Update profile
     */
    public function updateProfile()
    {
        $customerId = CustomerAuth::getCustomerId();

        if (!$customerId) {
            header('Location: /login');
            exit;
        }

        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['flash_error'] = 'Invalid security token';
            header('Location: /portal/profile');
            exit;
        }

        $data = [
            'first_name' => $_POST['first_name'] ?? null,
            'last_name' => $_POST['last_name'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
        ];

        if ($this->portalService->updateProfile($customerId, $data)) {
            $_SESSION['flash_success'] = 'Profile updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update profile';
        }

        header('Location: /portal/profile');
    }

    /**
     * Certifications page
     */
    public function certifications()
    {
        $customerId = CustomerAuth::getCustomerId();

        if (!$customerId) {
            header('Location: /login');
            exit;
        }

        $data = $this->portalService->getDashboardData($customerId);

        require __DIR__ . '/../../Views/customer/portal/certifications.php';
    }

    /**
     * Download certification card
     */
    public function downloadCertification(int $certificationId)
    {
        $customerId = CustomerAuth::getCustomerId();

        if (!$customerId) {
            header('Location: /login');
            exit;
        }

        $cert = $this->portalService->getCertificationCard($customerId, $certificationId);

        if (!$cert) {
            $_SESSION['flash_error'] = 'Certification not found';
            header('Location: /portal/certifications');
            exit;
        }

        // Generate PDF or display certification card
        require __DIR__ . '/../../Views/customer/portal/certification_card.php';
    }

    /**
     * Request appointment
     */
    public function requestAppointment()
    {
        $customerId = CustomerAuth::getCustomerId();

        if (!$customerId) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require __DIR__ . '/../../Views/customer/portal/request_appointment.php';
            return;
        }

        // Handle POST
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['flash_error'] = 'Invalid security token';
            header('Location: /portal/request-appointment');
            exit;
        }

        try {
            $data = [
                'appointment_type' => $_POST['appointment_type'],
                'start_time' => $_POST['preferred_date'] . ' ' . $_POST['preferred_time'],
                'end_time' => date('Y-m-d H:i:s', strtotime($_POST['preferred_date'] . ' ' . $_POST['preferred_time']) + 3600),
                'notes' => $_POST['notes'] ?? null
            ];

            $appointmentId = $this->portalService->requestAppointment($customerId, $data);

            $_SESSION['flash_success'] = 'Appointment request submitted successfully';
            header('Location: /portal');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error requesting appointment: ' . $e->getMessage();
            header('Location: /portal/request-appointment');
        }
    }

    /**
     * Dive log
     */
    public function diveLogs()
    {
        $customerId = CustomerAuth::getCustomerId();

        if (!$customerId) {
            header('Location: /login');
            exit;
        }

        $diveLogs = $this->portalService->getDiveLogs($customerId);

        require __DIR__ . '/../../Views/customer/portal/dive_logs.php';
    }
}
