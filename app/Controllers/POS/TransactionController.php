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
        if (!hasPermission('pos.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $products = Product::all(50, 0);
        $customers = Customer::all(100, 0);

        // Get active courses for enrollment
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT id, course_code, name, price, duration_days, max_students
            FROM courses
            WHERE is_active = 1
            ORDER BY name
        ");
        $courses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get rental equipment
        $stmt = $db->query("
            SELECT id, name, daily_rate, stock_quantity, sku
            FROM rental_equipment
            WHERE is_active = 1 AND stock_quantity > 0
            ORDER BY name
        ");
        $rentals = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get upcoming trips
        $stmt = $db->query("
            SELECT t.id, t.name, t.price, t.start_date, t.max_spots,
                   (SELECT COUNT(*) FROM trip_bookings WHERE trip_id = t.id AND status != 'cancelled') as booked_spots
            FROM trips t
            WHERE t.start_date >= CURDATE() AND t.status = 'scheduled'
            ORDER BY t.start_date
        ");
        $trips = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require __DIR__ . '/../../Views/pos/index.php';
    }
    
    public function searchProducts()
    {
        if (!hasPermission('pos.view')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }
        
        $query = sanitizeInput($_GET['q'] ?? '');
        
        if (empty($query)) {
            jsonResponse([]);
        }
        
        $products = Product::search($query, 20);
        jsonResponse($products);
    }
    
    public function checkout()
    {
        if (!hasPermission('pos.create')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }
        
        $customerId = (int)($_POST['customer_id'] ?? 0);
        $items = json_decode($_POST['items'] ?? '[]', true);
        $paymentMethod = sanitizeInput($_POST['payment_method'] ?? 'cash');
        $amountPaid = (float)($_POST['amount_paid'] ?? 0);

        // Allow empty customer_id for walk-in customers (will be set to NULL in transaction)
        if (empty($items)) {
            jsonResponse(['error' => 'Invalid transaction data - no items'], 400);
        }

        // Convert 0 to null for walk-in customers
        if ($customerId === 0) {
            $customerId = null;
        }
        
        try {
            $transactionId = $this->transactionService->createTransaction($customerId, $items);
            
            $transaction = Database::fetchOne(
                "SELECT total FROM transactions WHERE id = ?",
                [$transactionId]
            );
            
            if ($amountPaid < $transaction['total']) {
                jsonResponse(['error' => 'Insufficient payment amount'], 400);
            }
            
            $success = $this->transactionService->processPayment(
                $transactionId,
                $paymentMethod,
                $amountPaid
            );
            
            if ($success) {
                $_SESSION['flash_success'] = 'Transaction completed successfully';
                jsonResponse([
                    'success' => true,
                    'transaction_id' => $transactionId,
                    'redirect' => "/pos/receipt/{$transactionId}"
                ]);
            } else {
                jsonResponse(['error' => 'Payment processing failed'], 500);
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
        
        $amount = (float)($_POST['amount'] ?? 0);
        
        $success = $this->transactionService->refundTransaction($id, $amount);
        
        if ($success) {
            $_SESSION['flash_success'] = 'Refund processed successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to process refund';
        }
        
        redirect('/pos');
    }
}
