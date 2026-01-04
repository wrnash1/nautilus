<?php

namespace App\Controllers\API;

use App\Services\CRM\CustomerService;

class CustomerController
{
    private $customerService;
    
    public function __construct()
    {
        $this->customerService = new CustomerService();
    }
    
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        
        $customers = $this->customerService->getAllCustomers($page, $limit);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $customers]);
    }
    
    public function show($id)
    {
        $customer = $this->customerService->getCustomerById($id);
        
        if (!$customer) {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found', 'message' => 'Customer not found']);
            return;
        }
        
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $customer]);
    }
    
    public function store()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $customerId = $this->customerService->createCustomer($input);
        
        if ($customerId) {
            $customer = $this->customerService->getCustomerById($customerId);
            http_response_code(201);
            echo json_encode(['success' => true, 'data' => $customer]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'Failed to create customer']);
        }
    }
    
    public function update($id)
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $success = $this->customerService->updateCustomer($id, $input);
        
        if ($success) {
            $customer = $this->customerService->getCustomerById($id);
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $customer]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'Failed to update customer']);
        }
    }
    
    public function destroy($id)
    {
        $success = $this->customerService->deleteCustomer($id);
        
        if ($success) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Customer deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'Failed to delete customer']);
        }
    }
}
