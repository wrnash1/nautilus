<?php

namespace App\Controllers\API;

use App\Services\Ecommerce\OrderService;

class OrderController
{
    private $orderService;
    
    public function __construct()
    {
        $this->orderService = new OrderService();
    }
    
    public function index()
    {
        $customerId = $_GET['customer_id'] ?? null;
        $status = $_GET['status'] ?? null;
        
        $orders = $this->orderService->getOrders($customerId, $status);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $orders]);
    }
    
    public function show($id)
    {
        $order = $this->orderService->getOrderById($id);
        
        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found', 'message' => 'Order not found']);
            return;
        }
        
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $order]);
    }
    
    public function store()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $orderId = $this->orderService->createOrder($input);
        
        if ($orderId) {
            $order = $this->orderService->getOrderById($orderId);
            http_response_code(201);
            echo json_encode(['success' => true, 'data' => $order]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'Failed to create order']);
        }
    }
    
    public function update($id)
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $success = $this->orderService->updateOrderStatus($id, $input['status'] ?? '');
        
        if ($success) {
            $order = $this->orderService->getOrderById($id);
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $order]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'Failed to update order']);
        }
    }
}
