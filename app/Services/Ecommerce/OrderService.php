<?php

namespace App\Services\Ecommerce;

class OrderService
{
    public function createOrder(array $data): int
    {
        
        return 0;
    }
    
    public function processShipment(int $orderId): bool
    {
        
        return false;
    }
    
    public function updateOrderStatus(int $orderId, string $status): bool
    {
        
        return false;
    }
}
