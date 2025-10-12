<?php

namespace App\Controllers\Customer;

use App\Core\CustomerAuth;
use App\Models\Customer;

class CustomerAuthController
{
    public function showRegister()
    {
        if (CustomerAuth::check()) {
            header('Location: /account');
            exit;
        }
        
        require __DIR__ . '/../../Views/customer/auth/register.php';
    }
    
    public function register()
    {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        
        if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            $_SESSION['flash_error'] = 'Please fill in all required fields';
            header('Location: /account/register');
            exit;
        }
        
        if ($password !== $passwordConfirm) {
            $_SESSION['flash_error'] = 'Passwords do not match';
            header('Location: /account/register');
            exit;
        }
        
        if (strlen($password) < 8) {
            $_SESSION['flash_error'] = 'Password must be at least 8 characters';
            header('Location: /account/register');
            exit;
        }
        
        if (Customer::findByEmail($email)) {
            $_SESSION['flash_error'] = 'Email already registered';
            header('Location: /account/register');
            exit;
        }
        
        $customerId = Customer::create([
            'customer_type' => 'B2C',
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'customer_since' => date('Y-m-d'),
            'is_active' => 1
        ]);
        
        $customer = Customer::find($customerId);
        CustomerAuth::login($customer);
        
        $_SESSION['flash_success'] = 'Account created successfully!';
        header('Location: /account');
        exit;
    }
    
    public function showLogin()
    {
        if (CustomerAuth::check()) {
            header('Location: /account');
            exit;
        }
        
        require __DIR__ . '/../../Views/customer/auth/login.php';
    }
    
    public function login()
    {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Please enter email and password';
            header('Location: /account/login');
            exit;
        }
        
        if (CustomerAuth::attempt($email, $password)) {
            $intendedUrl = $_SESSION['intended_url'] ?? '/account';
            unset($_SESSION['intended_url']);
            header('Location: ' . $intendedUrl);
            exit;
        }
        
        $_SESSION['flash_error'] = 'Invalid email or password';
        header('Location: /account/login');
        exit;
    }
    
    public function logout()
    {
        CustomerAuth::logout();
        $_SESSION['flash_success'] = 'Logged out successfully';
        header('Location: /shop');
        exit;
    }
}
