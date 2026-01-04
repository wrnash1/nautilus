<?php

namespace App\Controllers\POS;

use App\Core\Database;
use App\Services\POS\TransactionService;
use App\Models\Product;
use App\Models\Customer;

class TransactionController
{
    private TransactionService $transactionService;

    public function __construct()
    {
        $this->transactionService = new TransactionService();
    }

    public function index()
    {
        try {
            // Check permission
            if (!hasPermission('pos.view')) {
                redirect('/store/dashboard');
            }

            $products = Product::limit(50)->get();
            $customers = Customer::limit(100)->get();

            // Get settings
            $taxRate = $this->transactionService->getTaxRate();

            // Check for bitcoin setting
            try {
                $btcSetting = Database::fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'bitcoin_enabled'");
                $bitcoinEnabled = $btcSetting && $btcSetting['setting_value'] === '1';
            } catch (\Exception $e) {
                $bitcoinEnabled = false;
            }

            // Get active courses for enrollment
            $courses = Database::fetchAll("
                SELECT id, course_code, name, price, duration_days, max_students
                FROM courses
                WHERE is_active = 1
                ORDER BY name
            ");

            // Get rental equipment
            $rentals = Database::fetchAll("
                SELECT id, name, daily_rate, equipment_code as sku, status
                FROM rental_equipment
                WHERE status = 'available'
                ORDER BY name
            ");

            // Get upcoming trips
            $trips = Database::fetchAll("
                SELECT t.id, t.name, t.price, ts.departure_date as start_date,
                       ts.max_participants as max_spots, ts.current_bookings as booked_spots
                FROM trips t
                INNER JOIN trip_schedules ts ON t.id = ts.trip_id
                WHERE ts.departure_date >= CURDATE() AND ts.status = 'scheduled' AND t.is_active = 1
                ORDER BY ts.departure_date
            ");

            require __DIR__ . '/../../Views/pos/index.php';

        } catch (\PDOException $e) {
            die("PDO Error: " . $e->getMessage());
        } catch (\Throwable $e) {
            die("General Error: " . $e->getMessage());
        }
    }

    public function searchProducts()
    {
        if (!hasPermission('pos.view')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        $rawQuery = $_GET['q'] ?? '';
        $query = sanitizeInput($rawQuery);

        error_log("POS Search: Raw='$rawQuery', Sanitized='$query'");

        if (empty($query)) {
            jsonResponse([]);
        }

        $products = Product::search($query, 20);
        error_log("POS Search Result Count: " . count($products));

        jsonResponse($products);
    }

    public function processCheckout()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }

            if (!hasPermission('pos.create')) {
                jsonResponse(['error' => 'Access denied'], 403);
            }

            $input = $_POST;
            $customerId = empty($input['customer_id']) ? null : (int) $input['customer_id'];
            $paymentMethod = $input['payment_method'] ?? 'cash';
            $amountPaid = (float) ($input['amount_paid'] ?? 0);
            $items = json_decode($input['items'] ?? '[]', true);
            $note = $input['note'] ?? null;

            error_log("Processing Checkout: Cust=$customerId, Amt=$amountPaid, Items=" . count($items));

            if (empty($items)) {
                jsonResponse(['error' => 'No items in cart'], 400);
            }

            // Create Transaction
            $transactionId = $this->transactionService->createTransaction($customerId, $items);
            error_log("Transaction Created: ID=$transactionId");

            // Process Payment
            if ($amountPaid > 0) {
                $this->transactionService->processPayment($transactionId, $paymentMethod, $amountPaid, $note);
                error_log("Payment Processed");
            }

            // Get Receipt URL (Mock or Real)
            $receiptUrl = "/store/pos/receipt/$transactionId";

            jsonResponse([
                'success' => true,
                'transaction_id' => $transactionId,
                'receipt_url' => $receiptUrl
            ]);
        } catch (\Throwable $e) {
            error_log("Checkout Failed: " . $e->getMessage());
            jsonResponse(['error' => 'Checkout Failed: ' . $e->getMessage()], 500);
        }
    }

    public function checkout()
    {
        if (!hasPermission('pos.create')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        $customerId = (int) ($_POST['customer_id'] ?? 0);
        $items = json_decode($_POST['items'] ?? '[]', true);
        $paymentMethod = sanitizeInput($_POST['payment_method'] ?? 'cash');
        $amountPaid = (float) ($_POST['amount_paid'] ?? 0);
        $action = sanitizeInput($_POST['action'] ?? 'pay'); // 'pay', 'quote', 'layaway'

        // Allow empty customer_id for walk-in customers (will be set to NULL in transaction)
        if (empty($items)) {
            jsonResponse(['error' => 'Invalid transaction data - no items'], 400);
        }

        // Convert 0 to null for walk-in customers
        if ($customerId === 0) {
            $customerId = null;
        }

        // Map action to transaction type
        $transactionType = 'sale';
        if ($action === 'quote')
            $transactionType = 'quote';
        if ($action === 'layaway')
            $transactionType = 'layaway';

        try {
            $transactionId = $this->transactionService->createTransaction($customerId, $items, $transactionType);

            $transaction = Database::fetchOne(
                "SELECT total FROM transactions WHERE id = ?",
                [$transactionId]
            );

            // Check payment sufficiency only for standard sales
            if ($action === 'pay') {
                if ($amountPaid < $transaction['total']) {
                    jsonResponse(['error' => 'Insufficient payment amount'], 400);
                }
            } else {
                // For quotes/layaways, amountPaid might be deposit or 0.
                if ($action === 'quote')
                    $amountPaid = 0;
            }

            $success = $this->transactionService->processPayment(
                $transactionId,
                $paymentMethod,
                $amountPaid,
                sanitizeInput($_POST['note'] ?? '')
            );

            if ($success) {
                $sessionKey = 'flash_success';
                $msg = 'Transaction completed successfully';
                if ($action === 'quote')
                    $msg = 'Quote saved successfully';
                if ($action === 'layaway')
                    $msg = 'Layaway created successfully';

                $_SESSION[$sessionKey] = $msg;

                jsonResponse([
                    'success' => true,
                    'transaction_id' => $transactionId,
                    'redirect' => "/store/pos/receipt/{$transactionId}"
                ]);
            } else {
                // Auto-void the pending transaction to prevent "phantom" outstanding balances
                $this->transactionService->voidTransaction($transactionId, "Payment Failed/Cancelled");

                jsonResponse(['error' => 'Payment processing failed. Transaction has been voided.'], 500);
            }
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function receipt(int $id)
    {
        if (!hasPermission('pos.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $transaction = $this->transactionService->getTransaction($id);
        $items = $this->transactionService->getTransactionItems($id);

        if (!$transaction) {
            $_SESSION['flash_error'] = 'Transaction not found';
            redirect('/pos');
        }

        require __DIR__ . '/../../Views/pos/receipt.php';
    }

    public function void(int $id)
    {
        if (!hasPermission('pos.void')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $reason = sanitizeInput($_POST['reason'] ?? 'Voided by user');

        $success = $this->transactionService->voidTransaction($id, $reason);

        if ($success) {
            $_SESSION['flash_success'] = 'Transaction voided successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to void transaction';
        }

        redirect('/pos');
    }

    public function refund(int $id)
    {
        if (!hasPermission('pos.refund')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $amount = (float) ($_POST['amount'] ?? 0);

        $success = $this->transactionService->refundTransaction($id, $amount);

        if ($success) {
            $_SESSION['flash_success'] = 'Refund processed successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to process refund';
        }

        redirect('/pos');
    }

    public function setCustomer()
    {
        if (!hasPermission('pos.view')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        $customerId = (int) ($_POST['customer_id'] ?? 0);

        if ($customerId > 0) {
            $_SESSION['active_customer_id'] = $customerId;

            try {
                $customerService = new \App\Services\CRM\CustomerService();
                $status = $customerService->getCustomerStatus($customerId);

                // Fetch full profile for UI
                $customer = \App\Models\Customer::find($customerId);

                // Get certification (simplified logic or reuse service)
                $highestCert = Database::fetchOne("
                    SELECT cc.certification_level as certification_name, 0 as level, NULL as logo_path, ca.name as agency_name
                    FROM customer_certifications cc
                    LEFT JOIN certification_agencies ca ON cc.certification_agency_id = ca.id
                    WHERE cc.customer_id = ?
                    ORDER BY cc.issue_date DESC LIMIT 1
                ", [$customerId]);

                $customer['certification'] = $highestCert;

                jsonResponse([
                    'success' => true,
                    'status' => $status,
                    'customer' => $customer
                ]);
            } catch (\Exception $e) {
                error_log("Failed to fetch customer data: " . $e->getMessage());
                // Still return success if session set, but empty data
                jsonResponse(['success' => true, 'status' => [], 'customer' => []]);
            }
        } else {
            // Clearing customer
            unset($_SESSION['active_customer_id']);
            jsonResponse(['success' => true]);
        }
    }

    public function clearCustomer()
    {
        if (!hasPermission('pos.view')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        unset($_SESSION['active_customer_id']);
        jsonResponse(['success' => true]);
    }
}
