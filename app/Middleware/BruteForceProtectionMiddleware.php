<?php

namespace App\Middleware;

use App\Core\Database;
use PDO;
use App\Core\Cache;

/**
 * Brute Force Protection Middleware
 * Protects login and sensitive endpoints from brute force attacks
 */
class BruteForceProtectionMiddleware
{
    private Cache $cache;
    private PDO $db;
    private int $maxAttempts = 5;
    private int $blockDuration = 900; // 15 minutes in seconds

    public function __construct()
    {
        $this->cache = Cache::getInstance();
        $this->db = Database::getPdo();
    }

    /**
     * Handle brute force protection
     */
    public function handle(callable $next, string $action = 'login')
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $identifier = $this->getIdentifier($action);

        // Check if IP is blocked
        if ($this->isBlocked($identifier)) {
            $this->sendBlockedResponse();
            return;
        }

        // Execute the action
        $result = $next();

        // If login failed, record attempt
        if ($this->isFailedAttempt()) {
            $this->recordFailedAttempt($identifier, $action, $ip);
        }

        return $result;
    }

    /**
     * Get unique identifier for brute force tracking
     */
    private function getIdentifier(string $action): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return "brute_force:{$action}:{$ip}";
    }

    /**
     * Check if identifier is blocked
     */
    private function isBlocked(string $identifier): bool
    {
        $blockKey = "{$identifier}:blocked";
        return $this->cache->has($blockKey);
    }

    /**
     * Record failed attempt
     */
    private function recordFailedAttempt(string $identifier, string $action, string $ip): void
    {
        $attempts = $this->cache->increment($identifier);

        // Set expiry on first attempt
        if ($attempts === 1) {
            $this->cache->set($identifier, 1, $this->blockDuration);
        }

        // Log security event
        $this->logSecurityEvent($action, $ip, $attempts);

        // Block if max attempts reached
        if ($attempts >= $this->maxAttempts) {
            $blockKey = "{$identifier}:blocked";
            $this->cache->set($blockKey, true, $this->blockDuration);

            $this->logSecurityEvent("brute_force_block", $ip, $attempts);
        }
    }

    /**
     * Check if current request is a failed attempt
     */
    private function isFailedAttempt(): bool
    {
        // Check for login failure indicators
        if (isset($_SESSION['login_failed']) && $_SESSION['login_failed']) {
            unset($_SESSION['login_failed']);
            return true;
        }

        return false;
    }

    /**
     * Send blocked response
     */
    private function sendBlockedResponse(): void
    {
        http_response_code(429);
        header('Content-Type: application/json');

        echo json_encode([
            'error' => 'Too Many Failed Attempts',
            'message' => 'Your IP has been temporarily blocked due to multiple failed login attempts.',
            'retry_after' => $this->blockDuration
        ]);

        exit;
    }

    /**
     * Log security event
     */
    private function logSecurityEvent(string $eventType, string $ip, int $attempts): void
    {
        try {
            $sql = "INSERT INTO security_events
                    (event_type, severity, description, ip_address, user_agent, details, action_taken, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $severity = $attempts >= $this->maxAttempts ? 'high' : 'medium';
            $actionTaken = $attempts >= $this->maxAttempts ? 'blocked' : 'logged';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $eventType,
                $severity,
                "Failed login attempt #{$attempts}",
                $ip,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                json_encode([
                    'attempts' => $attempts,
                    'max_attempts' => $this->maxAttempts,
                    'username_attempted' => $_POST['username'] ?? null
                ]),
                $actionTaken
            ]);
        } catch (\Exception $e) {
            // Silent fail
        }
    }

    /**
     * Clear failed attempts for identifier (call on successful login)
     */
    public static function clearAttempts(string $action = 'login'): void
    {
        $cache = Cache::getInstance();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $identifier = "brute_force:{$action}:{$ip}";

        $cache->delete($identifier);
        $cache->delete("{$identifier}:blocked");
    }
}
