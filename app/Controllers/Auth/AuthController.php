<?php

namespace App\Controllers\Auth;

use App\Core\Auth;

class AuthController
{
    public function showLogin()
    {
        if (Auth::check()) {
            header('Location: /');
            exit;
        }
        
        require __DIR__ . '/../../Views/auth/login.php';
    }
    
    public function login()
    {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Please enter email and password';
            header('Location: /login');
            exit;
        }
        
        if (Auth::attempt($email, $password)) {
            header('Location: /');
            exit;
        }
        
        $_SESSION['flash_error'] = 'Invalid email or password';
        header('Location: /login');
        exit;
    }
    
    public function logout()
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }
    
    public function verify2FA()
    {
        
    }
}
