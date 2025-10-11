<?php

namespace App\Services\POS;

use App\Core\Database;
use App\Models\Product;

class TransactionService
{
    private const TAX_RATE = 0.08;
    
    public function createTransaction(int $customerId, array $items): int
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        $tax = round($subtotal * self::TAX_RATE, 2);
        $total = round($subtotal + $tax, 2);
        
        $transactionNumber = 'TXN-' . date('Ymd-His') . '-' . substr(uniqid(), -4);
        
        Database::query(
            "INSERT INTO transactions (transaction_number, customer_id, subtotal, tax, total, status, cashier_id) 
             VALUES (?, ?, ?, ?, ?, 'pending', ?)",
            [$transactionNumber, $customerId, $subtotal, $tax, $total, $_SESSION['user_id']]
        );
        
        $transactionId = (int)Database::lastInsertId();
        
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $itemSubtotal = $item['price'] * $item['quantity'];
            $itemTax = $itemSubtotal * self::TAX_RATE;
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
    
    public function processPayment(int $transactionId, string $method, float $amount): bool
    {
        $transaction = Database::fetchOne(
            "SELECT * FROM transactions WHERE id = ?",
            [$transactionId]
        );
        
        if (!$transaction || $transaction['status'] !== 'pending') {
            return false;
        }
        
        if ($amount < $transaction['total']) {
            return false;
        }
        
        $paymentStatus = 'completed';
        
        Database::query(
            "INSERT INTO payments (transaction_id, payment_method, amount, status) 
             VALUES (?, ?, ?, ?)",
            [$transactionId, $method, $amount, $paymentStatus]
        );
        
        Database::query(
            "UPDATE transactions SET status = 'completed' 
             WHERE id = ?",
            [$transactionId]
        );
        
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
        
        logActivity('complete', 'transactions', $transactionId);
        
        return true;
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
}
