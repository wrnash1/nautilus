<?php

namespace App\Controllers\Auth;

use App\Core\Auth;

class LoginController
{
    public function showLogin()
    {
        if (Auth::check()) {
            redirect('/store');
        }

        require __DIR__ . '/../../Views/auth/login.php';
    }

    public function login()
    {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Please enter email and password';
            redirect('/store/login');
        }

        if (Auth::attempt($email, $password)) {
            redirect('/store');
        }

        $_SESSION['flash_error'] = 'Invalid email or password';
        redirect('/store/login');
    }

    public function logout()
    {
        Auth::logout();
        redirect('/store/login');
    }
    
    public function verify2FA()
    {
        
    }
}
