<?php

namespace App\Controllers\POS;

use App\Core\Database;
use App\Services\POS\TransactionService;

class TransactionController
{
    private TransactionService $transactionService;
    
    public function __construct()
    {
        $this->transactionService = new TransactionService();
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
    
    public function void(int $id)
    {
        
    }
    
    public function refund(int $id)
    {
        
    }
}
