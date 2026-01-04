<?php

namespace App\Services\Customer;

use App\Core\Database;
use PDO;

/**
 * Customer Portal Service
 * Provides customer-facing functionality for the portal
 */
class CustomerPortalService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getPdo();
    }

    /**
     * Get customer dashboard data
     */
    public function getDashboardData(int $customerId): array
    {
        return [
            'profile' => $this->getCustomerProfile($customerId),
            'recent_orders' => $this->getRecentOrders($customerId, 5),
            'upcoming_courses' => $this->getUpcomingCourses($customerId),
            'upcoming_trips' => $this->getUpcomingTrips($customerId),
            'active_rentals' => $this->getActiveRentals($customerId),
            'certifications' => $this->getCertifications($customerId),
            'loyalty_points' => $this->getLoyaltyPoints($customerId),
            'upcoming_appointments' => $this->getUpcomingAppointments($customerId),
            'statistics' => $this->getCustomerStatistics($customerId),
        ];
    }

    /**
     * Get customer profile
     */
    private function getCustomerProfile(int $customerId): ?array
    {
        $sql = "SELECT * FROM customers WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get recent orders
     */
    private function getRecentOrders(int $customerId, int $limit = 5): array
    {
        $sql = "SELECT o.*,
                       COUNT(oi.id) as item_count
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.customer_id = ?
                GROUP BY o.id
                ORDER BY o.created_at DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get upcoming courses
     */
    private function getUpcomingCourses(int $customerId): array
    {
        $sql = "SELECT ce.*, c.name as course_name, c.course_code,
                       cs.start_date, cs.end_date, cs.location,
                       CONCAT(u.first_name, ' ', u.last_name) as instructor_name
                FROM course_enrollments ce
                JOIN course_schedules cs ON ce.schedule_id = cs.id
                JOIN courses c ON cs.course_id = c.id
                LEFT JOIN users u ON cs.instructor_id = u.id
                WHERE ce.customer_id = ?
                AND cs.start_date >= CURDATE()
                AND ce.status IN ('enrolled', 'in_progress')
                ORDER BY cs.start_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get upcoming trips
     */
    private function getUpcomingTrips(int $customerId): array
    {
        $sql = "SELECT tb.*, t.name as trip_name, t.destination,
                       ts.departure_date, ts.return_date, ts.capacity
                FROM trip_bookings tb
                JOIN trip_schedules ts ON tb.schedule_id = ts.id
                JOIN trips t ON ts.trip_id = t.id
                WHERE tb.customer_id = ?
                AND ts.departure_date >= CURDATE()
                AND tb.status IN ('confirmed', 'paid')
                ORDER BY ts.departure_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get active rentals
     */
    private function getActiveRentals(int $customerId): array
    {
        $sql = "SELECT rr.*, re.name as equipment_name, re.equipment_code
                FROM rental_reservations rr
                JOIN rental_equipment re ON rr.equipment_id = re.id
                WHERE rr.customer_id = ?
                AND rr.status IN ('confirmed', 'picked_up')
                ORDER BY rr.start_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get certifications
     */
    private function getCertifications(int $customerId): array
    {
        $sql = "SELECT cert.*, c.name as course_name, c.course_code,
                       a.name as agency_name
                FROM certifications cert
                JOIN courses c ON cert.course_id = c.id
                LEFT JOIN certification_agencies a ON cert.agency_id = a.id
                WHERE cert.customer_id = ?
                ORDER BY cert.certification_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get loyalty points
     */
    private function getLoyaltyPoints(int $customerId): array
    {
        $sql = "SELECT lpm.*, lp.name as program_name, lp.points_currency
                FROM loyalty_points_members lpm
                JOIN loyalty_programs lp ON lpm.program_id = lp.id
                WHERE lpm.customer_id = ?
                AND lp.is_active = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get upcoming appointments
     */
    private function getUpcomingAppointments(int $customerId): array
    {
        $sql = "SELECT a.*,
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
                FROM appointments a
                LEFT JOIN users u ON a.assigned_to = u.id
                WHERE a.customer_id = ?
                AND a.start_time >= NOW()
                AND a.status IN ('scheduled', 'confirmed')
                ORDER BY a.start_time ASC
                LIMIT 5";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get customer statistics
     */
    private function getCustomerStatistics(int $customerId): array
    {
        $totalOrders = Database::fetchOne(
            "SELECT COUNT(*) as count FROM orders WHERE customer_id = ? AND status != 'cancelled'",
            [$customerId]
        );

        $totalSpent = Database::fetchOne(
            "SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE customer_id = ? AND status = 'completed'",
            [$customerId]
        );

        $totalCourses = Database::fetchOne(
            "SELECT COUNT(*) as count FROM course_enrollments WHERE customer_id = ?",
            [$customerId]
        );

        $totalTrips = Database::fetchOne(
            "SELECT COUNT(*) as count FROM trip_bookings WHERE customer_id = ?",
            [$customerId]
        );

        $totalRentals = Database::fetchOne(
            "SELECT COUNT(*) as count FROM rental_reservations WHERE customer_id = ?",
            [$customerId]
        );

        return [
            'total_orders' => (int)($totalOrders['count'] ?? 0),
            'total_spent' => (float)($totalSpent['total'] ?? 0),
            'total_courses' => (int)($totalCourses['count'] ?? 0),
            'total_trips' => (int)($totalTrips['count'] ?? 0),
            'total_rentals' => (int)($totalRentals['count'] ?? 0),
        ];
    }

    /**
     * Get order details with items
     */
    public function getOrderDetails(int $customerId, int $orderId): ?array
    {
        // Verify ownership
        $sql = "SELECT o.* FROM orders o WHERE o.id = ? AND o.customer_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId, $customerId]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        // Get order items
        $sql = "SELECT oi.*, p.name as product_name, p.sku
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $order['items'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $order;
    }

    /**
     * Get all orders for customer
     */
    public function getAllOrders(int $customerId, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT o.*, COUNT(oi.id) as item_count
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.customer_id = ?
                GROUP BY o.id
                ORDER BY o.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $limit, $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Update customer profile
     */
    public function updateProfile(int $customerId, array $data): bool
    {
        $allowedFields = ['first_name', 'last_name', 'phone', 'date_of_birth'];
        $fields = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $customerId;
        $sql = "UPDATE customers SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Get dive log entries
     */
    public function getDiveLogs(int $customerId): array
    {
        // If dive log table exists
        $sql = "SELECT * FROM dive_logs WHERE customer_id = ? ORDER BY dive_date DESC";
        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute([$customerId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Request appointment
     */
    public function requestAppointment(int $customerId, array $data): int
    {
        $sql = "INSERT INTO appointments (
                    customer_id, appointment_type, start_time, end_time,
                    notes, status, created_at
                ) VALUES (?, ?, ?, ?, ?, 'scheduled', NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $customerId,
            $data['appointment_type'],
            $data['start_time'],
            $data['end_time'],
            $data['notes'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Download certification card
     */
    public function getCertificationCard(int $customerId, int $certificationId): ?array
    {
        $sql = "SELECT cert.*, c.name as course_name, c.course_code,
                       a.name as agency_name,
                       cust.first_name, cust.last_name, cust.date_of_birth
                FROM certifications cert
                JOIN courses c ON cert.course_id = c.id
                LEFT JOIN certification_agencies a ON cert.agency_id = a.id
                JOIN customers cust ON cert.customer_id = cust.id
                WHERE cert.id = ? AND cert.customer_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$certificationId, $customerId]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}
