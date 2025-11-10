<?php

namespace App\Controllers\Customer;

use App\Core\Controller;
use App\Core\TenantDatabase;
use App\Core\CustomerAuth;
use App\Services\Tenant\WhiteLabelService;

class CustomerPortalController extends Controller
{
    private WhiteLabelService $whiteLabel;

    public function __construct()
    {
        parent::__construct();
        $this->whiteLabel = new WhiteLabelService();
        $this->requireCustomerAuth();
    }

    /**
     * Customer Portal Dashboard
     */
    public function dashboard(): void
    {
        $customerId = $_SESSION['customer_id'];
        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        // Get customer stats
        $stats = $this->getCustomerStats($customerId);

        // Get recent orders
        $recentOrders = $this->getRecentOrders($customerId, 5);

        // Get upcoming courses
        $upcomingCourses = $this->getUpcomingCourses($customerId);

        // Get active rentals
        $activeRentals = $this->getActiveRentals($customerId);

        $data = [
            'branding' => $branding,
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'upcoming_courses' => $upcomingCourses,
            'active_rentals' => $activeRentals,
            'page_title' => 'My Dashboard'
        ];

        $this->view('customer/portal/dashboard', $data);
    }

    /**
     * Profile Management
     */
    public function profile(): void
    {
        $customerId = $_SESSION['customer_id'];
        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateProfile($customerId, $_POST);
            $this->redirect('/portal/profile?success=Profile updated');
            return;
        }

        $customer = TenantDatabase::fetchOneTenant(
            "SELECT * FROM customers WHERE id = ?",
            [$customerId]
        );

        $data = [
            'branding' => $branding,
            'customer' => $customer,
            'page_title' => 'My Profile'
        ];

        $this->view('customer/portal/profile', $data);
    }

    /**
     * Orders List
     */
    public function orders(): void
    {
        $customerId = $_SESSION['customer_id'];
        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        $page = $_GET['page'] ?? 1;
        $perPage = 20;

        $orders = $this->getOrders($customerId, $page, $perPage);

        $data = [
            'branding' => $branding,
            'orders' => $orders['items'],
            'pagination' => $orders['pagination'],
            'page_title' => 'My Orders'
        ];

        $this->view('customer/portal/orders', $data);
    }

    /**
     * Order Detail
     */
    public function orderDetail(int $orderId): void
    {
        $customerId = $_SESSION['customer_id'];
        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        $order = TenantDatabase::fetchOneTenant("
            SELECT * FROM online_orders
            WHERE id = ? AND customer_id = ?
        ", [$orderId, $customerId]);

        if (!$order) {
            $this->redirect('/portal/orders?error=Order not found');
            return;
        }

        $orderItems = TenantDatabase::fetchAllTenant("
            SELECT oi.*, p.name as product_name, p.sku
            FROM online_order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ", [$orderId]) ?? [];

        $data = [
            'branding' => $branding,
            'order' => $order,
            'order_items' => $orderItems,
            'page_title' => 'Order #' . $orderId
        ];

        $this->view('customer/portal/order-detail', $data);
    }

    /**
     * Invoices List
     */
    public function invoices(): void
    {
        $customerId = $_SESSION['customer_id'];
        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        $invoices = TenantDatabase::fetchAllTenant("
            SELECT * FROM pos_transactions
            WHERE customer_id = ? AND status = 'completed'
            ORDER BY created_at DESC
            LIMIT 50
        ", [$customerId]) ?? [];

        $data = [
            'branding' => $branding,
            'invoices' => $invoices,
            'page_title' => 'My Invoices'
        ];

        $this->view('customer/portal/invoices', $data);
    }

    /**
     * Invoice Detail
     */
    public function invoiceDetail(int $invoiceId): void
    {
        $customerId = $_SESSION['customer_id'];
        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        $invoice = TenantDatabase::fetchOneTenant("
            SELECT * FROM pos_transactions
            WHERE id = ? AND customer_id = ?
        ", [$invoiceId, $customerId]);

        if (!$invoice) {
            $this->redirect('/portal/invoices?error=Invoice not found');
            return;
        }

        $invoiceItems = TenantDatabase::fetchAllTenant("
            SELECT ti.*, p.name as product_name, p.sku
            FROM pos_transaction_items ti
            LEFT JOIN products p ON ti.product_id = p.id
            WHERE ti.transaction_id = ?
        ", [$invoiceId]) ?? [];

        $data = [
            'branding' => $branding,
            'invoice' => $invoice,
            'invoice_items' => $invoiceItems,
            'page_title' => 'Invoice #' . $invoiceId
        ];

        $this->view('customer/portal/invoice-detail', $data);
    }

    /**
     * My Courses
     */
    public function courses(): void
    {
        $customerId = $_SESSION['customer_id'];
        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        $enrollments = TenantDatabase::fetchAllTenant("
            SELECT ce.*, c.name as course_name, c.description,
                   u.first_name as instructor_first_name, u.last_name as instructor_last_name
            FROM course_enrollments ce
            JOIN courses c ON ce.course_id = c.id
            LEFT JOIN users u ON c.instructor_id = u.id
            WHERE ce.customer_id = ?
            ORDER BY ce.enrollment_date DESC
        ", [$customerId]) ?? [];

        $data = [
            'branding' => $branding,
            'enrollments' => $enrollments,
            'page_title' => 'My Courses'
        ];

        $this->view('customer/portal/courses', $data);
    }

    /**
     * Course Detail
     */
    public function courseDetail(int $enrollmentId): void
    {
        $customerId = $_SESSION['customer_id'];
        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        $enrollment = TenantDatabase::fetchOneTenant("
            SELECT ce.*, c.name as course_name, c.description, c.duration,
                   u.first_name as instructor_first_name, u.last_name as instructor_last_name
            FROM course_enrollments ce
            JOIN courses c ON ce.course_id = c.id
            LEFT JOIN users u ON c.instructor_id = u.id
            WHERE ce.id = ? AND ce.customer_id = ?
        ", [$enrollmentId, $customerId]);

        if (!$enrollment) {
            $this->redirect('/portal/courses?error=Enrollment not found');
            return;
        }

        $data = [
            'branding' => $branding,
            'enrollment' => $enrollment,
            'page_title' => $enrollment['course_name']
        ];

        $this->view('customer/portal/course-detail', $data);
    }

    /**
     * Certifications
     */
    public function certifications(): void
    {
        $customerId = $_SESSION['customer_id'];
        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        $certifications = TenantDatabase::fetchAllTenant("
            SELECT * FROM certifications
            WHERE customer_id = ?
            ORDER BY issue_date DESC
        ", [$customerId]) ?? [];

        $customer = TenantDatabase::fetchOneTenant(
            "SELECT certification_level FROM customers WHERE id = ?",
            [$customerId]
        );

        $data = [
            'branding' => $branding,
            'certifications' => $certifications,
            'current_level' => $customer['certification_level'] ?? 'None',
            'page_title' => 'My Certifications'
        ];

        $this->view('customer/portal/certifications', $data);
    }

    /**
     * Rentals
     */
    public function rentals(): void
    {
        $customerId = $_SESSION['customer_id'];
        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        $rentals = TenantDatabase::fetchAllTenant("
            SELECT r.*, p.name as product_name
            FROM rentals r
            LEFT JOIN rental_items ri ON r.id = ri.rental_id
            LEFT JOIN products p ON ri.product_id = p.id
            WHERE r.customer_id = ?
            ORDER BY r.rental_date DESC
        ", [$customerId]) ?? [];

        $data = [
            'branding' => $branding,
            'rentals' => $rentals,
            'page_title' => 'My Rentals'
        ];

        $this->view('customer/portal/rentals', $data);
    }

    /**
     * Change Password
     */
    public function changePassword(): void
    {
        $customerId = $_SESSION['customer_id'];
        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->updatePassword($customerId, $_POST);

            if ($result['success']) {
                $this->redirect('/portal/profile?success=Password changed');
            } else {
                $data = [
                    'branding' => $branding,
                    'error' => $result['error'],
                    'page_title' => 'Change Password'
                ];
                $this->view('customer/portal/change-password', $data);
            }
            return;
        }

        $data = [
            'branding' => $branding,
            'page_title' => 'Change Password'
        ];

        $this->view('customer/portal/change-password', $data);
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        CustomerAuth::logout();
        $this->redirect('/?success=Logged out successfully');
    }

    // Private helper methods

    private function requireCustomerAuth(): void
    {
        if (!isset($_SESSION['customer_id'])) {
            $this->redirect('/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    private function getCustomerStats(int $customerId): array
    {
        $ordersCount = TenantDatabase::fetchOneTenant(
            "SELECT COUNT(*) as count FROM online_orders WHERE customer_id = ?",
            [$customerId]
        );

        $totalSpent = TenantDatabase::fetchOneTenant(
            "SELECT SUM(total_amount) as total FROM pos_transactions WHERE customer_id = ? AND status = 'completed'",
            [$customerId]
        );

        $coursesCount = TenantDatabase::fetchOneTenant(
            "SELECT COUNT(*) as count FROM course_enrollments WHERE customer_id = ?",
            [$customerId]
        );

        $certificationsCount = TenantDatabase::fetchOneTenant(
            "SELECT COUNT(*) as count FROM certifications WHERE customer_id = ?",
            [$customerId]
        );

        return [
            'orders_count' => $ordersCount['count'] ?? 0,
            'total_spent' => $totalSpent['total'] ?? 0,
            'courses_count' => $coursesCount['count'] ?? 0,
            'certifications_count' => $certificationsCount['count'] ?? 0
        ];
    }

    private function getRecentOrders(int $customerId, int $limit): array
    {
        return TenantDatabase::fetchAllTenant("
            SELECT * FROM online_orders
            WHERE customer_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ", [$customerId, $limit]) ?? [];
    }

    private function getUpcomingCourses(int $customerId): array
    {
        return TenantDatabase::fetchAllTenant("
            SELECT ce.*, c.name as course_name, c.start_date
            FROM course_enrollments ce
            JOIN courses c ON ce.course_id = c.id
            WHERE ce.customer_id = ? AND c.start_date >= CURDATE()
            ORDER BY c.start_date ASC
            LIMIT 5
        ", [$customerId]) ?? [];
    }

    private function getActiveRentals(int $customerId): array
    {
        return TenantDatabase::fetchAllTenant("
            SELECT r.*, p.name as product_name
            FROM rentals r
            LEFT JOIN rental_items ri ON r.id = ri.rental_id
            LEFT JOIN products p ON ri.product_id = p.id
            WHERE r.customer_id = ? AND r.status = 'active'
            ORDER BY r.due_date ASC
        ", [$customerId]) ?? [];
    }

    private function getOrders(int $customerId, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;

        $orders = TenantDatabase::fetchAllTenant("
            SELECT * FROM online_orders
            WHERE customer_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ", [$customerId, $perPage, $offset]) ?? [];

        $total = TenantDatabase::fetchOneTenant(
            "SELECT COUNT(*) as count FROM online_orders WHERE customer_id = ?",
            [$customerId]
        );

        return [
            'items' => $orders,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total['count'],
                'total_pages' => ceil($total['count'] / $perPage)
            ]
        ];
    }

    private function updateProfile(int $customerId, array $data): void
    {
        TenantDatabase::updateTenant('customers', [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
            'country' => $data['country'] ?? 'US'
        ], 'id = ?', [$customerId]);
    }

    private function updatePassword(int $customerId, array $data): array
    {
        if (empty($data['current_password']) || empty($data['new_password'])) {
            return ['success' => false, 'error' => 'All fields are required'];
        }

        if ($data['new_password'] !== $data['confirm_password']) {
            return ['success' => false, 'error' => 'Passwords do not match'];
        }

        $customer = TenantDatabase::fetchOneTenant(
            "SELECT password FROM customers WHERE id = ?",
            [$customerId]
        );

        if (!password_verify($data['current_password'], $customer['password'])) {
            return ['success' => false, 'error' => 'Current password is incorrect'];
        }

        $newPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);

        TenantDatabase::updateTenant('customers', [
            'password' => $newPassword
        ], 'id = ?', [$customerId]);

        return ['success' => true];
    }
}
