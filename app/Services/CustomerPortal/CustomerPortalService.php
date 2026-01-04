<?php

namespace App\Services\CustomerPortal;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;

/**
 * Customer Portal Service
 *
 * Self-service portal for customers to view their history and manage account
 */
class CustomerPortalService
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Get customer dashboard data
     */
    public function getCustomerDashboard(int $customerId): array
    {
        try {
            // Customer basic info
            $customer = TenantDatabase::fetchOneTenant(
                "SELECT id, first_name, last_name, email, phone,
                        certification_level, total_dives, created_at
                 FROM customers
                 WHERE id = ?",
                [$customerId]
            );

            if (!$customer) {
                return ['success' => false, 'error' => 'Customer not found'];
            }

            // Purchase history summary
            $purchaseStats = TenantDatabase::fetchOneTenant(
                "SELECT
                    COUNT(*) as total_transactions,
                    COALESCE(SUM(total_amount), 0) as total_spent,
                    COALESCE(AVG(total_amount), 0) as average_order,
                    MAX(transaction_date) as last_purchase_date
                 FROM transactions
                 WHERE customer_id = ?
                 AND status = 'completed'",
                [$customerId]
            );

            // Course enrollments
            $courseStats = TenantDatabase::fetchOneTenant(
                "SELECT
                    COUNT(*) as total_courses,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_courses,
                    COUNT(CASE WHEN status = 'enrolled' THEN 1 END) as active_courses
                 FROM course_enrollments
                 WHERE customer_id = ?",
                [$customerId]
            );

            // Equipment rentals
            $rentalStats = TenantDatabase::fetchOneTenant(
                "SELECT
                    COUNT(*) as total_rentals,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_rentals,
                    MAX(rental_date) as last_rental_date
                 FROM equipment_rentals
                 WHERE customer_id = ?",
                [$customerId]
            );

            // Upcoming courses
            $upcomingCourses = TenantDatabase::fetchAllTenant(
                "SELECT c.name, c.description, ce.start_date, ce.status, ce.price
                 FROM course_enrollments ce
                 JOIN courses c ON ce.course_id = c.id
                 WHERE ce.customer_id = ?
                 AND ce.start_date >= CURDATE()
                 ORDER BY ce.start_date
                 LIMIT 5",
                [$customerId]
            );

            // Active rentals
            $activeRentals = TenantDatabase::fetchAllTenant(
                "SELECT e.name, er.rental_date, er.return_due_date,
                        er.daily_rate, er.status
                 FROM equipment_rentals er
                 JOIN equipment e ON er.equipment_id = e.id
                 WHERE er.customer_id = ?
                 AND er.status = 'active'
                 ORDER BY er.return_due_date",
                [$customerId]
            );

            return [
                'success' => true,
                'customer' => $customer,
                'stats' => [
                    'purchases' => $purchaseStats,
                    'courses' => $courseStats,
                    'rentals' => $rentalStats
                ],
                'upcoming_courses' => $upcomingCourses,
                'active_rentals' => $activeRentals
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get customer dashboard failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get customer purchase history
     */
    public function getPurchaseHistory(int $customerId, int $limit = 50, int $offset = 0): array
    {
        try {
            $transactions = TenantDatabase::fetchAllTenant(
                "SELECT
                    t.id,
                    t.transaction_number,
                    t.transaction_date,
                    t.subtotal,
                    t.tax_amount,
                    t.total_amount,
                    t.payment_method,
                    t.status
                 FROM transactions t
                 WHERE t.customer_id = ?
                 ORDER BY t.transaction_date DESC
                 LIMIT ? OFFSET ?",
                [$customerId, $limit, $offset]
            );

            // Get items for each transaction
            foreach ($transactions as &$transaction) {
                $transaction['items'] = TenantDatabase::fetchAllTenant(
                    "SELECT
                        ti.product_name,
                        ti.quantity,
                        ti.unit_price,
                        ti.line_total
                     FROM transaction_items ti
                     WHERE ti.transaction_id = ?",
                    [$transaction['id']]
                );
            }

            return [
                'success' => true,
                'transactions' => $transactions,
                'count' => count($transactions)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get purchase history failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get customer course enrollments
     */
    public function getCourseEnrollments(int $customerId): array
    {
        try {
            $enrollments = TenantDatabase::fetchAllTenant(
                "SELECT
                    ce.id,
                    c.name as course_name,
                    c.description,
                    ce.enrollment_date,
                    ce.start_date,
                    ce.end_date,
                    ce.status,
                    ce.price,
                    ce.instructor_name,
                    ce.completion_date,
                    ce.certification_number
                 FROM course_enrollments ce
                 JOIN courses c ON ce.course_id = c.id
                 WHERE ce.customer_id = ?
                 ORDER BY ce.start_date DESC",
                [$customerId]
            );

            return [
                'success' => true,
                'enrollments' => $enrollments,
                'count' => count($enrollments)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get course enrollments failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get rental history
     */
    public function getRentalHistory(int $customerId): array
    {
        try {
            $rentals = TenantDatabase::fetchAllTenant(
                "SELECT
                    er.id,
                    e.name as equipment_name,
                    e.serial_number,
                    er.rental_date,
                    er.return_due_date,
                    er.return_date,
                    er.daily_rate,
                    er.total_amount,
                    er.status,
                    er.deposit_amount
                 FROM equipment_rentals er
                 JOIN equipment e ON er.equipment_id = e.id
                 WHERE er.customer_id = ?
                 ORDER BY er.rental_date DESC",
                [$customerId]
            );

            return [
                'success' => true,
                'rentals' => $rentals,
                'count' => count($rentals)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get rental history failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update customer profile
     */
    public function updateProfile(int $customerId, array $data): array
    {
        try {
            // Allowed fields to update
            $allowedFields = [
                'phone', 'address_line1', 'address_line2',
                'city', 'state', 'postal_code', 'country',
                'emergency_contact_name', 'emergency_contact_phone',
                'medical_conditions', 'total_dives'
            ];

            $updateData = [];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (empty($updateData)) {
                return ['success' => false, 'error' => 'No data to update'];
            }

            $updateData['updated_at'] = date('Y-m-d H:i:s');

            TenantDatabase::updateTenant('customers', $updateData, 'id = ?', [$customerId]);

            $this->logger->info('Customer profile updated', ['customer_id' => $customerId]);

            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Update profile failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get certification history
     */
    public function getCertifications(int $customerId): array
    {
        try {
            $certifications = TenantDatabase::fetchAllTenant(
                "SELECT
                    ce.certification_number,
                    c.name as course_name,
                    ce.completion_date,
                    ce.instructor_name
                 FROM course_enrollments ce
                 JOIN courses c ON ce.course_id = c.id
                 WHERE ce.customer_id = ?
                 AND ce.status = 'completed'
                 AND ce.certification_number IS NOT NULL
                 ORDER BY ce.completion_date DESC",
                [$customerId]
            );

            return [
                'success' => true,
                'certifications' => $certifications,
                'count' => count($certifications)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get certifications failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Request course enrollment
     */
    public function requestCourseEnrollment(int $customerId, int $courseId, array $data): array
    {
        try {
            // Check if customer already enrolled
            $existing = TenantDatabase::fetchOneTenant(
                "SELECT id FROM course_enrollments
                 WHERE customer_id = ? AND course_id = ?
                 AND status IN ('enrolled', 'completed')",
                [$customerId, $courseId]
            );

            if ($existing) {
                return ['success' => false, 'error' => 'Already enrolled in this course'];
            }

            // Get course details
            $course = TenantDatabase::fetchOneTenant(
                "SELECT name, price FROM courses WHERE id = ?",
                [$courseId]
            );

            if (!$course) {
                return ['success' => false, 'error' => 'Course not found'];
            }

            // Create enrollment request
            $enrollmentId = TenantDatabase::insertTenant('course_enrollments', [
                'customer_id' => $customerId,
                'course_id' => $courseId,
                'enrollment_date' => date('Y-m-d'),
                'start_date' => $data['start_date'] ?? null,
                'status' => 'pending',
                'price' => $course['price'],
                'notes' => $data['notes'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->logger->info('Course enrollment requested', [
                'customer_id' => $customerId,
                'course_id' => $courseId
            ]);

            return [
                'success' => true,
                'enrollment_id' => $enrollmentId,
                'message' => 'Enrollment request submitted successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Course enrollment request failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Download invoice/receipt
     */
    public function getTransactionReceipt(int $customerId, int $transactionId): ?array
    {
        try {
            // Verify transaction belongs to customer
            $transaction = TenantDatabase::fetchOneTenant(
                "SELECT * FROM transactions
                 WHERE id = ? AND customer_id = ?",
                [$transactionId, $customerId]
            );

            if (!$transaction) {
                return null;
            }

            // Get items
            $items = TenantDatabase::fetchAllTenant(
                "SELECT * FROM transaction_items WHERE transaction_id = ?",
                [$transactionId]
            );

            $transaction['items'] = $items;

            return $transaction;

        } catch (\Exception $e) {
            $this->logger->error('Get transaction receipt failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get customer notifications/messages
     */
    public function getNotifications(int $customerId, bool $unreadOnly = false): array
    {
        try {
            $sql = "SELECT * FROM customer_notifications
                    WHERE customer_id = ?";

            if ($unreadOnly) {
                $sql .= " AND is_read = 0";
            }

            $sql .= " ORDER BY created_at DESC LIMIT 50";

            $notifications = TenantDatabase::fetchAllTenant($sql, [$customerId]);

            return [
                'success' => true,
                'notifications' => $notifications ?? [],
                'unread_count' => count(array_filter($notifications ?? [], fn($n) => !$n['is_read']))
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get notifications failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(int $customerId, int $notificationId): bool
    {
        try {
            TenantDatabase::updateTenant(
                'customer_notifications',
                ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
                'id = ? AND customer_id = ?',
                [$notificationId, $customerId]
            );

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Mark notification read failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
