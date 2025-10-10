<?php

namespace App\Controllers\Auth;

use App\Core\Auth;

class AuthController
{
    public function showLogin()
    {
        
    }
    
    public function login()
    {
        
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
