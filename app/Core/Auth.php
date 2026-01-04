<?php

namespace App\Core;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    private static ?array $user = null;

    public static function attempt(string $identifier, string $password): bool
    {
        // Use Logger or a safe temp path for this specific debug log
        try {
            $logDir = sys_get_temp_dir();
            $logPath = $logDir . '/debug_auth.log';
            file_put_contents($logPath, date('Y-m-d H:i:s') . " - Attempting login for $identifier\n", FILE_APPEND);
        } catch (\Throwable $e) {
        }

        $user = User::findByEmail($identifier);

        if (!$user) {
            // Fallback: Try username
            $user = User::findByUsername($identifier);
        }

        if (!$user) {
            try {
                file_put_contents($logPath, date('Y-m-d H:i:s') . " - User not found for identifier: $identifier\n", FILE_APPEND);
            } catch (\Throwable $e) {
            }
            return false;
        }

        try {
            file_put_contents($logPath, date('Y-m-d H:i:s') . " - User found: " . json_encode(['id' => $user['id'], 'email' => $user['email']]) . "\n", FILE_APPEND);
        } catch (\Throwable $e) {
        }

        if (password_verify($password, $user['password_hash'])) {
            try {
                file_put_contents($logPath, date('Y-m-d H:i:s') . " - Password verified for user ID: " . $user['id'] . "\n", FILE_APPEND);
            } catch (\Throwable $e) {
            }

            if ($user['two_factor_enabled'] ?? false) {
                try {
                    file_put_contents($logPath, date('Y-m-d H:i:s') . " - Two-factor authentication enabled for user ID: " . $user['id'] . ". Pending 2FA.\n", FILE_APPEND);
                } catch (\Throwable $e) {
                }
                $_SESSION['2fa_pending'] = $user['id'];
                return false;
            }

            self::login($user);
            try {
                file_put_contents($logPath, date('Y-m-d H:i:s') . " - User ID: " . $user['id'] . " logged in successfully.\n", FILE_APPEND);
            } catch (\Throwable $e) {
            }
            return true;
        }

        try {
            file_put_contents($logPath, date('Y-m-d H:i:s') . " - Password verification failed\n", FILE_APPEND);
        } catch (\Throwable $e) {
        }
        return false;
    }

    /**
     * @param array|\App\Models\User $user
     */
    public static function login($user): void
    {
        // Regenerate session ID to prevent session fixation attacks
        // DISABLED for QA Environment compatibility (causes 500s in some restricted runtimes)
        // session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        // Handle role access safely for both Array and Object
        $_SESSION['user_role'] = is_object($user) ? ($user->roles->first()->id ?? null) : ($user['role_id'] ?? null);
        $_SESSION['tenant_id'] = $user['tenant_id'] ?? null;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $_SESSION['login_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        self::$user = is_object($user) ? $user->toArray() : $user; // Store as array internally for now if needed, or better, adapt user() to return mixed

        try {
            User::updateLastLogin($user['id']);
        } catch (\Throwable $e) {
            // Ignore DB update failure for optional stats
        }
    }

    public static function logout(): void
    {
        session_destroy();
        self::$user = null;
    }

    public static function user(): ?array
    {
        if (self::$user === null && isset($_SESSION['user_id'])) {
            $userModel = User::find($_SESSION['user_id']);
            self::$user = $userModel ? $userModel->toArray() : null;
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
        if (!$user)
            return false;

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
