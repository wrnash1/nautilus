<?php

namespace App\Middleware;

use App\Core\Database;
use PDO;
use App\Core\Cache;

/**
 * Rate Limit Middleware
 * Prevents abuse by limiting requests per time window
 */
class RateLimitMiddleware
{
    private Cache $cache;
    private PDO $db;
    private int $maxAttempts;
    private int $decayMinutes;

    public function __construct(int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $this->cache = Cache::getInstance();
        $this->db = Database::getInstance();
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    /**
     * Handle rate limiting
     */
    public function handle(callable $next, ?int $maxAttempts = null, ??int $decayMinutes = null)
    {
        $maxAttempts = $maxAttempts ?? $this->maxAttempts;
        $decayMinutes = $decayMinutes ?? $this->decayMinutes;

        $identifier = $this->getIdentifier();
        $key = $this->getRateLimitKey($identifier);

        // Check if blocked
        if ($this->isBlocked($identifier)) {
            $this->sendRateLimitResponse();
            return;
        }

        // Get current attempts
        $attempts = $this->cache->get($key, 0);

        if ($attempts >= $maxAttempts) {
            // Block the user
            $this->blockIdentifier($identifier, $decayMinutes);
            $this->logSecurityEvent($identifier, 'rate_limit_exceeded');
            $this->sendRateLimitResponse();
            return;
        }

        // Increment attempts
        $this->cache->increment($key);

        // Set expiry if first attempt
        if ($attempts === 0) {
            $this->cache->set($key, 1, $decayMinutes * 60);
        }

        // Add rate limit headers
        $this->addRateLimitHeaders($maxAttempts, $attempts + 1, $decayMinutes);

        // Continue to next middleware
        return $next();
    }

    /**
     * Get identifier for rate limiting (IP + User Agent or User ID)
     */
    private function getIdentifier(): string
    {
        if (isset($_SESSION['user_id'])) {
            return 'user_' . $_SESSION['user_id'];
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        return md5($ip . $userAgent);
    }

    /**
     * Get rate limit cache key
     */
    private function getRateLimitKey(string $identifier): string
    {
        $route = $_SERVER['REQUEST_URI'] ?? '/';
        return "rate_limit:{$identifier}:{$route}";
    }

    /**
     * Check if identifier is blocked
     */
    private function isBlocked(string $identifier): bool
    {
        $blockKey = "rate_limit_block:{$identifier}";
        return $this->cache->has($blockKey);
    }

    /**
     * Block an identifier
     */
    private function blockIdentifier(string $identifier, int $minutes): void
    {
        $blockKey = "rate_limit_block:{$identifier}";
        $this->cache->set($blockKey, true, $minutes * 60);
    }

    /**
     * Add rate limit headers to response
     */
    private function addRateLimitHeaders(int $limit, int $used, int $decay): void
    {
        header("X-RateLimit-Limit: {$limit}");
        header("X-RateLimit-Remaining: " . max(0, $limit - $used));
        header("X-RateLimit-Reset: " . (time() + ($decay * 60)));
    }

    /**
     * Send rate limit exceeded response
     */
    private function sendRateLimitResponse(): void
    {
        http_response_code(429);
        header('Content-Type: application/json');
        header('Retry-After: ' . ($this->decayMinutes * 60));

        echo json_encode([
            'error' => 'Too Many Requests',
            'message' => 'Rate limit exceeded. Please try again later.',
            'retry_after' => $this->decayMinutes * 60
        ]);

        exit;
    }

    /**
     * Log security event
     */
    private function logSecurityEvent(string $identifier, string $eventType): void
    {
        try {
            $sql = "INSERT INTO security_events (event_type, severity, description, ip_address, user_id, details, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $eventType,
                'medium',
                'Rate limit exceeded',
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SESSION['user_id'] ?? null,
                json_encode([
                    'identifier' => $identifier,
                    'route' => $_SERVER['REQUEST_URI'] ?? '/',
                    'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET'
                ])
            ]);
        } catch (\Exception $e) {
            // Silent fail - don't break the application
        }
    }
}
