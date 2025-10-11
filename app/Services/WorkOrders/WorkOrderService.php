<?php

namespace App\Services\WorkOrders;

use App\Core\Database;

class WorkOrderService
{
    public function getWorkOrderList(array $filters = []): array
    {
        $sql = "SELECT wo.*, 
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name
                FROM work_orders wo
                LEFT JOIN customers c ON wo.customer_id = c.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND wo.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $sql .= " AND wo.priority = ?";
            $params[] = $filters['priority'];
        }
        
        $sql .= " ORDER BY wo.created_at DESC";
        
        return Database::fetchAll($sql, $params);
    }
    
    public function getWorkOrderById(int $id): ?array
    {
        $sql = "SELECT wo.*, 
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       c.email as customer_email,
                       c.phone as customer_phone
                FROM work_orders wo
                LEFT JOIN customers c ON wo.customer_id = c.id
                WHERE wo.id = ?";
        
        return Database::fetch($sql, [$id]);
    }
    
    public function createWorkOrder(array $data): int
    {
        $workOrderNumber = 'WO-' . date('Ymd') . '-' . rand(1000, 9999);
        
        $sql = "INSERT INTO work_orders 
                (work_order_number, customer_id, equipment_type, equipment_brand, equipment_model, 
                 serial_number, issue_description, priority, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
        
        Database::execute($sql, [
            $workOrderNumber,
            $data['customer_id'] ?? null,
            $data['equipment_type'],
            $data['equipment_brand'] ?? null,
            $data['equipment_model'] ?? null,
            $data['serial_number'] ?? null,
            $data['issue_description'],
            $data['priority'] ?? 'normal',
            $_SESSION['user_id'] ?? 1
        ]);
        
        return Database::lastInsertId();
    }
    
    public function updateWorkOrder(int $id, array $data): bool
    {
        $sql = "UPDATE work_orders 
                SET equipment_type = ?, equipment_brand = ?, equipment_model = ?, 
                    serial_number = ?, issue_description = ?, priority = ?, updated_by = ?
                WHERE id = ?";
        
        return Database::execute($sql, [
            $data['equipment_type'],
            $data['equipment_brand'] ?? null,
            $data['equipment_model'] ?? null,
            $data['serial_number'] ?? null,
            $data['issue_description'],
            $data['priority'],
            $_SESSION['user_id'] ?? 1,
            $id
        ]);
    }
    
    public function updateStatus(int $id, string $status): bool
    {
        $sql = "UPDATE work_orders SET status = ?, updated_by = ? WHERE id = ?";
        
        if ($status === 'completed') {
            $sql = "UPDATE work_orders SET status = ?, completed_date = NOW(), updated_by = ? WHERE id = ?";
        }
        
        return Database::execute($sql, [$status, $_SESSION['user_id'] ?? 1, $id]);
    }
    
    public function deleteWorkOrder(int $id): bool
    {
        $sql = "DELETE FROM work_orders WHERE id = ?";
        return Database::execute($sql, [$id]);
    }
    
    public function getWorkOrderNotes(int $workOrderId): array
    {
        $sql = "SELECT won.*, CONCAT(u.first_name, ' ', u.last_name) as author_name
                FROM work_order_notes won
                LEFT JOIN users u ON won.created_by = u.id
                WHERE won.work_order_id = ?
                ORDER BY won.created_at DESC";
        
        return Database::fetchAll($sql, [$workOrderId]);
    }
    
    public function addNote(int $workOrderId, array $data): int
    {
        $sql = "INSERT INTO work_order_notes 
                (work_order_id, note, is_visible_to_customer, created_by)
                VALUES (?, ?, ?, ?)";
        
        Database::execute($sql, [
            $workOrderId,
            $data['note'],
            $data['is_visible_to_customer'] ?? 0,
            $_SESSION['user_id'] ?? 1
        ]);
        
        return Database::lastInsertId();
    }
}
