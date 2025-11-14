<?php

namespace App\Core;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    private static ?array $user = null;
    
    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['two_factor_enabled']) {
                $_SESSION['2fa_pending'] = $user['id'];
                return false;
            }
            
            self::login($user);
            return true;
        }
        
        return false;
    }
    
    public static function login(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role_id'];
        $_SESSION['tenant_id'] = $user['tenant_id'] ?? null;
        self::$user = $user;

        User::updateLastLogin($user['id']);
    }
    
    public static function logout(): void
    {
        session_destroy();
        self::$user = null;
    }
    
    public static function user(): ?array
    {
        if (self::$user === null && isset($_SESSION['user_id'])) {
            self::$user = User::find($_SESSION['user_id']);
        }
        
        return self::$user;
    }
    
    public static function id(): ?int
    {
        $user = self::user();
        return $user['id'] ?? null;
    }
    
    public static function check(): bool
    {
        return self::user() !== null;
    }
    
    public static function guest(): bool
    {
        return !self::check();
    }
    
    public static function hasPermission(string $permission): bool
    {
        $user = self::user();
        if (!$user) return false;
        
        return User::hasPermission($user['id'], $permission);
    }
    
    public static function generateToken(array $user): string
    {
        $payload = [
            'iss' => $_ENV['APP_URL'],
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24),
            'user_id' => $user['id'],
            'email' => $user['email']
        ];
        
        return JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    }
    
    public static function verifyToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }
}
