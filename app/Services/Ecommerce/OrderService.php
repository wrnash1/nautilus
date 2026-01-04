<?php

namespace App\Services\Ecommerce;

use App\Core\Database;

class OrderService
{
    public function getOrderList(array $filters = []): array
    {
        $sql = "SELECT o.*, 
                CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                c.email as customer_email
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND o.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['payment_status'])) {
            $sql .= " AND o.payment_status = ?";
            $params[] = $filters['payment_status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (o.order_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY o.created_at DESC LIMIT 100";
        
        return Database::fetchAll($sql, $params) ?? [];
    }
    
    public function getOrderStats(): array
    {
        $today = date('Y-m-d');
        $thisMonth = date('Y-m');
        
        $todayOrders = Database::fetchOne(
            "SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total 
             FROM orders WHERE DATE(created_at) = ?",
            [$today]
        );
        
        $monthOrders = Database::fetchOne(
            "SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total 
             FROM orders WHERE DATE_FORMAT(created_at, '%Y-%m') = ?",
            [$thisMonth]
        );
        
        $pendingCount = Database::fetchOne(
            "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'",
            []
        );
        
        $processingCount = Database::fetchOne(
            "SELECT COUNT(*) as count FROM orders WHERE status = 'processing'",
            []
        );
        
        $shippedCount = Database::fetchOne(
            "SELECT COUNT(*) as count FROM orders WHERE status = 'shipped'",
            []
        );
        
        return [
            'today_count' => (int)($todayOrders['count'] ?? 0),
            'today_total' => (float)($todayOrders['total'] ?? 0),
            'month_count' => (int)($monthOrders['count'] ?? 0),
            'month_total' => (float)($monthOrders['total'] ?? 0),
            'pending_count' => (int)($pendingCount['count'] ?? 0),
            'processing_count' => (int)($processingCount['count'] ?? 0),
            'shipped_count' => (int)($shippedCount['count'] ?? 0)
        ];
    }
    
    public function getOrderById(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT o.*, 
            CONCAT(c.first_name, ' ', c.last_name) as customer_name,
            c.email as customer_email,
            c.phone as customer_phone
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            WHERE o.id = ?",
            [$id]
        );
    }
    
    public function getOrderItems(int $orderId): array
    {
        return Database::fetchAll(
            "SELECT * FROM order_items WHERE order_id = ? ORDER BY id",
            [$orderId]
        ) ?? [];
    }
    
    public function createOrder(array $data): int
    {
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
        
        Database::query(
            "INSERT INTO orders (
                order_number, customer_id, order_type, subtotal, shipping, tax, 
                discount, total, status, payment_status, shipping_address_line1,
                shipping_city, shipping_state, shipping_postal_code, shipping_country,
                billing_address_line1, billing_city, billing_state, 
                billing_postal_code, billing_country, ip_address, user_agent
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $orderNumber,
                $data['customer_id'],
                $data['order_type'] ?? 'online',
                $data['subtotal'],
                $data['shipping'] ?? 0,
                $data['tax'] ?? 0,
                $data['discount'] ?? 0,
                $data['total'],
                'pending',
                'pending',
                $data['shipping_address_line1'] ?? null,
                $data['shipping_city'] ?? null,
                $data['shipping_state'] ?? null,
                $data['shipping_postal_code'] ?? null,
                $data['shipping_country'] ?? 'US',
                $data['billing_address_line1'] ?? null,
                $data['billing_city'] ?? null,
                $data['billing_state'] ?? null,
                $data['billing_postal_code'] ?? null,
                $data['billing_country'] ?? 'US',
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]
        );
        
        $orderId = (int)Database::lastInsertId();
        
        foreach ($data['items'] as $item) {
            Database::query(
                "INSERT INTO order_items (
                    order_id, product_id, product_name, sku, quantity,
                    unit_price, discount, tax, total
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $orderId,
                    $item['product_id'],
                    $item['product_name'],
                    $item['sku'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['discount'] ?? 0,
                    $item['tax'] ?? 0,
                    $item['total']
                ]
            );
            
            Database::query(
                "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?",
                [$item['quantity'], $item['product_id']]
            );
        }
        
        return $orderId;
    }
    
    public function updateOrderStatus(int $orderId, string $status): bool
    {
        Database::query(
            "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $orderId]
        );
        return true;
    }
    
    public function updatePaymentStatus(int $orderId, string $paymentStatus): bool
    {
        Database::query(
            "UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?",
            [$paymentStatus, $orderId]
        );
        return true;
    }
    
    public function processShipment(int $orderId, array $shipmentData): bool
    {
        Database::query(
            "INSERT INTO shipments (
                order_id, carrier, service, tracking_number, weight, 
                weight_unit, cost, shipped_at, estimated_delivery
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)",
            [
                $orderId,
                $shipmentData['carrier'],
                $shipmentData['service'],
                $shipmentData['tracking_number'],
                $shipmentData['weight'] ?? null,
                $shipmentData['weight_unit'] ?? 'lb',
                $shipmentData['cost'] ?? 0,
                $shipmentData['estimated_delivery'] ?? null
            ]
        );
        
        Database::query(
            "UPDATE orders SET 
                status = 'shipped',
                shipped_at = NOW(),
                tracking_number = ?,
                updated_at = NOW()
            WHERE id = ?",
            [$shipmentData['tracking_number'], $orderId]
        );
        
        return true;
    }
}
