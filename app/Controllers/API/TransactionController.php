<?php

namespace App\Controllers\API;

use App\Services\POS\TransactionService;

class TransactionController
{
    private $transactionService;
    
    public function __construct()
    {
        $this->transactionService = new TransactionService();
    }
    
    public function index()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        $transactions = $this->transactionService->getTransactionsByDateRange($startDate, $endDate);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $transactions]);
    }
    
    public function show($id)
    {
        $transaction = $this->transactionService->getTransactionById($id);
        
        if (!$transaction) {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found', 'message' => 'Transaction not found']);
            return;
        }
        
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $transaction]);
    }
    
    public function store()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $transactionId = $this->transactionService->createTransaction($input);
        
        if ($transactionId) {
            $transaction = $this->transactionService->getTransactionById($transactionId);
            http_response_code(201);
            echo json_encode(['success' => true, 'data' => $transaction]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'Failed to create transaction']);
        }
    }
}
