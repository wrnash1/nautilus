<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Cache;

/**
 * Health Check Controller
 * Provides system health and readiness checks for load balancers and monitoring
 */
class HealthCheckController extends Controller
{
    /**
     * Basic health check - is the application running?
     * Returns 200 if app is alive
     */
    public function index(): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'healthy',
            'timestamp' => date('c'),
            'app' => 'Nautilus Dive Shop',
            'version' => '1.0.0'
        ]);
        exit;
    }

    /**
     * Detailed health check - check all dependencies
     * Returns 200 if all systems are operational
     * Returns 503 if any critical system is down
     */
    public function detailed(): void
    {
        $checks = [];
        $overall_status = 'healthy';
        $http_code = 200;

        // 1. Database Check
        try {
            $db = Database::getPdo();
            $stmt = $db->query("SELECT 1");
            $checks['database'] = [
                'status' => 'healthy',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $checks['database'] = [
                'status' => 'unhealthy',
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ];
            $overall_status = 'unhealthy';
            $http_code = 503;
        }

        // 2. Cache Check
        try {
            $cache = new Cache();
            $test_key = 'health_check_' . time();
            $cache->set($test_key, 'test', 60);
            $value = $cache->get($test_key);
            $cache->delete($test_key);

            $checks['cache'] = [
                'status' => 'healthy',
                'message' => 'Cache is operational',
                'driver' => $_ENV['CACHE_DRIVER'] ?? 'file'
            ];
        } catch (\Exception $e) {
            $checks['cache'] = [
                'status' => 'degraded',
                'message' => 'Cache unavailable, using fallback',
                'error' => $e->getMessage()
            ];
            // Cache failure is not critical - app can function without it
            if ($overall_status === 'healthy') {
                $overall_status = 'degraded';
            }
        }

        // 3. File Storage Check
        $upload_dir = BASE_PATH . '/public/uploads';
        if (is_writable($upload_dir)) {
            $checks['storage'] = [
                'status' => 'healthy',
                'message' => 'File storage is writable',
                'path' => $upload_dir
            ];
        } else {
            $checks['storage'] = [
                'status' => 'unhealthy',
                'message' => 'File storage is not writable',
                'path' => $upload_dir
            ];
            $overall_status = 'unhealthy';
            $http_code = 503;
        }

        // 4. Session Storage Check
        try {
            session_start();
            $_SESSION['health_check'] = time();
            $checks['sessions'] = [
                'status' => 'healthy',
                'message' => 'Session storage operational'
            ];
        } catch (\Exception $e) {
            $checks['sessions'] = [
                'status' => 'unhealthy',
                'message' => 'Session storage failed',
                'error' => $e->getMessage()
            ];
            $overall_status = 'unhealthy';
            $http_code = 503;
        }

        // 5. Disk Space Check
        $free_space = disk_free_space(BASE_PATH);
        $total_space = disk_total_space(BASE_PATH);
        $free_percent = ($free_space / $total_space) * 100;

        if ($free_percent < 10) {
            $checks['disk_space'] = [
                'status' => 'critical',
                'message' => 'Disk space critically low',
                'free_percent' => round($free_percent, 2)
            ];
            $overall_status = 'unhealthy';
            $http_code = 503;
        } elseif ($free_percent < 20) {
            $checks['disk_space'] = [
                'status' => 'warning',
                'message' => 'Disk space running low',
                'free_percent' => round($free_percent, 2)
            ];
        } else {
            $checks['disk_space'] = [
                'status' => 'healthy',
                'message' => 'Disk space adequate',
                'free_percent' => round($free_percent, 2)
            ];
        }

        // 6. PHP Memory Check
        $memory_limit = ini_get('memory_limit');
        $memory_used = memory_get_usage(true);
        $checks['php'] = [
            'status' => 'healthy',
            'version' => PHP_VERSION,
            'memory_limit' => $memory_limit,
            'memory_used' => $this->formatBytes($memory_used)
        ];

        // Response
        header('Content-Type: application/json');
        http_response_code($http_code);
        echo json_encode([
            'status' => $overall_status,
            'timestamp' => date('c'),
            'app' => 'Nautilus Dive Shop',
            'version' => '1.0.0',
            'checks' => $checks
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Readiness check - is the application ready to serve traffic?
     * Used by Kubernetes/container orchestrators
     */
    public function ready(): void
    {
        $ready = true;
        $errors = [];

        // Check database connectivity
        try {
            $db = Database::getPdo();
            $db->query("SELECT 1");
        } catch (\Exception $e) {
            $ready = false;
            $errors[] = 'Database not ready';
        }

        // Check critical tables exist
        try {
            $tables = ['users', 'customers', 'products', 'transactions'];
            foreach ($tables as $table) {
                $stmt = $db->query("SELECT 1 FROM {$table} LIMIT 1");
            }
        } catch (\Exception $e) {
            $ready = false;
            $errors[] = 'Critical tables missing';
        }

        header('Content-Type: application/json');
        http_response_code($ready ? 200 : 503);
        echo json_encode([
            'ready' => $ready,
            'timestamp' => date('c'),
            'errors' => $errors
        ]);
        exit;
    }

    /**
     * Liveness check - is the application alive?
     * Used by Kubernetes/container orchestrators
     */
    public function alive(): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'alive' => true,
            'timestamp' => date('c')
        ]);
        exit;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
