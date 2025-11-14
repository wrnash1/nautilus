<?php

namespace App\Services\API;

use App\Core\Cache;
use App\Core\TenantDatabase;

/**
 * API Rate Limiting and Usage Tracking Service
 *
 * Features:
 * - Token bucket algorithm
 * - Per-tenant rate limits
 * - Usage tracking and analytics
 * - Automatic throttling
 * - Quota management
 * - Burst allowance
 */
class RateLimitService
{
    private Cache $cache;
    private const DEFAULT_LIMIT = 1000; // requests per hour
    private const DEFAULT_BURST = 50;   // burst allowance

    public function __construct()
    {
        $this->cache = Cache::getInstance();
    }

    /**
     * Check if request is allowed
     */
    public function checkLimit(int $tenantId, ?string $endpoint = null): array
    {
        $limits = $this->getTenantLimits($tenantId);

        $key = "rate_limit:{$tenantId}";
        if ($endpoint) {
            $key .= ":{$endpoint}";
        }

        // Get current usage
        $usage = $this->cache->get($key);

        if ($usage === false) {
            $usage = [
                'count' => 0,
                'reset_at' => time() + 3600, // 1 hour from now
                'burst_used' => 0
            ];
        }

        $usage = json_decode($usage, true);

        // Check if reset time has passed
        if (time() >= $usage['reset_at']) {
            $usage = [
                'count' => 0,
                'reset_at' => time() + 3600,
                'burst_used' => 0
            ];
        }

        // Check limits
        $limit = $limits['hourly_limit'] ?? self::DEFAULT_LIMIT;
        $burst = $limits['burst_allowance'] ?? self::DEFAULT_BURST;

        $allowed = true;
        $reason = null;

        if ($usage['count'] >= $limit) {
            if ($usage['burst_used'] < $burst) {
                // Allow burst
                $usage['burst_used']++;
            } else {
                $allowed = false;
                $reason = 'Rate limit exceeded';
            }
        }

        if ($allowed) {
            $usage['count']++;

            // Record usage
            $this->recordUsage($tenantId, $endpoint);
        }

        // Store updated usage
        $this->cache->set($key, json_encode($usage), 3600);

        return [
            'allowed' => $allowed,
            'reason' => $reason,
            'limit' => $limit,
            'remaining' => max(0, $limit - $usage['count']),
            'reset_at' => $usage['reset_at'],
            'burst_remaining' => max(0, $burst - $usage['burst_used'])
        ];
    }

    /**
     * Record API usage
     */
    private function recordUsage(int $tenantId, ?string $endpoint): void
    {
        TenantDatabase::insertTenant('api_usage', [
            'tenant_id' => $tenantId,
            'endpoint' => $endpoint,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }

    /**
     * Get tenant rate limits
     */
    private function getTenantLimits(int $tenantId): array
    {
        $limits = $this->cache->get("tenant_limits:{$tenantId}");

        if ($limits === false) {
            $config = TenantDatabase::fetchOneTenant(
                "SELECT api_rate_limit, api_burst_limit FROM tenants WHERE id = ?",
                [$tenantId]
            );

            $limits = [
                'hourly_limit' => $config['api_rate_limit'] ?? self::DEFAULT_LIMIT,
                'burst_allowance' => $config['api_burst_limit'] ?? self::DEFAULT_BURST
            ];

            $this->cache->set("tenant_limits:{$tenantId}", json_encode($limits), 3600);
        } else {
            $limits = json_decode($limits, true);
        }

        return $limits;
    }

    /**
     * Get usage statistics
     */
    public function getUsageStats(int $tenantId, string $period = '24h'): array
    {
        $dateFilter = match($period) {
            '1h' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            '24h' => date('Y-m-d H:i:s', strtotime('-24 hours')),
            '7d' => date('Y-m-d H:i:s', strtotime('-7 days')),
            '30d' => date('Y-m-d H:i:s', strtotime('-30 days')),
            default => date('Y-m-d H:i:s', strtotime('-24 hours'))
        };

        $stats = TenantDatabase::fetchOneTenant("
            SELECT
                COUNT(*) as total_requests,
                COUNT(DISTINCT endpoint) as unique_endpoints,
                COUNT(DISTINCT ip_address) as unique_ips
            FROM api_usage
            WHERE tenant_id = ?
            AND timestamp >= ?
        ", [$tenantId, $dateFilter]);

        $topEndpoints = TenantDatabase::fetchAllTenant("
            SELECT endpoint, COUNT(*) as count
            FROM api_usage
            WHERE tenant_id = ?
            AND timestamp >= ?
            GROUP BY endpoint
            ORDER BY count DESC
            LIMIT 10
        ", [$tenantId, $dateFilter]) ?? [];

        $hourlyBreakdown = TenantDatabase::fetchAllTenant("
            SELECT
                DATE_FORMAT(timestamp, '%Y-%m-%d %H:00:00') as hour,
                COUNT(*) as requests
            FROM api_usage
            WHERE tenant_id = ?
            AND timestamp >= ?
            GROUP BY hour
            ORDER BY hour ASC
        ", [$tenantId, $dateFilter]) ?? [];

        return [
            'total_requests' => (int) $stats['total_requests'],
            'unique_endpoints' => (int) $stats['unique_endpoints'],
            'unique_ips' => (int) $stats['unique_ips'],
            'top_endpoints' => $topEndpoints,
            'hourly_breakdown' => $hourlyBreakdown,
            'period' => $period
        ];
    }

    /**
     * Update tenant rate limits
     */
    public function updateLimits(int $tenantId, int $hourlyLimit, int $burstLimit): bool
    {
        $query = "UPDATE tenants SET api_rate_limit = ?, api_burst_limit = ? WHERE id = ?";
        $stmt = TenantDatabase::prepare($query);
        $result = $stmt->execute([$hourlyLimit, $burstLimit, $tenantId]);

        if ($result) {
            $this->cache->delete("tenant_limits:{$tenantId}");
        }

        return $result;
    }

    /**
     * Reset rate limit for tenant (emergency use)
     */
    public function resetLimit(int $tenantId, ?string $endpoint = null): bool
    {
        $key = "rate_limit:{$tenantId}";
        if ($endpoint) {
            $key .= ":{$endpoint}";
        }

        return $this->cache->delete($key);
    }

    /**
     * Block tenant API access
     */
    public function blockTenant(int $tenantId, string $reason): bool
    {
        $this->cache->set("tenant_blocked:{$tenantId}", json_encode([
            'blocked' => true,
            'reason' => $reason,
            'blocked_at' => time()
        ]), 86400); // 24 hours

        return true;
    }

    /**
     * Unblock tenant API access
     */
    public function unblockTenant(int $tenantId): bool
    {
        return $this->cache->delete("tenant_blocked:{$tenantId}");
    }

    /**
     * Check if tenant is blocked
     */
    public function isBlocked(int $tenantId): array
    {
        $blocked = $this->cache->get("tenant_blocked:{$tenantId}");

        if ($blocked === false) {
            return ['blocked' => false];
        }

        return json_decode($blocked, true);
    }

    /**
     * Get rate limit headers for response
     */
    public function getRateLimitHeaders(int $tenantId, ?string $endpoint = null): array
    {
        $check = $this->checkLimit($tenantId, $endpoint);

        return [
            'X-RateLimit-Limit' => $check['limit'],
            'X-RateLimit-Remaining' => $check['remaining'],
            'X-RateLimit-Reset' => $check['reset_at']
        ];
    }
}
