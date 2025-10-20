<?php

namespace App\Core;

/**
 * Application Logger
 * PSR-3 compliant logging system
 */
class Logger
{
    private string $logPath;
    private string $logLevel;
    private const LEVELS = [
        'emergency' => 0,
        'alert' => 1,
        'critical' => 2,
        'error' => 3,
        'warning' => 4,
        'notice' => 5,
        'info' => 6,
        'debug' => 7
    ];

    public function __construct()
    {
        $this->logPath = $_ENV['LOG_PATH'] ?? BASE_PATH . '/storage/logs';
        $this->logLevel = $_ENV['LOG_LEVEL'] ?? 'info';

        // Ensure log directory exists
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * System is unusable
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * Action must be taken immediately
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * Critical conditions
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Runtime errors
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Normal but significant events
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Interesting events
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Detailed debug information
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Main logging method
     */
    public function log(string $level, string $message, array $context = []): void
    {
        // Check if this log level should be logged
        if (!$this->shouldLog($level)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $level = strtoupper($level);

        // Interpolate context values into message
        $message = $this->interpolate($message, $context);

        // Format log entry
        $logEntry = sprintf(
            "[%s] %s: %s %s\n",
            $timestamp,
            $level,
            $message,
            !empty($context) ? json_encode($context, JSON_UNESCAPED_SLASHES) : ''
        );

        // Determine log file
        $logFile = $this->getLogFile($level);

        // Write to log file
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

        // Also log to database for important errors
        if (in_array(strtolower($level), ['emergency', 'alert', 'critical', 'error'])) {
            $this->logToDatabase($level, $message, $context);
        }
    }

    /**
     * Check if log level should be logged
     */
    private function shouldLog(string $level): bool
    {
        $currentLevel = self::LEVELS[$this->logLevel] ?? 6;
        $messageLevel = self::LEVELS[$level] ?? 6;

        return $messageLevel <= $currentLevel;
    }

    /**
     * Interpolate context values into message placeholders
     */
    private function interpolate(string $message, array $context = []): string
    {
        $replace = [];

        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }

    /**
     * Get log file path based on level
     */
    private function getLogFile(string $level): string
    {
        $date = date('Y-m-d');

        // Critical errors go to separate file
        if (in_array(strtolower($level), ['emergency', 'alert', 'critical', 'error'])) {
            return $this->logPath . "/error-{$date}.log";
        }

        return $this->logPath . "/app-{$date}.log";
    }

    /**
     * Log to database for critical errors
     */
    private function logToDatabase(string $level, string $message, array $context): void
    {
        try {
            $db = Database::getInstance();

            $sql = "INSERT INTO error_logs (level, message, context, url, ip_address, user_id, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $db->getConnection()->prepare($sql);
            $stmt->execute([
                strtolower($level),
                $message,
                json_encode($context),
                $_SERVER['REQUEST_URI'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SESSION['user_id'] ?? null
            ]);
        } catch (\Exception $e) {
            // If database logging fails, write to file
            error_log("Failed to log to database: " . $e->getMessage());
        }
    }

    /**
     * Clear old log files (keep last 30 days)
     */
    public function cleanOldLogs(int $daysToKeep = 30): int
    {
        $deletedCount = 0;
        $cutoffTime = time() - ($daysToKeep * 86400);

        $files = glob($this->logPath . '/*.log');

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
