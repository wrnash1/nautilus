<?php

namespace App\Controllers\CRM;

use App\Services\CRM\CustomerService;

class CustomerController
{
    private CustomerService $customerService;
    
    public function __construct()
    {
        $this->customerService = new CustomerService();
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
    
    public function search()
    {
        
    }
}
