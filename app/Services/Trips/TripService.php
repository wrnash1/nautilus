<?php

namespace App\Services\Trips;

use App\Core\Database;

class TripService
{
    public function getTripList(array $filters = []): array
    {
        $sql = "SELECT t.* FROM trips t WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (t.name LIKE ? OR t.trip_code LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY t.name ASC";
        
        return Database::fetchAll($sql, $params);
    }
    
    public function getTripById(int $id): ?array
    {
        $sql = "SELECT * FROM trips WHERE id = ?";
        return Database::fetch($sql, [$id]);
    }
    
    public function createTrip(array $data): int
    {
        $sql = "INSERT INTO trips 
                (trip_code, name, destination, description, duration_days, max_participants, price, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        Database::execute($sql, [
            $data['trip_code'],
            $data['name'],
            $data['destination'],
            $data['description'] ?? null,
            $data['duration_days'],
            $data['max_participants'] ?? 20,
            $data['price'],
            $_SESSION['user_id'] ?? 1
        ]);
        
        return Database::lastInsertId();
    }
    
    public function updateTrip(int $id, array $data): bool
    {
        $sql = "UPDATE trips 
                SET name = ?, destination = ?, description = ?, duration_days = ?, 
                    max_participants = ?, price = ?, updated_by = ?
                WHERE id = ?";
        
        return Database::execute($sql, [
            $data['name'],
            $data['destination'],
            $data['description'] ?? null,
            $data['duration_days'],
            $data['max_participants'],
            $data['price'],
            $_SESSION['user_id'] ?? 1,
            $id
        ]);
    }
    
    public function deleteTrip(int $id): bool
    {
        $sql = "DELETE FROM trips WHERE id = ?";
        return Database::execute($sql, [$id]);
    }
    
    public function getScheduleList(array $filters = []): array
    {
        $sql = "SELECT ts.*, t.name as trip_name, t.trip_code, t.destination,
                       (SELECT COUNT(*) FROM trip_bookings WHERE schedule_id = ts.id) as booking_count
                FROM trip_schedules ts
                LEFT JOIN trips t ON ts.trip_id = t.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['trip_id'])) {
            $sql .= " AND ts.trip_id = ?";
            $params[] = $filters['trip_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND ts.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY ts.departure_date DESC";
        
        return Database::fetchAll($sql, $params);
    }
    
    public function getScheduleById(int $id): ?array
    {
        $sql = "SELECT ts.*, t.name as trip_name, t.trip_code, t.max_participants
                FROM trip_schedules ts
                LEFT JOIN trips t ON ts.trip_id = t.id
                WHERE ts.id = ?";
        
        return Database::fetch($sql, [$id]);
    }
    
    public function createSchedule(array $data): int
    {
        $sql = "INSERT INTO trip_schedules 
                (trip_id, departure_date, return_date, status, max_participants, price_override, created_by)
                VALUES (?, ?, ?, 'scheduled', ?, ?, ?)";
        
        Database::execute($sql, [
            $data['trip_id'],
            $data['departure_date'],
            $data['return_date'],
            $data['max_participants'] ?? 20,
            $data['price_override'] ?? null,
            $_SESSION['user_id'] ?? 1
        ]);
        
        return Database::lastInsertId();
    }
    
    public function getBookingList(array $filters = []): array
    {
        $sql = "SELECT tb.*, 
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       c.email as customer_email,
                       t.name as trip_name,
                       ts.departure_date
                FROM trip_bookings tb
                LEFT JOIN customers c ON tb.customer_id = c.id
                LEFT JOIN trip_schedules ts ON tb.schedule_id = ts.id
                LEFT JOIN trips t ON ts.trip_id = t.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND tb.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY tb.created_at DESC";
        
        return Database::fetchAll($sql, $params);
    }
    
    public function getBookingById(int $id): ?array
    {
        $sql = "SELECT tb.*, 
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       c.email as customer_email,
                       t.name as trip_name,
                       ts.departure_date,
                       ts.return_date
                FROM trip_bookings tb
                LEFT JOIN customers c ON tb.customer_id = c.id
                LEFT JOIN trip_schedules ts ON tb.schedule_id = ts.id
                LEFT JOIN trips t ON ts.trip_id = t.id
                WHERE tb.id = ?";
        
        return Database::fetch($sql, [$id]);
    }
    
    public function createBooking(array $data): int
    {
        $bookingNumber = 'TRIP-' . date('Ymd') . '-' . rand(1000, 9999);
        
        $sql = "INSERT INTO trip_bookings 
                (booking_number, schedule_id, customer_id, number_of_participants, total_amount, 
                 deposit_amount, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)";
        
        Database::execute($sql, [
            $bookingNumber,
            $data['schedule_id'],
            $data['customer_id'],
            $data['number_of_participants'] ?? 1,
            $data['total_amount'] ?? 0,
            $data['deposit_amount'] ?? 0,
            $_SESSION['user_id'] ?? 1
        ]);
        
        return Database::lastInsertId();
    }
    
    public function updateBookingStatus(int $id, string $status): bool
    {
        $sql = "UPDATE trip_bookings SET status = ?, updated_by = ? WHERE id = ?";
        return Database::execute($sql, [$status, $_SESSION['user_id'] ?? 1, $id]);
    }
}
