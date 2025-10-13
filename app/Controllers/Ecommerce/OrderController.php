<?php

namespace App\Controllers\Ecommerce;

use App\Services\Ecommerce\OrderService;

class OrderController
{
    private OrderService $orderService;
    
    public function __construct()
    {
        $this->orderService = new OrderService();
    }
    
    public function index()
    {
        if (!hasPermission('orders.view')) {
            header('Location: /');
            exit;
        }
        
        $filters = [
            'status' => $_GET['status'] ?? null,
            'payment_status' => $_GET['payment_status'] ?? null,
            'search' => $_GET['search'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];
        
        $orders = $this->orderService->getOrderList($filters);
        $stats = $this->orderService->getOrderStats();
        
        $activeMenu = 'orders';
        $pageTitle = 'Orders';
        require_once __DIR__ . '/../../Views/orders/index.php';
    }
    
    public function show(int $id)
    {
        $order = $this->orderService->getOrderById($id);
        $orderItems = $this->orderService->getOrderItems($id);
        
        if (!$order) {
            header('Location: /orders');
            exit;
        }
        
        $activeMenu = 'orders';
        $pageTitle = 'Order ' . $order['order_number'];
        require_once __DIR__ . '/../../Views/orders/show.php';
    }
    
    public function updateStatus(int $id)
    {
        if (!hasPermission('orders.edit')) {
            header('Location: /orders/' . $id);
            exit;
        }
        
        $status = $_POST['status'];
        $this->orderService->updateOrderStatus($id, $status);
        
        $_SESSION['flash_success'] = 'Order status updated!';
        header('Location: /orders/' . $id);
        exit;
    }
    
    public function ship(int $id)
    {
        if (!hasPermission('orders.ship')) {
            header('Location: /orders/' . $id);
            exit;
        }
        
        $shipmentData = [
            'carrier' => $_POST['carrier'],
            'service' => $_POST['service'],
            'tracking_number' => $_POST['tracking_number'],
            'weight' => $_POST['weight'] ?? null,
            'estimated_delivery' => $_POST['estimated_delivery'] ?? null
        ];
        
        $this->orderService->processShipment($id, $shipmentData);
        
        $_SESSION['flash_success'] = 'Order shipped successfully!';
        header('Location: /orders/' . $id);
        exit;
    }
}
