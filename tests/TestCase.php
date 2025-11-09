<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use App\Core\Database;

/**
 * Base Test Case
 * All test classes should extend this class
 */
abstract class TestCase extends BaseTestCase
{
    protected \PDO $db;
    protected static bool $dbInitialized = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize database connection
        $this->db = Database::getInstance();

        // Set up test database once
        if (!self::$dbInitialized) {
            $this->setUpTestDatabase();
            self::$dbInitialized = true;
        }

        // Start transaction for test isolation
        $this->db->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback transaction to keep database clean
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }

        parent::tearDown();
    }

    /**
     * Set up test database schema
     */
    protected function setUpTestDatabase(): void
    {
        // This can be expanded to run migrations or seed test data
        // For now, we assume the test database schema exists
    }

    /**
     * Helper: Create a mock user for testing
     */
    protected function createTestUser(array $attributes = []): array
    {
        $defaults = [
            'username' => 'testuser_' . uniqid(),
            'email' => 'test_' . uniqid() . '@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'first_name' => 'Test',
            'last_name' => 'User',
            'role_id' => 1,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $data = array_merge($defaults, $attributes);

        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password, first_name, last_name, role_id, is_active, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $data['username'],
            $data['email'],
            $data['password'],
            $data['first_name'],
            $data['last_name'],
            $data['role_id'],
            $data['is_active'],
            $data['created_at']
        ]);

        $data['id'] = $this->db->lastInsertId();
        unset($data['password']); // Don't return password

        return $data;
    }

    /**
     * Helper: Create a test customer
     */
    protected function createTestCustomer(array $attributes = []): array
    {
        $defaults = [
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'customer_' . uniqid() . '@example.com',
            'phone' => '555-0100',
            'customer_type' => 'b2c',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $data = array_merge($defaults, $attributes);

        $stmt = $this->db->prepare(
            "INSERT INTO customers (first_name, last_name, email, phone, customer_type, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['customer_type'],
            $data['status'],
            $data['created_at']
        ]);

        $data['id'] = $this->db->lastInsertId();

        return $data;
    }

    /**
     * Helper: Create a test product
     */
    protected function createTestProduct(array $attributes = []): array
    {
        $defaults = [
            'name' => 'Test Product ' . uniqid(),
            'sku' => 'TEST-' . uniqid(),
            'category_id' => 1,
            'price' => 99.99,
            'cost' => 50.00,
            'stock_quantity' => 100,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $data = array_merge($defaults, $attributes);

        $stmt = $this->db->prepare(
            "INSERT INTO products (name, sku, category_id, price, cost, stock_quantity, is_active, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $data['name'],
            $data['sku'],
            $data['category_id'],
            $data['price'],
            $data['cost'],
            $data['stock_quantity'],
            $data['is_active'],
            $data['created_at']
        ]);

        $data['id'] = $this->db->lastInsertId();

        return $data;
    }

    /**
     * Assert that a database table has a record matching the criteria
     */
    protected function assertDatabaseHas(string $table, array $criteria): void
    {
        $conditions = [];
        $values = [];

        foreach ($criteria as $column => $value) {
            $conditions[] = "$column = ?";
            $values[] = $value;
        }

        $sql = "SELECT COUNT(*) as count FROM $table WHERE " . implode(' AND ', $conditions);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertGreaterThan(0, $result['count'],
            "Failed asserting that table '$table' has a record matching the given criteria."
        );
    }

    /**
     * Assert that a database table does not have a record matching the criteria
     */
    protected function assertDatabaseMissing(string $table, array $criteria): void
    {
        $conditions = [];
        $values = [];

        foreach ($criteria as $column => $value) {
            $conditions[] = "$column = ?";
            $values[] = $value;
        }

        $sql = "SELECT COUNT(*) as count FROM $table WHERE " . implode(' AND ', $conditions);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals(0, $result['count'],
            "Failed asserting that table '$table' does not have a record matching the given criteria."
        );
    }
}
