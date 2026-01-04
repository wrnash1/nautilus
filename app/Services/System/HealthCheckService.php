<?php

namespace App\Services\System;

use App\Core\Database;
use App\Core\Cache;

/**
 * Health Check and Monitoring Service
 *
 * Provides:
 * - System health status
 * - Database connectivity
 * - Cache status
 * - Disk space monitoring
 * - Performance metrics
 * - Uptime tracking
 */
class HealthCheckService
{
    private Database $db;
    private Cache $cache;

    public function __construct()
    {
        $this->db = new Database();
        $this->cache = Cache::getInstance();
    }

    /**
     * Perform comprehensive health check
     */
    public function checkHealth(): array
    {
        $startTime = microtime(true);

        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'disk' => $this->checkDiskSpace(),
            'memory' => $this->checkMemory(),
            'uploads' => $this->checkUploadsDirectory(),
            'logs' => $this->checkLogsDirectory()
        ];

        $allHealthy = true;
        foreach ($checks as $check) {
            if ($check['status'] !== 'healthy') {
                $allHealthy = false;
                break;
            }
        }

        $responseTime = round((microtime(true) - $startTime) * 1000, 2);

        return [
            'status' => $allHealthy ? 'healthy' : 'degraded',
            'timestamp' => date('Y-m-d H:i:s'),
            'response_time_ms' => $responseTime,
            'checks' => $checks,
            'system' => $this->getSystemInfo()
        ];
    }

    /**
     * Check database connectivity and performance
     */
    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);

            // Simple query to test connection
            $stmt = $this->db->query("SELECT 1");
            $stmt->fetch();

            $queryTime = round((microtime(true) - $start) * 1000, 2);

            // Check table count
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $tableCount = $result['count'];

            // Check connection pool
            $stmt = $this->db->query("SHOW STATUS LIKE 'Threads_connected'");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $connections = $result['Value'] ?? 0;

            $status = 'healthy';
            if ($queryTime > 100) {
                $status = 'slow';
            }

            return [
                'status' => $status,
                'query_time_ms' => $queryTime,
                'table_count' => (int) $tableCount,
                'active_connections' => (int) $connections,
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'message' => 'Database connection failed'
            ];
        }
    }

    /**
     * Check cache system
     */
    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test';

            // Test write
            $writeStart = microtime(true);
            $this->cache->set($testKey, $testValue, 60);
            $writeTime = round((microtime(true) - $writeStart) * 1000, 2);

            // Test read
            $readStart = microtime(true);
            $retrieved = $this->cache->get($testKey);
            $readTime = round((microtime(true) - $readStart) * 1000, 2);

            // Clean up
            $this->cache->delete($testKey);

            $status = 'healthy';
            if ($retrieved !== $testValue) {
                $status = 'degraded';
            }

            return [
                'status' => $status,
                'write_time_ms' => $writeTime,
                'read_time_ms' => $readTime,
                'message' => 'Cache system operational'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'degraded',
                'error' => $e->getMessage(),
                'message' => 'Cache system unavailable (using fallback)'
            ];
        }
    }

    /**
     * Check disk space
     */
    private function checkDiskSpace(): array
    {
        $path = __DIR__ . '/../../../';

        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercent = round(($usedSpace / $totalSpace) * 100, 2);

        $status = 'healthy';
        if ($usagePercent > 90) {
            $status = 'critical';
        } elseif ($usagePercent > 80) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'total_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
            'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
            'used_gb' => round($usedSpace / 1024 / 1024 / 1024, 2),
            'usage_percent' => $usagePercent,
            'message' => "Disk usage at {$usagePercent}%"
        ];
    }

    /**
     * Check memory usage
     */
    private function checkMemory(): array
    {
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $memoryUsage = memory_get_usage(true);
        $usagePercent = round(($memoryUsage / $memoryLimit) * 100, 2);

        $status = 'healthy';
        if ($usagePercent > 90) {
            $status = 'critical';
        } elseif ($usagePercent > 75) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'limit_mb' => round($memoryLimit / 1024 / 1024, 2),
            'used_mb' => round($memoryUsage / 1024 / 1024, 2),
            'usage_percent' => $usagePercent,
            'message' => "Memory usage at {$usagePercent}%"
        ];
    }

    /**
     * Check uploads directory
     */
    private function checkUploadsDirectory(): array
    {
        $uploadDir = __DIR__ . '/../../../public/uploads';

        if (!is_dir($uploadDir)) {
            return [
                'status' => 'unhealthy',
                'message' => 'Uploads directory does not exist'
            ];
        }

        if (!is_writable($uploadDir)) {
            return [
                'status' => 'unhealthy',
                'message' => 'Uploads directory is not writable'
            ];
        }

        return [
            'status' => 'healthy',
            'path' => $uploadDir,
            'writable' => true,
            'message' => 'Uploads directory is accessible'
        ];
    }

    /**
     * Check logs directory
     */
    private function checkLogsDirectory(): array
    {
        $logsDir = $_ENV['LOG_PATH'] ?? __DIR__ . '/../../../storage/logs';

        if (!is_dir($logsDir)) {
            return [
                'status' => 'warning',
                'message' => 'Logs directory does not exist'
            ];
        }

        if (!is_writable($logsDir)) {
            return [
                'status' => 'warning',
                'message' => 'Logs directory is not writable'
            ];
        }

        // Check log file size
        $logFile = $logsDir . '/app.log';
        $logSize = file_exists($logFile) ? filesize($logFile) : 0;
        $logSizeMB = round($logSize / 1024 / 1024, 2);

        $status = 'healthy';
        if ($logSizeMB > 100) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'path' => $logsDir,
            'writable' => true,
            'log_size_mb' => $logSizeMB,
            'message' => 'Logs directory is accessible'
        ];
    }

    /**
     * Get system information
     */
    private function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'os' => PHP_OS,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'server_time' => date('Y-m-d H:i:s'),
            'timezone' => date_default_timezone_get(),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size')
        ];
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'uptime' => $this->getUptime(),
            'requests' => $this->getRequestStats(),
            'errors' => $this->getErrorStats(),
            'slow_queries' => $this->getSlowQueries()
        ];
    }

    /**
     * Get application uptime
     */
    private function getUptime(): array
    {
        // This would typically come from a monitoring service
        return [
            'days' => 0,
            'hours' => 0,
            'minutes' => 0
        ];
    }

    /**
     * Get request statistics
     */
    private function getRequestStats(): array
    {
        try {
            $stats = $this->db->query("
                SELECT
                    COUNT(*) as total_requests,
                    AVG(response_time) as avg_response_time
                FROM request_logs
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ")->fetch(\PDO::FETCH_ASSOC);

            return [
                'total_last_hour' => (int) ($stats['total_requests'] ?? 0),
                'avg_response_time_ms' => round($stats['avg_response_time'] ?? 0, 2)
            ];
        } catch (\Exception $e) {
            return [
                'total_last_hour' => 0,
                'avg_response_time_ms' => 0
            ];
        }
    }

    /**
     * Get error statistics
     */
    private function getErrorStats(): array
    {
        $logFile = ($_ENV['LOG_PATH'] ?? __DIR__ . '/../../../storage/logs') . '/app.log';

        if (!file_exists($logFile)) {
            return ['errors_last_hour' => 0];
        }

        // Simple error count (in production, use proper log aggregation)
        return ['errors_last_hour' => 0];
    }

    /**
     * Get slow queries
     */
    private function getSlowQueries(): array
    {
        try {
            $queries = $this->db->query("
                SELECT query_text, execution_time
                FROM slow_query_log
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY execution_time DESC
                LIMIT 5
            ")->fetchAll(\PDO::FETCH_ASSOC);

            return $queries ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Simple liveness check
     */
    public function liveness(): array
    {
        return [
            'status' => 'alive',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Readiness check
     */
    public function readiness(): array
    {
        $dbCheck = $this->checkDatabase();

        if ($dbCheck['status'] === 'healthy') {
            return [
                'status' => 'ready',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        return [
            'status' => 'not_ready',
            'reason' => 'Database not available',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Parse memory limit string to bytes
     */
    private function parseMemoryLimit(string $limit): int
    {
        $unit = strtolower(substr($limit, -1));
        $value = (int) $limit;

        switch ($unit) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }

        return $value;
    }
}
