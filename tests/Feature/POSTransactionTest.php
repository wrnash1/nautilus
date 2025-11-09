<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\POS\TransactionService;

class POSTransactionTest extends TestCase
{
    private TransactionService $transactionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transactionService = new TransactionService();
    }

    public function testCompleteTransaction(): void
    {
        // Create test data
        $customer = $this->createTestCustomer();
        $product1 = $this->createTestProduct(['price' => 50.00, 'stock_quantity' => 100]);
        $product2 = $this->createTestProduct(['price' => 30.00, 'stock_quantity' => 50]);
        $user = $this->createTestUser();

        // Set session user for the transaction
        $_SESSION['user_id'] = $user['id'];

        // Create transaction (without payment)
        $items = [
            [
                'product_id' => $product1['id'],
                'quantity' => 2,
                'price' => 50.00
            ],
            [
                'product_id' => $product2['id'],
                'quantity' => 1,
                'price' => 30.00
            ]
        ];

        $transactionId = $this->transactionService->createTransaction($customer['id'], $items);

        $this->assertIsInt($transactionId);
        $this->assertGreaterThan(0, $transactionId);

        // Verify transaction was created
        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'customer_id' => $customer['id'],
            'status' => 'pending'
        ]);

        // Process payment to complete transaction
        $transaction = $this->db->prepare("SELECT total FROM transactions WHERE id = ?");
        $transaction->execute([$transactionId]);
        $txn = $transaction->fetch(\PDO::FETCH_ASSOC);

        $paymentResult = $this->transactionService->processPayment($transactionId, 'credit_card', $txn['total']);
        $this->assertTrue($paymentResult);

        // Verify stock was decreased after payment
        $updatedProduct1 = $this->db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $updatedProduct1->execute([$product1['id']]);
        $result1 = $updatedProduct1->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals(98, $result1['stock_quantity']); // 100 - 2

        $updatedProduct2 = $this->db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $updatedProduct2->execute([$product2['id']]);
        $result2 = $updatedProduct2->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals(49, $result2['stock_quantity']); // 50 - 1
    }

    public function testVoidTransaction(): void
    {
        $customer = $this->createTestCustomer();
        $product = $this->createTestProduct(['price' => 50.00, 'stock_quantity' => 100]);
        $user = $this->createTestUser();

        $_SESSION['user_id'] = $user['id'];

        // Create and complete a transaction
        $items = [
            ['product_id' => $product['id'], 'quantity' => 2, 'price' => 50.00]
        ];

        $transactionId = $this->transactionService->createTransaction($customer['id'], $items);

        $transaction = $this->db->prepare("SELECT total FROM transactions WHERE id = ?");
        $transaction->execute([$transactionId]);
        $txn = $transaction->fetch(\PDO::FETCH_ASSOC);

        $this->transactionService->processPayment($transactionId, 'cash', $txn['total']);

        // Now void it
        $result = $this->transactionService->voidTransaction($transactionId, 'Test void');

        $this->assertTrue($result);

        // Verify transaction is voided
        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'status' => 'voided'
        ]);

        // Verify stock was returned
        $updatedProduct = $this->db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $updatedProduct->execute([$product['id']]);
        $result = $updatedProduct->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals(100, $result['stock_quantity']); // Back to original
    }

    public function testRefundTransaction(): void
    {
        // Create and complete a transaction first
        $customer = $this->createTestCustomer();
        $product = $this->createTestProduct(['price' => 100.00, 'stock_quantity' => 50]);
        $user = $this->createTestUser();

        $_SESSION['user_id'] = $user['id'];

        $items = [
            ['product_id' => $product['id'], 'quantity' => 2, 'price' => 100.00]
        ];

        $transactionId = $this->transactionService->createTransaction($customer['id'], $items);

        $transaction = $this->db->prepare("SELECT total FROM transactions WHERE id = ?");
        $transaction->execute([$transactionId]);
        $txn = $transaction->fetch(\PDO::FETCH_ASSOC);

        $this->transactionService->processPayment($transactionId, 'credit_card', $txn['total']);

        // Now process refund
        $refundResult = $this->transactionService->refundTransaction($transactionId, $txn['total']);

        $this->assertTrue($refundResult);

        // Verify transaction is refunded
        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'status' => 'refunded'
        ]);

        // Verify stock was returned
        $updatedProduct = $this->db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $updatedProduct->execute([$product['id']]);
        $result = $updatedProduct->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals(50, $result['stock_quantity']); // Back to original
    }
}
