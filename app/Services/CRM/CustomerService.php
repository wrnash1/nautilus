<?php

namespace App\Services\CRM;

use App\Models\Customer;

class CustomerService
{
    public function createCustomer(array $data): int
    {
        $this->validateCustomerData($data);
        
        return Customer::create($data);
    }
    
    public function updateCustomer(int $id, array $data): bool
    {
        $this->validateCustomerData($data, $id);
        
        return Customer::update($id, $data);
    }
    
    public function search(string $query): array
    {
        return Customer::search($query);
    }
    
    public function getCustomer360(int $id): array
    {
        $customer = Customer::find($id);
        
        if (!$customer) {
            return [];
        }
        
        $addresses = Customer::getAllAddresses($id);
        $transactions = Customer::getTransactionHistory($id);
        $certifications = Customer::getCertifications($id);
        
        return [
            'customer' => $customer,
            'addresses' => $addresses,
            'transactions' => $transactions,
            'certifications' => $certifications
        ];
    }
    
    private function validateCustomerData(array $data, ?int $id = null): void
    {
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required';
        }
        
        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required';
        }
        
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }
            
            $existingCustomer = \App\Core\Database::fetchOne(
                "SELECT id FROM customers WHERE email = ? AND id != ? AND is_active = 1",
                [$data['email'], $id ?? 0]
            );
            
            if ($existingCustomer) {
                $errors[] = 'Email already exists';
            }
        }
        
        if (($data['customer_type'] ?? 'B2C') === 'B2B' && empty($data['company_name'])) {
            $errors[] = 'Company name is required for B2B customers';
        }
        
        if (!empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }
    }
}
