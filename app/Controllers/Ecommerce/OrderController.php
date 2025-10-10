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
        
    }
    
    public function show(int $id)
    {
        
    }
    
    public function create()
    {
        
    }
    
    public function updateStatus(int $id)
    {
        
    }
    
    public function ship(int $id)
    {
        
    }
}
