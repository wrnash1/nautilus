<?php

namespace App\Controllers\Auth;

use App\Core\Auth;

class AuthController
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

        $logPath = BASE_PATH . '/storage/logs/debug_login.log';
        file_put_contents($logPath, date('Y-m-d H:i:s') . " - Login attempt for: $email\n", FILE_APPEND);

        if (empty($email) || empty($password)) {
            file_put_contents($logPath, date('Y-m-d H:i:s') . " - Empty email or password\n", FILE_APPEND);
            $_SESSION['flash_error'] = 'Please enter email and password';
            redirect('/store/login');
        }

        if (Auth::attempt($email, $password)) {
            file_put_contents($logPath, date('Y-m-d H:i:s') . " - Auth::attempt success\n", FILE_APPEND);
            redirect('/store');
        }

        file_put_contents($logPath, date('Y-m-d H:i:s') . " - Auth::attempt failed\n", FILE_APPEND);
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
