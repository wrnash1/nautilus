<?php

namespace App\Controllers\Customer;

use App\Core\CustomerAuth;
use App\Core\Database;
use App\Models\Customer;

class AccountController
{
    public function dashboard()
    {
        $customer = CustomerAuth::customer();
        
        $orders = Database::fetchAll(
            "SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5",
            [$customer['id']]
        ) ?? [];
        
        require __DIR__ . '/../../Views/customer/dashboard.php';
    }
    
    public function orders()
    {
        $customer = CustomerAuth::customer();
        
        $orders = Database::fetchAll(
            "SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC",
            [$customer['id']]
        ) ?? [];
        
        require __DIR__ . '/../../Views/customer/orders.php';
    }
    
    public function orderDetail(int $id)
    {
        $customer = CustomerAuth::customer();
        
        $order = Database::fetchOne(
            "SELECT * FROM orders WHERE id = ? AND customer_id = ?",
            [$id, $customer['id']]
        );
        
        if (!$order) {
            $_SESSION['flash_error'] = 'Order not found';
            header('Location: /account/orders');
            exit;
        }
        
        $orderItems = Database::fetchAll(
            "SELECT * FROM order_items WHERE order_id = ?",
            [$id]
        ) ?? [];
        
        require __DIR__ . '/../../Views/customer/order-detail.php';
    }
    
    public function profile()
    {
        $customer = CustomerAuth::customer();
        require __DIR__ . '/../../Views/customer/profile.php';
    }
    
    public function updateProfile()
    {
        $customer = CustomerAuth::customer();
        
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        
        if (empty($firstName) || empty($lastName) || empty($email)) {
            $_SESSION['flash_error'] = 'Please fill in all required fields';
            header('Location: /account/profile');
            exit;
        }
        
        $existingCustomer = Customer::findByEmail($email);
        if ($existingCustomer && $existingCustomer['id'] != $customer['id']) {
            $_SESSION['flash_error'] = 'Email already in use';
            header('Location: /account/profile');
            exit;
        }
        
        Customer::update($customer['id'], [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone
        ]);
        
        $_SESSION['flash_success'] = 'Profile updated successfully!';
        header('Location: /account/profile');
        exit;
    }
    
    public function addresses()
    {
        $customer = CustomerAuth::customer();
        
        $addresses = Database::fetchAll(
            "SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC, id ASC",
            [$customer['id']]
        ) ?? [];
        
        require __DIR__ . '/../../Views/customer/addresses.php';
    }
    
    public function createAddress()
    {
        $customer = CustomerAuth::customer();
        
        Database::query(
            "INSERT INTO customer_addresses (customer_id, address_type, address_line1, address_line2, city, state, postal_code, country, is_default) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $customer['id'],
                $_POST['address_type'] ?? 'both',
                sanitizeInput($_POST['address_line1'] ?? ''),
                sanitizeInput($_POST['address_line2'] ?? ''),
                sanitizeInput($_POST['city'] ?? ''),
                sanitizeInput($_POST['state'] ?? ''),
                sanitizeInput($_POST['postal_code'] ?? ''),
                sanitizeInput($_POST['country'] ?? 'US'),
                isset($_POST['is_default']) ? 1 : 0
            ]
        );
        
        $_SESSION['flash_success'] = 'Address added successfully!';
        header('Location: /account/addresses');
        exit;
    }
    
    public function updateAddress(int $id)
    {
        $customer = CustomerAuth::customer();
        
        $address = Database::fetchOne(
            "SELECT * FROM customer_addresses WHERE id = ? AND customer_id = ?",
            [$id, $customer['id']]
        );
        
        if (!$address) {
            $_SESSION['flash_error'] = 'Address not found';
            header('Location: /account/addresses');
            exit;
        }
        
        Database::query(
            "UPDATE customer_addresses SET address_type = ?, address_line1 = ?, address_line2 = ?, 
             city = ?, state = ?, postal_code = ?, country = ?, is_default = ? 
             WHERE id = ?",
            [
                $_POST['address_type'] ?? 'both',
                sanitizeInput($_POST['address_line1'] ?? ''),
                sanitizeInput($_POST['address_line2'] ?? ''),
                sanitizeInput($_POST['city'] ?? ''),
                sanitizeInput($_POST['state'] ?? ''),
                sanitizeInput($_POST['postal_code'] ?? ''),
                sanitizeInput($_POST['country'] ?? 'US'),
                isset($_POST['is_default']) ? 1 : 0,
                $id
            ]
        );
        
        $_SESSION['flash_success'] = 'Address updated successfully!';
        header('Location: /account/addresses');
        exit;
    }
    
    public function deleteAddress(int $id)
    {
        $customer = CustomerAuth::customer();
        
        Database::query(
            "DELETE FROM customer_addresses WHERE id = ? AND customer_id = ?",
            [$id, $customer['id']]
        );
        
        $_SESSION['flash_success'] = 'Address deleted successfully!';
        header('Location: /account/addresses');
        exit;
    }
}
