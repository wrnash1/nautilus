<?php

namespace App\Middleware;

use App\Core\Cache;

/**
 * Cache Middleware
 * Caches HTTP responses for configured routes
 */
class CacheMiddleware
{
    private Cache $cache;
    private int $defaultTTL;

    public function __construct()
    {
        $this->cache = Cache::getInstance();
        $this->defaultTTL = 300; // 5 minutes default
    }

    /**
     * Handle request caching
     */
    public function handle(callable $next, int $ttl = null)
    {
        $ttl = $ttl ?? $this->defaultTTL;

        // Only cache GET requests
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return $next();
        }

        // Don't cache authenticated users (unless specifically configured)
        if (isset($_SESSION['user_id'])) {
            return $next();
        }

        // Generate cache key from request URI and query params
        $cacheKey = $this->getCacheKey();

        // Try to get cached response
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            // Return cached response
            header('X-Cache: HIT');
            echo $cached;
            return;
        }

        // Start output buffering
        ob_start();

        // Execute the route handler
        $next();

        // Get the output
        $output = ob_get_clean();

        // Cache the output
        $this->cache->set($cacheKey, $output, $ttl);

        // Send the output
        header('X-Cache: MISS');
        echo $output;
    }

    /**
     * Generate cache key from request
     */
    private function getCacheKey(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $query = $_SERVER['QUERY_STRING'] ?? '';

        return 'http_cache:' . md5($uri . '|' . $query);
    }

    /**
     * Clear cache for a specific route
     */
    public static function clearRoute(string $route): bool
    {
        $cache = Cache::getInstance();
        $cacheKey = 'http_cache:' . md5($route);

        return $cache->delete($cacheKey);
    }

    /**
     * Clear all HTTP cache
     */
    public static function clearAll(): bool
    {
        // This would need to be implemented based on cache driver
        // For now, we can clear specific patterns
        return true;
    }
}
