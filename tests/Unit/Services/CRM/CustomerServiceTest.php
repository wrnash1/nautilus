<?php

namespace Tests\Unit\Services\CRM;

use Tests\TestCase;
use App\Services\CRM\CustomerService;

class CustomerServiceTest extends TestCase
{
    private CustomerService $customerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerService = new CustomerService();
    }

    public function testCreateCustomer(): void
    {
        $customerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '555-1234',
            'customer_type' => 'b2c'
        ];

        $customerId = $this->customerService->createCustomer($customerData);

        $this->assertIsInt($customerId);
        $this->assertGreaterThan(0, $customerId);

        // Verify customer was created in database
        $this->assertDatabaseHas('customers', [
            'id' => $customerId,
            'email' => 'john.doe@example.com'
        ]);
    }

    public function testGetCustomer360(): void
    {
        // Create a test customer
        $testCustomer = $this->createTestCustomer([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com'
        ]);

        // Retrieve the customer 360 view
        $customer360 = $this->customerService->getCustomer360($testCustomer['id']);

        $this->assertIsArray($customer360);
        $this->assertArrayHasKey('customer', $customer360);
        $this->assertEquals($testCustomer['id'], $customer360['customer']['id']);
        $this->assertEquals('Jane', $customer360['customer']['first_name']);
        $this->assertEquals('Smith', $customer360['customer']['last_name']);
    }

    public function testUpdateCustomer(): void
    {
        // Create a test customer
        $testCustomer = $this->createTestCustomer();

        // Update the customer
        $updateData = [
            'first_name' => 'UpdatedName',
            'phone' => '555-9999'
        ];

        $result = $this->customerService->updateCustomer($testCustomer['id'], $updateData);

        $this->assertTrue($result);

        // Verify changes in database
        $this->assertDatabaseHas('customers', [
            'id' => $testCustomer['id'],
            'first_name' => 'UpdatedName',
            'phone' => '555-9999'
        ]);
    }

    public function testSearchCustomers(): void
    {
        // Create multiple test customers
        $this->createTestCustomer(['first_name' => 'Alice', 'last_name' => 'Anderson']);
        $this->createTestCustomer(['first_name' => 'Bob', 'last_name' => 'Brown']);
        $this->createTestCustomer(['first_name' => 'Alice', 'last_name' => 'Baker']);

        // Search for customers named Alice
        $results = $this->customerService->search('Alice');

        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(2, count($results));

        foreach ($results as $customer) {
            $this->assertStringContainsString('Alice', $customer['first_name']);
        }
    }
}
