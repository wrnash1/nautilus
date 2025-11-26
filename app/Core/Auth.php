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
        $logPath = BASE_PATH . '/storage/logs/debug_auth.log';
        file_put_contents($logPath, date('Y-m-d H:i:s') . " - Attempting login for $email\n", FILE_APPEND);
        
        $user = User::findByEmail($email);

        if (!$user) {
            file_put_contents($logPath, date('Y-m-d H:i:s') . " - User not found for email: $email\n", FILE_APPEND);
            return false;
        }

        file_put_contents($logPath, date('Y-m-d H:i:s') . " - User found: " . json_encode(['id' => $user['id'], 'email' => $user['email'], 'two_factor_enabled' => $user['two_factor_enabled'] ?? 'N/A']) . "\n", FILE_APPEND);

        if (password_verify($password, $user['password_hash'])) {
            file_put_contents($logPath, date('Y-m-d H:i:s') . " - Password verified for user ID: " . $user['id'] . "\n", FILE_APPEND);
            
            if ($user['two_factor_enabled']) {
                file_put_contents($logPath, date('Y-m-d H:i:s') . " - Two-factor authentication enabled for user ID: " . $user['id'] . ". Pending 2FA.\n", FILE_APPEND);
                $_SESSION['2fa_pending'] = $user['id'];
                return false;
            }
            
            self::login($user);
            file_put_contents($logPath, date('Y-m-d H:i:s') . " - User ID: " . $user['id'] . " logged in successfully.\n", FILE_APPEND);
            return true;
        }
        
        file_put_contents($logPath, date('Y-m-d H:i:s') . " - Password verification failed\n", FILE_APPEND);
        return false;
    }
    
    public static function login(array $user): void
    {
        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role_id'];
        $_SESSION['tenant_id'] = $user['tenant_id'] ?? null;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $_SESSION['login_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

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
