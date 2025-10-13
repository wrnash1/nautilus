<?php

namespace App\Controllers\Auth;

use App\Core\Auth;

class AuthController
{
    public function showLogin()
    {
        if (Auth::check()) {
            redirect('/');
        }
        
        require __DIR__ . '/../../Views/auth/login.php';
    }
    
    public function login()
    {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Please enter email and password';
            redirect('/login');
        }
        
        if (Auth::attempt($email, $password)) {
            redirect('/');
        }
        
        $_SESSION['flash_error'] = 'Invalid email or password';
        redirect('/login');
    }
    
    public function logout()
    {
        Auth::logout();
        redirect('/login');
    }
    
    public function verify2FA()
    {
        
    }
}
