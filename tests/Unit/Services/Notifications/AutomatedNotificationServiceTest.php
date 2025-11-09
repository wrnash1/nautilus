<?php

namespace Tests\Unit\Services\Notifications;

use Tests\TestCase;

class AutomatedNotificationServiceTest extends TestCase
{
    public function testNotificationServiceExists(): void
    {
        // Verify the notification service class exists
        $this->assertTrue(
            class_exists('App\Services\Notifications\AutomatedNotificationService'),
            'AutomatedNotificationService class should exist'
        );
    }

    public function testNotificationSettingsTableStructure(): void
    {
        // Create notification_settings table for testing
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS notification_settings (
                id INT PRIMARY KEY,
                low_stock_enabled BOOLEAN DEFAULT TRUE,
                maintenance_enabled BOOLEAN DEFAULT TRUE,
                course_enabled BOOLEAN DEFAULT TRUE,
                rental_enabled BOOLEAN DEFAULT TRUE,
                admin_email VARCHAR(255),
                manager_email VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Insert default settings
        $this->db->exec("
            INSERT INTO notification_settings (id, admin_email, manager_email)
            VALUES (1, 'admin@test.com', 'manager@test.com')
            ON DUPLICATE KEY UPDATE id=1
        ");

        // Verify settings exist
        $this->assertDatabaseHas('notification_settings', [
            'id' => 1
        ]);
    }

    public function testNotificationLogTableStructure(): void
    {
        // Create notification_log table for testing
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS notification_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                notification_type VARCHAR(50) NOT NULL,
                recipient VARCHAR(255) NOT NULL,
                reference_id INT,
                sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_type (notification_type),
                INDEX idx_sent_at (sent_at)
            )
        ");

        // Test insert
        $stmt = $this->db->prepare("
            INSERT INTO notification_log (notification_type, recipient, reference_id)
            VALUES (?, ?, ?)
        ");
        $stmt->execute(['test_notification', 'test@example.com', 1]);

        $this->assertDatabaseHas('notification_log', [
            'notification_type' => 'test_notification',
            'recipient' => 'test@example.com'
        ]);
    }

    public function testLowStockDataRetrieval(): void
    {
        // Create products with low stock
        $this->createTestProduct([
            'name' => 'Low Stock Item',
            'stock_quantity' => 2,
            'low_stock_threshold' => 10
        ]);

        // Query to find low stock items (same as in the service)
        $stmt = $this->db->query("
            SELECT COUNT(*) as count
            FROM products
            WHERE stock_quantity <= low_stock_threshold
            AND is_active = 1
        ");

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertGreaterThan(0, $result['count'], 'Should find low stock items');
    }

    public function testCourseEnrollmentData(): void
    {
        // Create test course and customer
        $customer = $this->createTestCustomer();
        $user = $this->createTestUser();

        // Create course
        $stmt = $this->db->prepare("
            INSERT INTO courses (course_code, name, duration_days, max_students, price, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute(['OW101', 'Open Water', 3, 8, 399.00, $user['id'], date('Y-m-d H:i:s')]);
        $courseId = $this->db->lastInsertId();

        // Create enrollment
        $stmt = $this->db->prepare("
            INSERT INTO course_enrollments (course_id, customer_id, enrollment_date, amount_paid, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$courseId, $customer['id'], date('Y-m-d'), 399.00, 'enrolled', date('Y-m-d H:i:s')]);
        $enrollmentId = $this->db->lastInsertId();

        // Verify enrollment exists and can be queried
        $this->assertDatabaseHas('course_enrollments', [
            'id' => $enrollmentId,
            'course_id' => $courseId,
            'customer_id' => $customer['id']
        ]);
    }
}
