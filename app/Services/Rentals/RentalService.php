<?php

namespace App\Services\Rentals;

use App\Core\Database;

class RentalService
{
    public function getEquipmentList(array $filters = []): array
    {
        $sql = "SELECT re.*, rc.name as category_name 
                FROM rental_equipment re
                LEFT JOIN rental_categories rc ON re.category_id = rc.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (re.name LIKE ? OR re.equipment_code LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND re.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND re.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY re.equipment_code ASC";
        
        return Database::fetchAll($sql, $params);
    }
    
    public function getEquipmentById(int $id): ?array
    {
        $sql = "SELECT re.*, rc.name as category_name 
                FROM rental_equipment re
                LEFT JOIN rental_categories rc ON re.category_id = rc.id
                WHERE re.id = ?";
        
        return Database::fetch($sql, [$id]);
    }
    
    public function createEquipment(array $data): int
    {
        $sql = "INSERT INTO rental_equipment 
                (category_id, equipment_code, name, size, manufacturer, model, serial_number, 
                 purchase_date, purchase_cost, daily_rate, weekly_rate, status, `condition`, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        Database::execute($sql, [
            $data['category_id'],
            $data['equipment_code'],
            $data['name'],
            $data['size'] ?? null,
            $data['manufacturer'] ?? null,
            $data['model'] ?? null,
            $data['serial_number'] ?? null,
            $data['purchase_date'] ?? null,
            $data['purchase_cost'] ?? null,
            $data['daily_rate'],
            $data['weekly_rate'] ?? null,
            $data['status'] ?? 'available',
            $data['condition'] ?? 'good',
            $data['notes'] ?? null,
            $_SESSION['user_id'] ?? 1
        ]);
        
        return Database::lastInsertId();
    }
    
    public function updateEquipment(int $id, array $data): bool
    {
        $sql = "UPDATE rental_equipment 
                SET category_id = ?, name = ?, size = ?, manufacturer = ?, model = ?, 
                    serial_number = ?, purchase_date = ?, purchase_cost = ?, daily_rate = ?, 
                    weekly_rate = ?, status = ?, `condition` = ?, notes = ?, updated_by = ?
                WHERE id = ?";
        
        return Database::execute($sql, [
            $data['category_id'],
            $data['name'],
            $data['size'] ?? null,
            $data['manufacturer'] ?? null,
            $data['model'] ?? null,
            $data['serial_number'] ?? null,
            $data['purchase_date'] ?? null,
            $data['purchase_cost'] ?? null,
            $data['daily_rate'],
            $data['weekly_rate'] ?? null,
            $data['status'],
            $data['condition'],
            $data['notes'] ?? null,
            $_SESSION['user_id'] ?? 1,
            $id
        ]);
    }
    
    public function deleteEquipment(int $id): bool
    {
        $sql = "DELETE FROM rental_equipment WHERE id = ?";
        return Database::execute($sql, [$id]);
    }
    
    public function getAllCategories(): array
    {
        $sql = "SELECT * FROM rental_categories ORDER BY name ASC";
        return Database::fetchAll($sql);
    }
    
    public function getReservationList(array $filters = []): array
    {
        $sql = "SELECT rr.*, 
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       c.email as customer_email,
                       COUNT(rri.id) as item_count
                FROM rental_reservations rr
                LEFT JOIN customers c ON rr.customer_id = c.id
                LEFT JOIN rental_reservation_items rri ON rr.id = rri.reservation_id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND rr.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " GROUP BY rr.id ORDER BY rr.created_at DESC";
        
        return Database::fetchAll($sql, $params);
    }
    
    public function getReservationById(int $id): ?array
    {
        $sql = "SELECT rr.*, 
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       c.email as customer_email,
                       c.phone as customer_phone
                FROM rental_reservations rr
                LEFT JOIN customers c ON rr.customer_id = c.id
                WHERE rr.id = ?";
        
        return Database::fetch($sql, [$id]);
    }
    
    public function getReservationItems(int $reservationId): array
    {
        $sql = "SELECT rri.*, re.name as equipment_name, re.equipment_code, rc.name as category_name
                FROM rental_reservation_items rri
                LEFT JOIN rental_equipment re ON rri.equipment_id = re.id
                LEFT JOIN rental_categories rc ON re.category_id = rc.id
                WHERE rri.reservation_id = ?";
        
        return Database::fetchAll($sql, [$reservationId]);
    }
    
    public function createReservation(array $data): int
    {
        $reservationNumber = 'RES-' . date('Ymd') . '-' . rand(1000, 9999);
        
        $sql = "INSERT INTO rental_reservations 
                (reservation_number, customer_id, start_date, end_date, status, total_amount, 
                 deposit_amount, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        Database::execute($sql, [
            $reservationNumber,
            $data['customer_id'],
            $data['start_date'],
            $data['end_date'],
            'pending',
            $data['total_amount'] ?? 0,
            $data['deposit_amount'] ?? 0,
            $data['notes'] ?? null,
            $_SESSION['user_id'] ?? 1
        ]);
        
        $reservationId = Database::lastInsertId();
        
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $this->addReservationItem($reservationId, $item);
            }
        }
        
        return $reservationId;
    }
    
    public function addReservationItem(int $reservationId, array $item): int
    {
        $sql = "INSERT INTO rental_reservation_items 
                (reservation_id, equipment_id, quantity, daily_rate, total_amount)
                VALUES (?, ?, ?, ?, ?)";
        
        Database::execute($sql, [
            $reservationId,
            $item['equipment_id'],
            $item['quantity'] ?? 1,
            $item['daily_rate'],
            $item['total_amount']
        ]);
        
        return Database::lastInsertId();
    }
    
    public function updateReservationStatus(int $id, string $status): bool
    {
        $sql = "UPDATE rental_reservations SET status = ?, updated_by = ? WHERE id = ?";
        return Database::execute($sql, [$status, $_SESSION['user_id'] ?? 1, $id]);
    }
    
    public function checkoutEquipment(int $reservationId): bool
    {
        $sql = "INSERT INTO rental_checkouts 
                (reservation_id, checkout_date, checked_out_by, created_by)
                VALUES (?, NOW(), ?, ?)";
        
        Database::execute($sql, [
            $reservationId,
            $_SESSION['user_id'] ?? 1,
            $_SESSION['user_id'] ?? 1
        ]);
        
        $this->updateReservationStatus($reservationId, 'active');
        
        $items = $this->getReservationItems($reservationId);
        foreach ($items as $item) {
            $this->updateEquipmentStatus($item['equipment_id'], 'rented');
        }
        
        return true;
    }
    
    public function checkinEquipment(int $checkoutId, array $condition): bool
    {
        $sql = "UPDATE rental_checkouts 
                SET checkin_date = NOW(), checked_in_by = ?, equipment_condition = ?
                WHERE id = ?";
        
        Database::execute($sql, [
            $_SESSION['user_id'] ?? 1,
            $condition['condition'] ?? 'good',
            $checkoutId
        ]);
        
        $checkout = Database::fetch("SELECT reservation_id FROM rental_checkouts WHERE id = ?", [$checkoutId]);
        if ($checkout) {
            $this->updateReservationStatus($checkout['reservation_id'], 'completed');
            
            $items = $this->getReservationItems($checkout['reservation_id']);
            foreach ($items as $item) {
                $this->updateEquipmentStatus($item['equipment_id'], 'available');
            }
        }
        
        return true;
    }
    
    public function updateEquipmentStatus(int $id, string $status): bool
    {
        $sql = "UPDATE rental_equipment SET status = ? WHERE id = ?";
        return Database::execute($sql, [$status, $id]);
    }
    
    public function getAvailableEquipment(string $startDate, string $endDate, ?int $categoryId = null): array
    {
        $sql = "SELECT re.*, rc.name as category_name 
                FROM rental_equipment re
                LEFT JOIN rental_categories rc ON re.category_id = rc.id
                WHERE re.status = 'available'";
        
        $params = [];
        
        if ($categoryId) {
            $sql .= " AND re.category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql .= " ORDER BY rc.name ASC, re.name ASC";
        
        return Database::fetchAll($sql, $params);
    }
}
