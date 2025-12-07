<?php

namespace App\Core;

use App\Models\Customer;

class CustomerAuth
{
    private static ?array $customer = null;
    
    public static function attempt(string $email, string $password): bool
    {
        $customer = Customer::findByEmail($email);
        
        if ($customer && isset($customer['password']) && password_verify($password, $customer['password'])) {
            self::login($customer);
            return true;
        }
        
        return false;
    }
    
    public static function login(array $customer): void
    {
        $_SESSION['customer_id'] = $customer['id'];
        $_SESSION['customer_email'] = $customer['email'];
        self::$customer = $customer;
    }
    
    public static function logout(): void
    {
        unset($_SESSION['customer_id']);
        unset($_SESSION['customer_email']);
        self::$customer = null;
    }
    
    public static function customer(): ?array
    {
        if (self::$customer === null && isset($_SESSION['customer_id'])) {
            self::$customer = Customer::find($_SESSION['customer_id']);
        }
        
        return self::$customer;
    }
    
    public static function id(): ?int
    {
        $customer = self::customer();
        return $customer['id'] ?? null;
    }
    
    public static function check(): bool
    {
        return self::customer() !== null;
    }
    
    public static function guest(): bool
    {
        return !self::check();
    }
}
