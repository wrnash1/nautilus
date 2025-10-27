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
    }
}
