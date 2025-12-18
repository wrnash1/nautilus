<?php

namespace App\Services\POS;

use App\Core\Database;
use App\Models\Product;
use App\Services\Courses\EnrollmentService;

class TransactionService
{
    public function getTaxRate(): float
    {
        try {
            $setting = Database::fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'tax_rate'");
            return $setting ? (float)$setting['setting_value'] : 0.08;
        } catch (\Exception $e) {
            return 0.08;
        }
    }

    public function createTransaction(?int $customerId, array $items, string $type = 'sale'): int
    {
        $taxRate = $this->getTaxRate();
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        $tax = round($subtotal * $taxRate, 2);
        $total = round($subtotal + $tax, 2);
        
        $transactionNumber = 'TXN-' . date('Ymd-His') . '-' . substr(uniqid(), -4);
        
        $status = 'pending';

        Database::query(
            "INSERT INTO transactions (transaction_number, customer_id, subtotal, tax, total, status, cashier_id, transaction_type) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$transactionNumber, $customerId, $subtotal, $tax, $total, $status, $_SESSION['user_id'], $type]
        );
        
        $transactionId = (int)Database::lastInsertId();
        
        $transactionId = (int)Database::lastInsertId();
        
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $itemSubtotal = $item['price'] * $item['quantity'];
            $itemTax = $itemSubtotal * $taxRate;
            $itemTotal = $itemSubtotal + $itemTax;
            
            Database::query(
                "INSERT INTO transaction_items (transaction_id, product_id, item_name, 
                 item_sku, quantity, unit_price, tax, total) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $transactionId,
                    $item['product_id'],
                    $product['name'] ?? 'Unknown Product',
                    $product['sku'] ?? '',
                    $item['quantity'],
                    $item['price'],
                    $itemTax,
                    $itemTotal
                ]
            );
        }
        
        logActivity('create', 'transactions', $transactionId);
        
        return $transactionId;
    }
    
    public function processPayment(int $transactionId, string $method, float $amount, ?string $note = null): bool
    {
        $transaction = Database::fetchOne(
            "SELECT * FROM transactions WHERE id = ?",
            [$transactionId]
        );
        
        if (!$transaction) {
            return false;
        }

        if ($transaction['transaction_type'] === 'quote') {
            Database::query(
                "UPDATE transactions SET notes = ? WHERE id = ?",
                [$note, $transactionId]
            );
            return true;
        }
        
        if ($transaction['transaction_type'] === 'layaway') {
            if ($amount > 0) {
                 Database::query(
                    "INSERT INTO payments (transaction_id, payment_method, amount, status) 
                     VALUES (?, ?, ?, 'completed')",
                    [$transactionId, $method, $amount]
                );
                
                if ($method === 'cash') {
                    $this->recordCashDrawerTransaction($transactionId, $amount);
                }
            }
            
            Database::query(
                "UPDATE transactions SET notes = ? WHERE id = ?",
                [$note, $transactionId]
            );
            
            return true;
        }

        if ($transaction['status'] !== 'pending') {
            return false;
        }
        
        // Use epsilon for float comparison to avoid precision issues
        if (($transaction['total'] - $amount) > 0.005) {
            return false;
        }
        
        $paymentStatus = 'completed';

        // Cap recorded payment at transaction total to prevent negative balances (credit)
        // when change is given for cash payments
        $recordedAmount = $amount;
        if ($amount > $transaction['total']) {
            $recordedAmount = $transaction['total'];
        }
        
        Database::query(
            "INSERT INTO payments (transaction_id, payment_method, amount, status) 
             VALUES (?, ?, ?, ?)",
            [$transactionId, $method, $recordedAmount, $paymentStatus]
        );
        
        Database::query(
            "UPDATE transactions SET status = 'completed', notes = ?
             WHERE id = ?",
            [$note, $transactionId]
        );

        // Record cash drawer transaction if payment is cash and there's an open session
        if ($method === 'cash') {
            $this->recordCashDrawerTransaction($transactionId, $recordedAmount);
        }

        $items = Database::fetchAll(
            "SELECT product_id, quantity FROM transaction_items WHERE transaction_id = ?",
            [$transactionId]
        );

        foreach ($items as $item) {
            Product::adjustStock(
                $item['product_id'],
                -$item['quantity'],
                'sale',
                $transactionId
            );
        }

        // Handle course enrollments if customer is specified
        if ($transaction['customer_id']) {
            $this->processCourseEnrollments($transactionId, $transaction['customer_id'], $items);
        }

        logActivity('complete', 'transactions', $transactionId);

        return true;
    }

    /**
     * Process course enrollments from transaction items
     */
    private function processCourseEnrollments(int $transactionId, int $customerId, array $items): void
    {
        try {
            foreach ($items as $item) {
                // Check if this is a course purchase (has schedule_id)
                if (isset($item['schedule_id']) && $item['schedule_id']) {
                    $this->enrollmentService->enrollFromTransaction(
                        $customerId,
                        $item['schedule_id'],
                        $item['total'], // Amount paid for this course
                        $transactionId
                    );
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail the transaction
            error_log("Failed to process course enrollment: " . $e->getMessage());
        }
    }
    
    public function voidTransaction(int $transactionId, string $reason): bool
    {
        $transaction = Database::fetchOne(
            "SELECT * FROM transactions WHERE id = ?",
            [$transactionId]
        );
        
        if (!$transaction) {
            return false;
        }
        
        Database::query(
            "UPDATE transactions SET status = 'voided', notes = ? WHERE id = ?",
            [$reason, $transactionId]
        );
        
        if ($transaction['status'] === 'completed') {
            $items = Database::fetchAll(
                "SELECT product_id, quantity FROM transaction_items WHERE transaction_id = ?",
                [$transactionId]
            );
            
            foreach ($items as $item) {
                Product::adjustStock(
                    $item['product_id'],
                    $item['quantity'],
                    'void',
                    $transactionId
                );
            }
        }
        
        logActivity('void', 'transactions', $transactionId);
        
        return true;
    }
    
    public function refundTransaction(int $transactionId, float $amount): bool
    {
        $transaction = Database::fetchOne(
            "SELECT * FROM transactions WHERE id = ?",
            [$transactionId]
        );
        
        if (!$transaction || $transaction['status'] !== 'completed') {
            return false;
        }
        
        if ($amount > $transaction['total']) {
            return false;
        }
        
        Database::query(
            "INSERT INTO payments (transaction_id, payment_method, amount, status) 
             VALUES (?, 'refund', ?, 'completed')",
            [$transactionId, -$amount]
        );
        
        if ($amount >= $transaction['total']) {
            Database::query(
                "UPDATE transactions SET status = 'refunded' WHERE id = ?",
                [$transactionId]
            );
            
            $items = Database::fetchAll(
                "SELECT product_id, quantity FROM transaction_items WHERE transaction_id = ?",
                [$transactionId]
            );
            
            foreach ($items as $item) {
                Product::adjustStock(
                    $item['product_id'],
                    $item['quantity'],
                    'refund',
                    $transactionId
                );
            }
        }
        
        logActivity('refund', 'transactions', $transactionId);
        
        return true;
    }
    
    public function getTransaction(int $transactionId): ?array
    {
        return Database::fetchOne(
            "SELECT t.*, c.first_name, c.last_name, c.email,
                    u.first_name as cashier_name
             FROM transactions t
             LEFT JOIN customers c ON t.customer_id = c.id
             LEFT JOIN users u ON t.cashier_id = u.id
             WHERE t.id = ?",
            [$transactionId]
        );
    }
    
    public function getTransactionItems(int $transactionId): array
    {
        return Database::fetchAll(
            "SELECT ti.*, p.name as product_name, p.sku
             FROM transaction_items ti
             LEFT JOIN products p ON ti.product_id = p.id
             WHERE ti.transaction_id = ?",
            [$transactionId]
        ) ?? [];
    }

    /**
     * Record a cash drawer transaction for POS sales
     * Automatically links to the current open cash drawer session
     */
    private function recordCashDrawerTransaction(int $transactionId, float $amount): void
    {
        try {
            // Find the current user's open cash drawer session
            $session = Database::fetchOne("
                SELECT cds.id, cd.id as drawer_id
                FROM cash_drawer_sessions cds
                INNER JOIN cash_drawers cd ON cds.drawer_id = cd.id
                WHERE cds.status = 'open'
                AND cds.user_id = ?
                ORDER BY cds.opened_at DESC
                LIMIT 1
            ", [$_SESSION['user_id']]);

            if (!$session) {
                // No open session for this user - log but don't fail
                error_log("No open cash drawer session for user {$_SESSION['user_id']} during transaction {$transactionId}");
                return;
            }

            // Get transaction details for description
            $transaction = Database::fetchOne("
                SELECT transaction_number, total
                FROM transactions
                WHERE id = ?
            ", [$transactionId]);

            $description = "POS Sale - " . ($transaction['transaction_number'] ?? "Transaction #{$transactionId}");

            // Record the cash drawer transaction
            Database::query("
                INSERT INTO cash_drawer_transactions (
                    session_id,
                    transaction_type,
                    amount,
                    payment_method,
                    description,
                    reference_type,
                    reference_id,
                    created_by
                ) VALUES (?, 'sale', ?, 'cash', ?, 'transaction', ?, ?)
            ", [
                $session['id'],
                $amount,
                $description,
                $transactionId,
                $_SESSION['user_id']
            ]);

            error_log("Cash drawer transaction recorded for POS sale #{$transactionId}, session {$session['id']}");

        } catch (\Exception $e) {
            // Log error but don't fail the sale
            error_log("Failed to record cash drawer transaction: " . $e->getMessage());
        }
    }
}
