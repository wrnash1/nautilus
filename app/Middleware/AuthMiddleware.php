<?php

namespace App\Middleware;

use App\Core\Auth;

class AuthMiddleware
{
    public function handle(): void
    {
        if (Auth::guest()) {
            redirect('/store/login');
        }

        // Restrict 'Customer' role from accessing /store routes
        // Customers should use /account or /portal
        $user = Auth::user();
        if ($user && isset($user['role_name']) && $user['role_name'] === 'Customer') {
            redirect('/account');
        }
    }
}
