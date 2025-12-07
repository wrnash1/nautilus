<?php

namespace App\Middleware;

use App\Core\CustomerAuth;

class CustomerAuthMiddleware
{
    public function handle(): void
    {
        if (!CustomerAuth::check()) {
            $_SESSION['flash_error'] = 'Please login to access your account';
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            redirect('/account/login');
        }
    }
}
