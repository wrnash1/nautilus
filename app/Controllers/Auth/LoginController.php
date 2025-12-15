<?php

namespace App\Controllers\Auth;

use App\Core\Auth;

class LoginController
{
    public function showLogin()
    {
        if (Auth::check()) {
            $redirect = $_GET['redirect'] ?? '/store';
            redirect($redirect);
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
            $redirect = $_POST['redirect'] ?? '/store';
            redirect($redirect);
        }

        $_SESSION['flash_error'] = 'Invalid email or password';
        redirect('/store/login' . (isset($_POST['redirect']) ? '?redirect=' . urlencode($_POST['redirect']) : ''));
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
