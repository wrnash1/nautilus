<?php

namespace App\Controllers\Admin;

use App\Core\Database;

class DashboardController
{
    public function index()
    {
        $metrics = [
            'today_sales' => $this->getTodaySales(),
            'total_sales' => $this->getTotalSales(),
            'total_customers' => $this->getTotalCustomers(),
            'low_stock_count' => $this->getLowStockCount(),
            'total_products' => $this->getTotalProducts(),
            'active_rentals' => $this->getActiveRentals(),
            'upcoming_courses' => $this->getUpcomingCoursesCount(),
            'upcoming_trips' => $this->getUpcomingTripsCount(),
            'equipment_maintenance' => $this->getEquipmentMaintenanceCount(),
            'pending_certifications' => $this->getPendingCertifications(),
            'today_air_fills' => $this->getTodayAirFills(),
            'open_cash_sessions' => $this->getOpenCashSessions(),
            'today_cash_variance' => $this->getTodayCashVariance(),
            'new_customers_this_month' => $this->getNewCustomersThisMonth(),
        ];

        // Add trend data
        $metrics['sales_trend'] = $this->getSalesTrend();
        $metrics['customer_trend'] = $this->getCustomerTrend();

        $recent_transactions = $this->getRecentTransactions(10);
        $sales_chart_data = $this->getSalesChartData(7);
        $revenue_breakdown = $this->getRevenueBreakdown();
        $equipment_status = $this->getEquipmentStatus();
        $upcoming_events = $this->getUpcomingEvents();
        $alerts = $this->getAlerts();
        $top_products = $this->getTopProducts(5);

        require __DIR__ . '/../../Views/dashboard/index.php';
    }
    
    private function getTodaySales(): float
    {
        $result = Database::fetchOne(
            "SELECT COALESCE(SUM(total), 0) as total 
             FROM transactions 
             WHERE DATE(created_at) = CURDATE() AND status = 'completed'"
        );
        return (float)($result['total'] ?? 0);
    }
    
    private function getTotalCustomers(): int
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count FROM customers WHERE is_active = 1"
        );
        return (int)($result['count'] ?? 0);
    }
    
    private function getLowStockCount(): int
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count FROM products 
             WHERE track_inventory = 1 
             AND stock_quantity <= low_stock_threshold 
             AND is_active = 1"
        );
        return (int)($result['count'] ?? 0);
    }
    
    private function getTotalProducts(): int
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count FROM products WHERE is_active = 1"
        );
        return (int)($result['count'] ?? 0);
    }
    
    private function getRecentTransactions(int $limit): array
    {
        return Database::fetchAll(
            "SELECT t.*, c.first_name, c.last_name
             FROM transactions t
             LEFT JOIN customers c ON t.customer_id = c.id
             WHERE t.status = 'completed'
             ORDER BY t.created_at DESC
             LIMIT ?",
            [$limit]
        ) ?? [];
    }
    
    private function getSalesChartData(int $days): array
    {
        return Database::fetchAll(
            "SELECT DATE(created_at) as date, COALESCE(SUM(total), 0) as total
             FROM transactions
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             AND status = 'completed'
             GROUP BY DATE(created_at)
             ORDER BY date",
            [$days]
        ) ?? [];
    }
    
    private function getTotalSales(): float
    {
        $result = Database::fetchOne(
            "SELECT COALESCE(SUM(total), 0) as total
             FROM transactions
             WHERE MONTH(created_at) = MONTH(CURDATE())
             AND YEAR(created_at) = YEAR(CURDATE())
             AND status = 'completed'"
        );
        return (float)($result['total'] ?? 0);
    }

    private function getActiveRentals(): int
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM rental_reservations
             WHERE status IN ('confirmed', 'picked_up')"
        );
        return (int)($result['count'] ?? 0);
    }

    private function getUpcomingCoursesCount(): int
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM course_schedules
             WHERE status = 'scheduled'
             AND start_date >= CURDATE()"
        );
        return (int)($result['count'] ?? 0);
    }

    private function getUpcomingTripsCount(): int
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM trip_schedules
             WHERE status IN ('scheduled', 'confirmed')
             AND departure_date >= CURDATE()"
        );
        return (int)($result['count'] ?? 0);
    }

    private function getEquipmentMaintenanceCount(): int
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM rental_equipment
             WHERE status IN ('maintenance', 'damaged')"
        );
        return (int)($result['count'] ?? 0);
    }

    private function getPendingCertifications(): int
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM course_enrollments
             WHERE status = 'completed'
             AND (certification_number IS NULL OR certification_number = '')"
        );
        return (int)($result['count'] ?? 0);
    }

    private function getTodayAirFills(): int
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM air_fills
             WHERE DATE(created_at) = CURDATE()"
        );
        return (int)($result['count'] ?? 0);
    }

    private function getSalesTrend(): float
    {
        $thisMonth = Database::fetchOne(
            "SELECT COALESCE(SUM(total), 0) as total
             FROM transactions
             WHERE MONTH(created_at) = MONTH(CURDATE())
             AND YEAR(created_at) = YEAR(CURDATE())
             AND status = 'completed'"
        );

        $lastMonth = Database::fetchOne(
            "SELECT COALESCE(SUM(total), 0) as total
             FROM transactions
             WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
             AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
             AND status = 'completed'"
        );

        $thisTotal = (float)($thisMonth['total'] ?? 0);
        $lastTotal = (float)($lastMonth['total'] ?? 0);

        if ($lastTotal == 0) return 0;

        return (($thisTotal - $lastTotal) / $lastTotal) * 100;
    }

    private function getCustomerTrend(): float
    {
        $thisMonth = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM customers
             WHERE MONTH(created_at) = MONTH(CURDATE())
             AND YEAR(created_at) = YEAR(CURDATE())"
        );

        $lastMonth = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM customers
             WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
             AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))"
        );

        $thisCount = (int)($thisMonth['count'] ?? 0);
        $lastCount = (int)($lastMonth['count'] ?? 0);

        if ($lastCount == 0) return $thisCount > 0 ? 100 : 0;

        return (($thisCount - $lastCount) / $lastCount) * 100;
    }

    private function getRevenueBreakdown(): array
    {
        // Revenue by source
        $retail = Database::fetchOne(
            "SELECT COALESCE(SUM(total), 0) as total
             FROM transactions
             WHERE MONTH(created_at) = MONTH(CURDATE())
             AND status = 'completed'
             AND transaction_type = 'sale'"
        );

        $rentals = Database::fetchOne(
            "SELECT COALESCE(SUM(total_cost), 0) as total
             FROM rental_reservations
             WHERE MONTH(created_at) = MONTH(CURDATE())
             AND status IN ('picked_up', 'returned')"
        );

        $courses = Database::fetchOne(
            "SELECT COALESCE(SUM(ce.amount_paid), 0) as total
             FROM course_enrollments ce
             JOIN course_schedules cs ON ce.schedule_id = cs.id
             WHERE MONTH(cs.start_date) = MONTH(CURDATE())"
        );

        $trips = Database::fetchOne(
            "SELECT COALESCE(SUM(total_amount), 0) as total
             FROM trip_bookings
             WHERE MONTH(created_at) = MONTH(CURDATE())
             AND status IN ('confirmed', 'completed')"
        );

        $airFills = Database::fetchOne(
            "SELECT COALESCE(SUM(cost), 0) as total
             FROM air_fills
             WHERE MONTH(created_at) = MONTH(CURDATE())"
        );

        return [
            'labels' => ['Retail Sales', 'Rentals', 'Courses', 'Trips', 'Air Fills'],
            'values' => [
                (float)($retail['total'] ?? 0),
                (float)($rentals['total'] ?? 0),
                (float)($courses['total'] ?? 0),
                (float)($trips['total'] ?? 0),
                (float)($airFills['total'] ?? 0)
            ]
        ];
    }

    private function getEquipmentStatus(): array
    {
        $results = Database::fetchAll(
            "SELECT status, COUNT(*) as count
             FROM rental_equipment
             GROUP BY status"
        );

        $statusMap = [
            'available' => 0,
            'rented' => 0,
            'maintenance' => 0,
            'damaged' => 0
        ];

        foreach ($results as $row) {
            if (isset($statusMap[$row['status']])) {
                $statusMap[$row['status']] = (int)$row['count'];
            }
        }

        return [
            'labels' => ['Available', 'Rented', 'Maintenance', 'Damaged'],
            'values' => array_values($statusMap)
        ];
    }

    private function getUpcomingEvents(): array
    {
        $events = [];

        // Upcoming courses
        $courses = Database::fetchAll(
            "SELECT cs.*, c.name as course_name, c.course_code,
                    CONCAT(u.first_name, ' ', u.last_name) as instructor_name
             FROM course_schedules cs
             JOIN courses c ON cs.course_id = c.id
             LEFT JOIN users u ON cs.instructor_id = u.id
             WHERE cs.status = 'scheduled'
             AND cs.start_date >= CURDATE()
             ORDER BY cs.start_date
             LIMIT 5"
        );

        foreach ($courses as $course) {
            $events[] = [
                'type' => 'course',
                'title' => $course['course_name'],
                'date' => $course['start_date'],
                'meta' => 'Instructor: ' . ($course['instructor_name'] ?? 'TBD'),
                'link' => '/courses/schedules/' . $course['id']
            ];
        }

        // Upcoming trips
        $trips = Database::fetchAll(
            "SELECT ts.*, t.name as trip_name, t.destination
             FROM trip_schedules ts
             JOIN trips t ON ts.trip_id = t.id
             WHERE ts.status IN ('scheduled', 'confirmed')
             AND ts.departure_date >= CURDATE()
             ORDER BY ts.departure_date
             LIMIT 5"
        );

        foreach ($trips as $trip) {
            $events[] = [
                'type' => 'trip',
                'title' => $trip['trip_name'],
                'date' => $trip['departure_date'],
                'meta' => 'Destination: ' . $trip['destination'],
                'link' => '/trips/schedules/' . $trip['id']
            ];
        }

        // Sort by date
        usort($events, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return array_slice($events, 0, 8);
    }

    private function getAlerts(): array
    {
        $alerts = [];

        // Equipment needing inspection
        $overdueInspections = Database::fetchAll(
            "SELECT * FROM rental_equipment
             WHERE next_inspection_due < CURDATE()
             AND status != 'retired'
             LIMIT 5"
        );

        foreach ($overdueInspections as $equipment) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Equipment Inspection Overdue',
                'message' => $equipment['name'] . ' (' . $equipment['equipment_code'] . ') needs inspection',
                'link' => '/rentals/equipment/' . $equipment['id']
            ];
        }

        // Pending certifications
        $pendingCerts = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM course_enrollments
             WHERE status = 'completed'
             AND (certification_number IS NULL OR certification_number = '')"
        );

        if ($pendingCerts['count'] > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Pending Certifications',
                'message' => $pendingCerts['count'] . ' certification(s) need to be issued',
                'link' => '/courses/enrollments?status=completed'
            ];
        }

        // Low stock items
        $lowStock = Database::fetchOne(
            "SELECT COUNT(*) as count FROM products
             WHERE track_inventory = 1
             AND stock_quantity <= low_stock_threshold
             AND is_active = 1"
        );

        if ($lowStock['count'] > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Low Stock Alert',
                'message' => $lowStock['count'] . ' product(s) are running low on stock',
                'link' => '/reports/low-stock'
            ];
        }

        return $alerts;
    }

    private function getTopProducts(int $limit): array
    {
        return Database::fetchAll(
            "SELECT p.name, p.sku, SUM(ti.quantity) as total_sold,
                    SUM(ti.total) as revenue
             FROM transaction_items ti
             JOIN products p ON ti.product_id = p.id
             JOIN transactions t ON ti.transaction_id = t.id
             WHERE t.status = 'completed'
             AND t.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY p.id, p.name, p.sku
             ORDER BY total_sold DESC
             LIMIT ?",
            [$limit]
        ) ?? [];
    }

    public function salesMetrics()
    {
        // For AJAX endpoint if needed
        header('Content-Type: application/json');
        echo json_encode([
            'today_sales' => $this->getTodaySales(),
            'total_sales' => $this->getTotalSales(),
            'trend' => $this->getSalesTrend()
        ]);
    }

    public function inventoryStatus()
    {
        // For AJAX endpoint if needed
        header('Content-Type: application/json');
        echo json_encode($this->getEquipmentStatus());
    }

    public function upcomingCourses()
    {
        // For AJAX endpoint if needed
        header('Content-Type: application/json');
        echo json_encode($this->getUpcomingEvents());
    }

    private function getOpenCashSessions(): int
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count FROM cash_drawer_sessions WHERE status = 'open'"
        );
        return (int)($result['count'] ?? 0);
    }

    private function getTodayCashVariance(): float
    {
        $result = Database::fetchOne(
            "SELECT COALESCE(SUM(ABS(difference)), 0) as total_variance
             FROM cash_drawer_sessions
             WHERE DATE(closed_at) = CURDATE()
             AND status IN ('over', 'short')"
        );
        return (float)($result['total_variance'] ?? 0);
    }

    private function getNewCustomersThisMonth(): int
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM customers
             WHERE YEAR(created_at) = YEAR(CURDATE())
             AND MONTH(created_at) = MONTH(CURDATE())"
        );
        return (int)($result['count'] ?? 0);
    }
}
