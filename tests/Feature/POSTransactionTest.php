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

        // Create transaction data
        $transactionData = [
            'customer_id' => $customer['id'],
            'user_id' => $user['id'],
            'items' => [
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
            ],
            'payment_method' => 'cash',
            'tax_rate' => 8.5
        ];

        // Process transaction
        $transactionId = $this->transactionService->processTransaction($transactionData);

        $this->assertIsInt($transactionId);
        $this->assertGreaterThan(0, $transactionId);

        // Verify transaction was created
        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'customer_id' => $customer['id']
        ]);

        // Verify stock was decreased
        $updatedProduct1 = $this->db->getConnection()->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $updatedProduct1->execute([$product1['id']]);
        $result1 = $updatedProduct1->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals(98, $result1['stock_quantity']); // 100 - 2

        $updatedProduct2 = $this->db->getConnection()->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $updatedProduct2->execute([$product2['id']]);
        $result2 = $updatedProduct2->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals(49, $result2['stock_quantity']); // 50 - 1
    }

    public function testTransactionWithInsufficientStock(): void
    {
        $customer = $this->createTestCustomer();
        $product = $this->createTestProduct(['stock_quantity' => 5]);
        $user = $this->createTestUser();

        $transactionData = [
            'customer_id' => $customer['id'],
            'user_id' => $user['id'],
            'items' => [
                [
                    'product_id' => $product['id'],
                    'quantity' => 10, // More than available
                    'price' => 50.00
                ]
            ],
            'payment_method' => 'cash'
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->transactionService->processTransaction($transactionData);
    }

    public function testRefundTransaction(): void
    {
        // Create and complete a transaction first
        $customer = $this->createTestCustomer();
        $product = $this->createTestProduct(['price' => 100.00, 'stock_quantity' => 50]);
        $user = $this->createTestUser();

        $transactionData = [
            'customer_id' => $customer['id'],
            'user_id' => $user['id'],
            'items' => [
                ['product_id' => $product['id'], 'quantity' => 2, 'price' => 100.00]
            ],
            'payment_method' => 'credit_card'
        ];

        $transactionId = $this->transactionService->processTransaction($transactionData);

        // Now process refund
        $refundId = $this->transactionService->processRefund($transactionId, $user['id'], 'Customer request');

        $this->assertIsInt($refundId);
        $this->assertGreaterThan(0, $refundId);

        // Verify refund record
        $this->assertDatabaseHas('refunds', [
            'transaction_id' => $transactionId,
            'processed_by' => $user['id']
        ]);

        // Verify stock was returned
        $updatedProduct = $this->db->getConnection()->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $updatedProduct->execute([$product['id']]);
        $result = $updatedProduct->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals(50, $result['stock_quantity']); // Back to original
    }
}
