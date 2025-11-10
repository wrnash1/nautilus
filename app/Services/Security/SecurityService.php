<?php

namespace App\Services\Security;

use App\Core\Database;
use PDO;
use App\Core\Logger;

/**
 * Security Service
 * Centralized security operations and monitoring
 */
class SecurityService
{
    private PDO $db;
    private Logger $logger;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new Logger();
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(
        string $eventType,
        string $severity,
        string $description,
        array $details = []
    ): void {
        try {
            $sql = "INSERT INTO security_events
                    (event_type, severity, description, ip_address, user_id, user_agent, details, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $eventType,
                $severity,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SESSION['user_id'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                json_encode($details)
            ]);

            // Log critical events
            if (in_array($severity, ['high', 'critical'])) {
                $this->logger->warning("Security Event: {$eventType}", [
                    'severity' => $severity,
                    'description' => $description,
                    'details' => $details
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to log security event', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get recent security events
     */
    public function getRecentEvents(int $limit = 50, ??string $severity = null): array
    {
        $sql = "SELECT * FROM security_events";

        if ($severity) {
            $sql .= " WHERE severity = ?";
            $params = [$severity];
        } else {
            $params = [];
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Check for suspicious activity
     */
    public function detectSuspiciousActivity(): array
    {
        $suspicious = [];

        // Check for multiple failed logins
        $failedLogins = $this->checkFailedLogins();
        if (!empty($failedLogins)) {
            $suspicious['failed_logins'] = $failedLogins;
        }

        // Check for unusual access patterns
        $unusualAccess = $this->checkUnusualAccess();
        if (!empty($unusualAccess)) {
            $suspicious['unusual_access'] = $unusualAccess;
        }

        // Check for rate limit violations
        $rateLimitViolations = $this->checkRateLimitViolations();
        if (!empty($rateLimitViolations)) {
            $suspicious['rate_limit_violations'] = $rateLimitViolations;
        }

        return $suspicious;
    }

    /**
     * Check for failed login attempts
     */
    private function checkFailedLogins(): array
    {
        $sql = "SELECT ip_address, COUNT(*) as attempts, MAX(created_at) as last_attempt
                FROM security_events
                WHERE event_type IN ('login_failed', 'brute_force')
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                GROUP BY ip_address
                HAVING attempts >= 5
                ORDER BY attempts DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Check for unusual access patterns
     */
    private function checkUnusualAccess(): array
    {
        $sql = "SELECT ip_address, COUNT(DISTINCT user_id) as users, COUNT(*) as events
                FROM security_events
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                GROUP BY ip_address
                HAVING users > 3
                ORDER BY users DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Check for rate limit violations
     */
    private function checkRateLimitViolations(): array
    {
        $sql = "SELECT ip_address, COUNT(*) as violations, MAX(created_at) as last_violation
                FROM security_events
                WHERE event_type = 'rate_limit_exceeded'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                GROUP BY ip_address
                ORDER BY violations DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Block IP address
     */
    public function blockIP(string $ip, int $duration = 86400, string $reason = ''): void
    {
        $sql = "INSERT INTO ip_blacklist (ip_address, blocked_until, reason, created_at)
                VALUES (?, DATE_ADD(NOW(), INTERVAL ? SECOND), ?, NOW())
                ON DUPLICATE KEY UPDATE
                    blocked_until = DATE_ADD(NOW(), INTERVAL ? SECOND),
                    reason = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ip, $duration, $reason, $duration, $reason]);

        $this->logSecurityEvent('ip_blocked', 'high', "IP address blocked: {$ip}", [
            'ip' => $ip,
            'duration' => $duration,
            'reason' => $reason
        ]);
    }

    /**
     * Unblock IP address
     */
    public function unblockIP(string $ip): void
    {
        $sql = "DELETE FROM ip_blacklist WHERE ip_address = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ip]);

        $this->logSecurityEvent('ip_unblocked', 'low', "IP address unblocked: {$ip}", [
            'ip' => $ip
        ]);
    }

    /**
     * Check if IP is blocked
     */
    public function isIPBlocked(string $ip): bool
    {
        $sql = "SELECT id FROM ip_blacklist
                WHERE ip_address = ?
                AND (blocked_until IS NULL OR blocked_until > NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ip]);

        return $stmt->fetch() !== false;
    }

    /**
     * Get security statistics
     */
    public function getSecurityStats(int $days = 7): array
    {
        $sql = "SELECT
                    event_type,
                    severity,
                    COUNT(*) as count,
                    DATE(created_at) as date
                FROM security_events
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY event_type, severity, DATE(created_at)
                ORDER BY date DESC, count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
