<?php

namespace App\Controllers\Financial;

use App\Core\Controller;
use App\Core\Database;
use App\Services\Financial\LayawayService;

class LayawayController extends Controller
{
    private LayawayService $layawayService;

    public function __construct()
    {
        parent::__construct();
        $this->layawayService = new LayawayService($this->db);
    }

    /**
     * Display layaway agreements list
     */
    public function index(): void
    {
        $this->checkPermission('transactions.view');

        $status = $_GET['status'] ?? 'all';
        $search = $_GET['search'] ?? '';

        // Get agreements
        $sql = "SELECT la.*, lp.plan_name,
                       c.first_name, c.last_name, c.email, c.phone
                FROM layaway_agreements la
                LEFT JOIN layaway_plans lp ON la.layaway_plan_id = lp.id
                LEFT JOIN customers c ON la.customer_id = c.id
                WHERE 1=1";
        $params = [];

        if ($status !== 'all') {
            $sql .= " AND la.status = ?";
            $params[] = $status;
        }

        if ($search) {
            $sql .= " AND (la.agreement_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY la.created_at DESC LIMIT 100";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $agreements = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $agreements = [];
        }

        // Get statistics
        $stats = $this->getLayawayStats();

        $this->view('financial/layaway/index', [
            'title' => 'Layaway Agreements',
            'agreements' => $agreements,
            'stats' => $stats,
            'currentStatus' => $status,
            'search' => $search
        ]);
    }

    /**
     * Show create layaway form
     */
    public function create(): void
    {
        $this->checkPermission('transactions.create');

        // Get active layaway plans
        try {
            $stmt = $this->db->query("SELECT * FROM layaway_plans WHERE is_active = 1 ORDER BY plan_name");
            $plans = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $plans = [];
        }

        // Pre-select customer if provided
        $customerId = $_GET['customer_id'] ?? null;
        $customer = null;
        if ($customerId) {
            $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = ?");
            $stmt->execute([$customerId]);
            $customer = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        $this->view('financial/layaway/create', [
            'title' => 'Create Layaway Agreement',
            'plans' => $plans,
            'customer' => $customer
        ]);
    }

    /**
     * Store new layaway agreement
     */
    public function store(): void
    {
        $this->checkPermission('transactions.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/layaway');
            return;
        }

        $customerId = (int)($_POST['customer_id'] ?? 0);
        $planId = (int)($_POST['plan_id'] ?? 0);
        $items = json_decode($_POST['items'] ?? '[]', true);
        $totalAmount = (float)($_POST['total_amount'] ?? 0);

        if (!$customerId || !$planId || empty($items) || $totalAmount <= 0) {
            $_SESSION['flash_error'] = 'Please fill in all required fields';
            $this->redirect('/store/layaway/create');
            return;
        }

        $result = $this->layawayService->createLayawayAgreement([
            'tenant_id' => 1, // Default tenant
            'customer_id' => $customerId,
            'layaway_plan_id' => $planId,
            'items' => $items,
            'total_amount' => $totalAmount,
            'created_by' => $_SESSION['user_id'] ?? null
        ]);

        if ($result['success']) {
            $_SESSION['flash_success'] = "Layaway agreement {$result['agreement_number']} created successfully. Down payment: \${$result['down_payment']}";
            $this->redirect('/store/layaway/' . $result['agreement_id']);
        } else {
            $_SESSION['flash_error'] = $result['error'] ?? 'Failed to create layaway agreement';
            $this->redirect('/store/layaway/create');
        }
    }

    /**
     * Show layaway agreement details
     */
    public function show(int $id): void
    {
        $this->checkPermission('transactions.view');

        $agreement = $this->getAgreement($id);
        if (!$agreement) {
            $_SESSION['flash_error'] = 'Agreement not found';
            $this->redirect('/store/layaway');
            return;
        }

        // Get payment schedule
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM layaway_payment_schedules
                WHERE agreement_id = ?
                ORDER BY payment_number
            ");
            $stmt->execute([$id]);
            $schedule = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $schedule = [];
        }

        // Get payment history
        try {
            $stmt = $this->db->prepare("
                SELECT lps.*, p.payment_date as actual_payment_date, p.amount as actual_amount
                FROM layaway_payment_schedules lps
                LEFT JOIN payments p ON lps.payment_id = p.id
                WHERE lps.agreement_id = ? AND lps.payment_status = 'paid'
                ORDER BY lps.payment_number
            ");
            $stmt->execute([$id]);
            $paymentHistory = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $paymentHistory = [];
        }

        $this->view('financial/layaway/show', [
            'title' => 'Layaway: ' . $agreement['agreement_number'],
            'agreement' => $agreement,
            'schedule' => $schedule,
            'paymentHistory' => $paymentHistory
        ]);
    }

    /**
     * Record a payment
     */
    public function recordPayment(int $id): void
    {
        $this->checkPermission('transactions.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/layaway/' . $id);
            return;
        }

        $amount = (float)($_POST['amount'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'cash';

        if ($amount <= 0) {
            $_SESSION['flash_error'] = 'Invalid payment amount';
            $this->redirect('/store/layaway/' . $id);
            return;
        }

        // Create payment record first
        try {
            $stmt = $this->db->prepare("
                INSERT INTO payments (customer_id, amount, payment_method, payment_type, notes, created_at)
                SELECT customer_id, ?, ?, 'layaway', 'Layaway payment', NOW()
                FROM layaway_agreements WHERE id = ?
            ");
            $stmt->execute([$amount, $paymentMethod, $id]);
            $paymentId = $this->db->lastInsertId();

            $result = $this->layawayService->recordPayment($id, $amount, $paymentId);

            if ($result['success']) {
                if ($result['completed']) {
                    $_SESSION['flash_success'] = "Layaway completed! All payments received.";
                } else {
                    $_SESSION['flash_success'] = "Payment of \${$amount} recorded. Remaining balance: \${$result['balance_remaining']}";
                }
            } else {
                $_SESSION['flash_error'] = $result['error'] ?? 'Failed to record payment';
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error recording payment: ' . $e->getMessage();
        }

        $this->redirect('/store/layaway/' . $id);
    }

    /**
     * Cancel layaway agreement
     */
    public function cancel(int $id): void
    {
        $this->checkPermission('transactions.void');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/layaway/' . $id);
            return;
        }

        $reason = $_POST['reason'] ?? 'Customer request';
        $processRefund = isset($_POST['process_refund']);

        $result = $this->layawayService->cancelAgreement($id, $reason, $processRefund);

        if ($result['success']) {
            $msg = 'Layaway agreement cancelled.';
            if ($result['refund_amount'] > 0) {
                $msg .= " Refund amount: \${$result['refund_amount']}";
            }
            $_SESSION['flash_success'] = $msg;
        } else {
            $_SESSION['flash_error'] = $result['error'] ?? 'Failed to cancel agreement';
        }

        $this->redirect('/store/layaway');
    }

    /**
     * Manage layaway plans
     */
    public function plans(): void
    {
        $this->checkPermission('transactions.create');

        try {
            $stmt = $this->db->query("SELECT * FROM layaway_plans ORDER BY plan_name");
            $plans = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $plans = [];
        }

        $this->view('financial/layaway/plans', [
            'title' => 'Layaway Plans',
            'plans' => $plans
        ]);
    }

    /**
     * Create or update layaway plan
     */
    public function savePlan(): void
    {
        $this->checkPermission('transactions.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/layaway/plans');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'plan_name' => $_POST['plan_name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'number_of_payments' => (int)($_POST['number_of_payments'] ?? 4),
            'payment_frequency' => $_POST['payment_frequency'] ?? 'monthly',
            'down_payment_percentage' => (float)($_POST['down_payment_percentage'] ?? 25),
            'down_payment_minimum' => (float)($_POST['down_payment_minimum'] ?? 50),
            'layaway_fee' => (float)($_POST['layaway_fee'] ?? 0),
            'layaway_fee_type' => $_POST['layaway_fee_type'] ?? 'fixed',
            'min_purchase_amount' => (float)($_POST['min_purchase_amount'] ?? 100),
            'cancellation_fee' => (float)($_POST['cancellation_fee'] ?? 25),
            'restocking_fee_percentage' => (float)($_POST['restocking_fee_percentage'] ?? 10),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        try {
            if ($id > 0) {
                // Update existing
                $sql = "UPDATE layaway_plans SET
                        plan_name = ?, description = ?, number_of_payments = ?,
                        payment_frequency = ?, down_payment_percentage = ?, down_payment_minimum = ?,
                        layaway_fee = ?, layaway_fee_type = ?, min_purchase_amount = ?,
                        cancellation_fee = ?, restocking_fee_percentage = ?, is_active = ?
                        WHERE id = ?";
                $params = array_values($data);
                $params[] = $id;
                $this->db->prepare($sql)->execute($params);
                $_SESSION['flash_success'] = 'Layaway plan updated';
            } else {
                // Create new
                $sql = "INSERT INTO layaway_plans (
                        plan_name, description, number_of_payments, payment_frequency,
                        down_payment_percentage, down_payment_minimum, layaway_fee, layaway_fee_type,
                        min_purchase_amount, cancellation_fee, restocking_fee_percentage, is_active
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $this->db->prepare($sql)->execute(array_values($data));
                $_SESSION['flash_success'] = 'Layaway plan created';
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error saving plan: ' . $e->getMessage();
        }

        $this->redirect('/store/layaway/plans');
    }

    /**
     * Get upcoming payments report
     */
    public function upcomingPayments(): void
    {
        $this->checkPermission('transactions.view');

        $daysAhead = (int)($_GET['days'] ?? 7);
        $result = $this->layawayService->getUpcomingPayments(1, $daysAhead);

        $this->view('financial/layaway/upcoming', [
            'title' => 'Upcoming Layaway Payments',
            'payments' => $result['upcoming_payments'] ?? [],
            'daysAhead' => $daysAhead
        ]);
    }

    /**
     * Get agreement details
     */
    private function getAgreement(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT la.*, lp.plan_name,
                       c.first_name, c.last_name, c.email, c.phone
                FROM layaway_agreements la
                LEFT JOIN layaway_plans lp ON la.layaway_plan_id = lp.id
                LEFT JOIN customers c ON la.customer_id = c.id
                WHERE la.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get layaway statistics
     */
    private function getLayawayStats(): array
    {
        try {
            $stats = [];

            // Active agreements
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM layaway_agreements WHERE status = 'active'");
            $stats['active'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            // Pending agreements
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM layaway_agreements WHERE status = 'pending'");
            $stats['pending'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            // Completed this month
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM layaway_agreements WHERE status = 'completed' AND completed_at >= DATE_FORMAT(NOW(), '%Y-%m-01')");
            $stats['completed_month'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            // Total outstanding balance
            $stmt = $this->db->query("SELECT SUM(balance_remaining) as total FROM layaway_agreements WHERE status IN ('active', 'pending')");
            $stats['outstanding_balance'] = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            return $stats;
        } catch (\Exception $e) {
            return ['active' => 0, 'pending' => 0, 'completed_month' => 0, 'outstanding_balance' => 0];
        }
    }
}
