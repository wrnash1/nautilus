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
            $user = Auth::user();
            $redirect = $_POST['redirect'] ?? '';
            
            // If no specific redirect or redirect loops back to login
            if (empty($redirect) || $redirect === '/store/login') {
                $roleName = $user['role_name'] ?? '';
                
                switch ($roleName) {
                    case 'Instructor':
                        $redirect = '/store/courses/schedules'; // Valid instructor route
                        break;
                    case 'Sales':
                    case 'Sales Associate':
                        $redirect = '/store/pos'; // Valid sales route
                        break;
                    case 'Customer':
                        $redirect = '/account'; // Valid customer route
                        break;
                    default:
                        $redirect = '/store'; // Default admin dashboard
                }
            }
            
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
