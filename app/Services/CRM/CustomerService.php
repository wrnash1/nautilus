<?php

namespace App\Services\CRM;

use App\Models\Customer;
use App\Core\Database;

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

        // Get highest certification with agency info
        $highestCert = null;
        if (!empty($certifications)) {
            $highestCert = Database::fetchOne("
                SELECT
                    cc.*,
                    c.name as certification_name,
                    c.level as certification_level,
                    c.code as certification_code,
                    ca.name as agency_name,
                    ca.abbreviation as agency_abbreviation,
                    ca.logo_path,
                    ca.primary_color
                FROM customer_certifications cc
                JOIN certifications c ON cc.certification_id = c.id
                JOIN certification_agencies ca ON c.agency_id = ca.id
                WHERE cc.customer_id = ?
                AND cc.verification_status IN ('verified', 'pending')
                ORDER BY c.level DESC, cc.issue_date DESC
                LIMIT 1
            ", [$id]);
        }

        return [
            'customer' => $customer,
            'addresses' => $addresses,
            'transactions' => $transactions,
            'certifications' => $certifications,
            'highestCert' => $highestCert
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
