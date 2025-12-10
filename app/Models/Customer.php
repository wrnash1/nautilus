<?php

namespace App\Models;

use App\Core\Database;

class Customer
{
    public static function all(int $limit = 50, int $offset = 0): array
    {
        return Database::fetchAll(
            "SELECT * FROM customers 
             WHERE is_active = 1
             ORDER BY first_name ASC, last_name ASC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        ) ?? [];
    }
    
    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM customers WHERE id = ? AND is_active = 1",
            [$id]
        );
    }
    
    public static function findByEmail(string $email): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM customers WHERE email = ? AND is_active = 1",
            [$email]
        );
    }
    
    public static function search(string $query, int $limit = 20): array
    {
        $searchTerm = "%{$query}%";
        return Database::fetchAll(
            "SELECT * FROM customers 
             WHERE is_active = 1
             AND (first_name LIKE ? OR last_name LIKE ? OR company_name LIKE ? 
                  OR email LIKE ? OR phone LIKE ?)
             ORDER BY first_name ASC, last_name ASC
             LIMIT ?",
            [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]
        ) ?? [];
    }
    
    public static function create(array $data): int
    {
        Database::query(
            "INSERT INTO customers (
                customer_type, first_name, last_name, email, phone, mobile,
                company_name, birth_date, emergency_contact_name, emergency_contact_phone,
                tax_exempt, tax_exempt_number, credit_limit, credit_terms, notes, 
                password, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['customer_type'] ?? 'B2C',
                $data['first_name'],
                $data['last_name'],
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['mobile'] ?? null,
                $data['company_name'] ?? null,
                $data['birth_date'] ?? null,
                $data['emergency_contact_name'] ?? null,
                $data['emergency_contact_phone'] ?? null,
                isset($data['tax_exempt']) ? (int)$data['tax_exempt'] : 0,
                $data['tax_exempt_number'] ?? null,
                $data['credit_limit'] ?? 0.00,
                $data['credit_terms'] ?? null,
                $data['notes'] ?? null,
                $data['password'] ?? null,
                $data['is_active'] ?? 1
            ]
        );
        
        $customerId = (int)Database::lastInsertId();
        
        if (!empty($data['address_line1'])) {
            Database::query(
                "INSERT INTO customer_addresses (
                    customer_id, address_type, address_line1, address_line2,
                    city, state, postal_code, country, is_default
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $customerId,
                    'billing',
                    $data['address_line1'],
                    $data['address_line2'] ?? null,
                    $data['city'] ?? null,
                    $data['state'] ?? null,
                    $data['postal_code'] ?? null,
                    $data['country'] ?? 'US',
                    1
                ]
            );
        }
        
        logActivity('create', 'customers', $customerId);
        
        return $customerId;
    }
    
    public static function update(int $id, array $data): bool
    {
        $sql = "UPDATE customers SET 
                customer_type = ?, first_name = ?, last_name = ?, email = ?,
                phone = ?, mobile = ?, company_name = ?, birth_date = ?,
                emergency_contact_name = ?, emergency_contact_phone = ?,
                tax_exempt = ?, tax_exempt_number = ?, credit_limit = ?,
                credit_terms = ?, notes = ?, is_active = ?";
        
        $params = [
            $data['customer_type'] ?? 'B2C',
            $data['first_name'],
            $data['last_name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['mobile'] ?? null,
            $data['company_name'] ?? null,
            $data['birth_date'] ?? null,
            $data['emergency_contact_name'] ?? null,
            $data['emergency_contact_phone'] ?? null,
            isset($data['tax_exempt']) ? (int)$data['tax_exempt'] : 0,
            $data['tax_exempt_number'] ?? null,
            $data['credit_limit'] ?? 0.00,
            $data['credit_terms'] ?? null,
            $data['notes'] ?? null,
            $data['is_active'] ?? 1
        ];
        
        if (isset($data['password']) && !empty($data['password'])) {
            $sql .= ", password = ?";
            $params[] = $data['password'];
        }
        
        $sql .= ", updated_at = NOW() WHERE id = ?";
        $params[] = $id;
        
        Database::query($sql, $params);
        
        if (!empty($data['address_line1'])) {
            $existingAddress = Database::fetchOne(
                "SELECT id FROM customer_addresses WHERE customer_id = ? AND is_default = 1",
                [$id]
            );
            
            if ($existingAddress) {
                Database::query(
                    "UPDATE customer_addresses SET 
                        address_line1 = ?, address_line2 = ?, city = ?,
                        state = ?, postal_code = ?, country = ?, updated_at = NOW()
                     WHERE id = ?",
                    [
                        $data['address_line1'],
                        $data['address_line2'] ?? null,
                        $data['city'] ?? null,
                        $data['state'] ?? null,
                        $data['postal_code'] ?? null,
                        $data['country'] ?? 'US',
                        $existingAddress['id']
                    ]
                );
            } else {
                Database::query(
                    "INSERT INTO customer_addresses (
                        customer_id, address_type, address_line1, address_line2,
                        city, state, postal_code, country, is_default
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $id,
                        'billing',
                        $data['address_line1'],
                        $data['address_line2'] ?? null,
                        $data['city'] ?? null,
                        $data['state'] ?? null,
                        $data['postal_code'] ?? null,
                        $data['country'] ?? 'US',
                        1
                    ]
                );
            }
        }
        
        logActivity('update', 'customers', $id);
        
        return true;
    }
    
    public static function count(): int
    {
        $result = Database::fetchOne("SELECT COUNT(*) as count FROM customers WHERE is_active = 1");
        return (int)($result['count'] ?? 0);
    }
    
    public static function delete(int $id): bool
    {
        Database::query(
            "UPDATE customers SET is_active = 0, updated_at = NOW() WHERE id = ?",
            [$id]
        );
        
        return true;
    }
    
    public static function getTransactionHistory(int $id): array
    {
        return Database::fetchAll(
            "SELECT t.*, p.payment_method, p.amount as payment_amount
             FROM transactions t
             LEFT JOIN payments p ON t.id = p.transaction_id
             WHERE t.customer_id = ?
             ORDER BY t.created_at DESC
             LIMIT 50",
            [$id]
        ) ?? [];
    }
    
    public static function getCertifications(int $id): array
    {
        return Database::fetchAll(
            "SELECT cc.*,
                    c.name as certification_name,
                    c.level as certification_level,
                    c.code as certification_code,
                    ca.id as agency_id,
                    ca.name as agency_name,
                    ca.abbreviation as agency_abbreviation
             FROM customer_certifications cc
             LEFT JOIN certifications c ON cc.certification_id = c.id
             LEFT JOIN certification_agencies ca ON c.agency_id = ca.id
             WHERE cc.customer_id = ?
             ORDER BY c.level DESC, cc.issue_date DESC",
            [$id]
        ) ?? [];
    }
    
    public static function getDefaultAddress(int $customerId): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM customer_addresses WHERE customer_id = ? AND is_default = 1",
            [$customerId]
        );
    }
    
    public static function getAllAddresses(int $customerId): array
    {
        return Database::fetchAll(
            "SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC, id ASC",
            [$customerId]
        ) ?? [];
    }
    
    public static function getAddress(int $addressId): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM customer_addresses WHERE id = ?",
            [$addressId]
        );
    }
    
    public static function createAddress(int $customerId, array $data): int
    {
        Database::query(
            "INSERT INTO customer_addresses (
                customer_id, address_type, address_line1, address_line2,
                city, state, postal_code, country, is_default
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $customerId,
                $data['address_type'] ?? 'billing',
                $data['address_line1'],
                $data['address_line2'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['postal_code'] ?? null,
                $data['country'] ?? 'US',
                isset($data['is_default']) ? (int)$data['is_default'] : 0
            ]
        );
        
        $addressId = (int)Database::lastInsertId();
        
        if (!empty($data['is_default'])) {
            Database::query(
                "UPDATE customer_addresses SET is_default = 0 WHERE customer_id = ? AND id != ?",
                [$customerId, $addressId]
            );
        }
        
        return $addressId;
    }
    
    public static function updateAddress(int $addressId, array $data): bool
    {
        $address = self::getAddress($addressId);
        if (!$address) {
            return false;
        }
        
        Database::query(
            "UPDATE customer_addresses SET 
                address_type = ?, address_line1 = ?, address_line2 = ?,
                city = ?, state = ?, postal_code = ?, country = ?,
                is_default = ?, updated_at = NOW()
             WHERE id = ?",
            [
                $data['address_type'] ?? 'billing',
                $data['address_line1'],
                $data['address_line2'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['postal_code'] ?? null,
                $data['country'] ?? 'US',
                isset($data['is_default']) ? (int)$data['is_default'] : 0,
                $addressId
            ]
        );
        
        if (!empty($data['is_default'])) {
            Database::query(
                "UPDATE customer_addresses SET is_default = 0 WHERE customer_id = ? AND id != ?",
                [$address['customer_id'], $addressId]
            );
        }
        
        return true;
    }
    
    public static function deleteAddress(int $addressId): bool
    {
        Database::query(
            "DELETE FROM customer_addresses WHERE id = ?",
            [$addressId]
        );
        
        return true;
    }
}
