<?php

namespace App\Services\Notifications;

use App\Core\Database;
use App\Core\Logger;

/**
 * Notification Service
 * Handles in-app notifications for users
 */
class NotificationService
{
    private Database $db;
    private Logger $logger;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new Logger();
    }

    /**
     * Create a notification
     */
    public function create(
        int $userId,
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null,
        ?array $data = null
    ): int {
        try {
            $sql = "INSERT INTO notifications (user_id, title, message, type, action_url, data, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([
                $userId,
                $title,
                $message,
                $type,
                $actionUrl,
                $data ? json_encode($data) : null
            ]);

            $notificationId = (int)$this->db->getConnection()->lastInsertId();

            $this->logger->info('Notification created', [
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'type' => $type
            ]);

            return $notificationId;
        } catch (\Exception $e) {
            $this->logger->error('Failed to create notification', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Create notifications for multiple users
     */
    public function createBulk(
        array $userIds,
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null,
        ?array $data = null
    ): int {
        $created = 0;

        foreach ($userIds as $userId) {
            if ($this->create($userId, $title, $message, $type, $actionUrl, $data)) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * Get notifications for a user
     */
    public function getForUser(int $userId, bool $unreadOnly = false, int $limit = 50): array
    {
        $sql = "SELECT * FROM notifications WHERE user_id = ?";

        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$userId, $limit]);

        $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Parse JSON data
        foreach ($notifications as &$notification) {
            if ($notification['data']) {
                $notification['data'] = json_decode($notification['data'], true);
            }
        }

        return $notifications;
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM notifications
                WHERE user_id = ? AND is_read = 0";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$userId]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int)($result['count'] ?? 0);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        try {
            $sql = "UPDATE notifications
                    SET is_read = 1, read_at = NOW()
                    WHERE id = ? AND user_id = ?";

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$notificationId, $userId]);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            $this->logger->error('Failed to mark notification as read', [
                'notification_id' => $notificationId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(int $userId): int
    {
        try {
            $sql = "UPDATE notifications
                    SET is_read = 1, read_at = NOW()
                    WHERE user_id = ? AND is_read = 0";

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$userId]);

            return $stmt->rowCount();
        } catch (\Exception $e) {
            $this->logger->error('Failed to mark all as read', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Delete a notification
     */
    public function delete(int $notificationId, int $userId): bool
    {
        try {
            $sql = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$notificationId, $userId]);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete notification', [
                'notification_id' => $notificationId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete old read notifications
     */
    public function deleteOldRead(int $daysOld = 30): int
    {
        try {
            $sql = "DELETE FROM notifications
                    WHERE is_read = 1
                    AND read_at < DATE_SUB(NOW(), INTERVAL ? DAY)";

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$daysOld]);

            return $stmt->rowCount();
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete old notifications', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Notification templates for common events
     */

    public function notifyNewOrder(int $userId, int $orderId, float $total): void
    {
        $this->create(
            $userId,
            'New Order Received',
            sprintf('You have a new order #%d for $%.2f', $orderId, $total),
            'success',
            "/orders/{$orderId}",
            ['order_id' => $orderId, 'total' => $total]
        );
    }

    public function notifyPaymentReceived(int $userId, int $transactionId, float $amount): void
    {
        $this->create(
            $userId,
            'Payment Received',
            sprintf('Payment of $%.2f has been received', $amount),
            'success',
            "/transactions/{$transactionId}",
            ['transaction_id' => $transactionId, 'amount' => $amount]
        );
    }

    public function notifyLowStock(int $userId, int $productId, string $productName, int $quantity): void
    {
        $this->create(
            $userId,
            'Low Stock Alert',
            sprintf('%s is running low (only %d remaining)', $productName, $quantity),
            'warning',
            "/products/{$productId}",
            ['product_id' => $productId, 'quantity' => $quantity]
        );
    }

    public function notifyCourseEnrollment(int $userId, int $enrollmentId, string $courseName, string $studentName): void
    {
        $this->create(
            $userId,
            'New Course Enrollment',
            sprintf('%s enrolled in %s', $studentName, $courseName),
            'info',
            "/courses/enrollments/{$enrollmentId}",
            ['enrollment_id' => $enrollmentId, 'course_name' => $courseName]
        );
    }

    public function notifyTripBooking(int $userId, int $bookingId, string $tripName, string $customerName): void
    {
        $this->create(
            $userId,
            'New Trip Booking',
            sprintf('%s booked for %s', $customerName, $tripName),
            'info',
            "/trips/bookings/{$bookingId}",
            ['booking_id' => $bookingId, 'trip_name' => $tripName]
        );
    }

    public function notifyRentalReservation(int $userId, int $reservationId, string $customerName): void
    {
        $this->create(
            $userId,
            'New Rental Reservation',
            sprintf('New equipment rental by %s', $customerName),
            'info',
            "/rentals/reservations/{$reservationId}",
            ['reservation_id' => $reservationId]
        );
    }

    public function notifyEquipmentDue(int $userId, int $equipmentId, string $equipmentName, string $dueDate): void
    {
        $this->create(
            $userId,
            'Equipment Due Back',
            sprintf('%s is due back on %s', $equipmentName, $dueDate),
            'warning',
            "/rentals/equipment/{$equipmentId}",
            ['equipment_id' => $equipmentId, 'due_date' => $dueDate]
        );
    }

    public function notifyWorkOrderAssigned(int $userId, int $workOrderId, string $description): void
    {
        $this->create(
            $userId,
            'Work Order Assigned',
            sprintf('You have been assigned a work order: %s', $description),
            'info',
            "/workorders/{$workOrderId}",
            ['work_order_id' => $workOrderId]
        );
    }

    public function notifySystemUpdate(array $userIds, string $message): void
    {
        $this->createBulk(
            $userIds,
            'System Update',
            $message,
            'info'
        );
    }
}
