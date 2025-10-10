<?php

namespace App\Controllers\Inventory;

use App\Services\Inventory\ProductService;

class ProductController
{
    private ProductService $productService;
    
    public function __construct()
    {
        $this->productService = new ProductService();
    }
    
    public function index()
    {
        
    }
    
    public function create()
    {
        
    }
    
    public function store()
    {
        
    }
    
    public function show(int $id)
    {
        
    }
    
    public function edit(int $id)
    {
        
    }
    
    public function update(int $id)
    {
        
    }
    
    public function delete(int $id)
    {
        
    }
    
    public function adjustStock(int $id)
    {
        
    }
}
