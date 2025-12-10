<?php

namespace App\Services;

use App\Core\Database;
use Exception;

/**
 * Error Logging Service
 * Logs application errors to database for tracking and debugging
 */
class ErrorLogService
{
    /**
     * Log an error to the database
     */
    public static function log(
        string $errorType,
        string $errorMessage,
        ?string $errorFile = null,
        ?int $errorLine = null,
        ?string $stackTrace = null,
        ?array $context = []
    ): ?int {
        try {
            $tenantId = $_SESSION['tenant_id'] ?? 1;
            $userId = $_SESSION['user_id'] ?? null;

            // Get request information
            $requestUri = $_SERVER['REQUEST_URI'] ?? null;
            $requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
            $userIp = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $sessionId = session_id();

            // Capture request data (sanitized)
            $requestData = json_encode([
                'GET' => $_GET ?? [],
                'POST' => self::sanitizePostData($_POST ?? []),
                'context' => $context
            ]);

            $errorId = Database::execute(
                "INSERT INTO application_errors (
                    tenant_id, error_type, error_message, error_file, error_line,
                    stack_trace, request_uri, request_method, request_data,
                    user_id, user_ip, user_agent, session_id, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $tenantId, $errorType, $errorMessage, $errorFile, $errorLine,
                    $stackTrace, $requestUri, $requestMethod, $requestData,
                    $userId, $userIp, $userAgent, $sessionId
                ]
            );

            // If critical error, notify admins
            if (in_array($errorType, ['fatal', 'error'])) {
                self::notifyAdmins($errorType, $errorMessage, $errorFile, $errorLine);
            }

            return $errorId;

        } catch (Exception $e) {
            // Fail silently - don't break the app if error logging fails
            error_log("Error logging failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Log an exception
     */
    public static function logException(Exception $e, ?array $context = []): ?int
    {
        return self::log(
            'error',
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString(),
            $context
        );
    }

    /**
     * Get recent unresolved errors
     */
    public static function getRecentErrors(int $limit = 50): array
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        return Database::fetchAll(
            "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
             FROM application_errors e
             LEFT JOIN users u ON e.user_id = u.id
             WHERE e.tenant_id = ? AND e.is_resolved = FALSE
             ORDER BY e.created_at DESC
             LIMIT ?",
            [$tenantId, $limit]
        );
    }

    /**
     * Get error by ID
     */
    public static function getError(int $errorId): ?array
    {
        $error = Database::fetchOne(
            "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
             FROM application_errors e
             LEFT JOIN users u ON e.user_id = u.id
             WHERE e.id = ?",
            [$errorId]
        );

        return $error ?: null;
    }

    /**
     * Mark error as resolved
     */
    public static function resolveError(int $errorId, string $resolutionNotes = ''): bool
    {
        $userId = $_SESSION['user_id'] ?? null;

        return Database::execute(
            "UPDATE application_errors
             SET is_resolved = TRUE, resolved_at = NOW(), resolved_by = ?, resolution_notes = ?
             WHERE id = ?",
            [$userId, $resolutionNotes, $errorId]
        ) > 0;
    }

    /**
     * Get error statistics
     */
    public static function getErrorStats(int $days = 7): array
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        $stats = Database::fetchOne(
            "SELECT
                COUNT(*) as total_errors,
                SUM(CASE WHEN is_resolved = FALSE THEN 1 ELSE 0 END) as unresolved,
                SUM(CASE WHEN error_type = 'fatal' THEN 1 ELSE 0 END) as fatal_errors,
                SUM(CASE WHEN error_type = 'error' THEN 1 ELSE 0 END) as errors,
                SUM(CASE WHEN error_type = 'warning' THEN 1 ELSE 0 END) as warnings
             FROM application_errors
             WHERE tenant_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$tenantId, $days]
        );

        return $stats;
    }

    /**
     * Sanitize POST data to remove sensitive information
     */
    private static function sanitizePostData(array $data): array
    {
        $sensitiveKeys = [
            'password', 'passwd', 'pwd', 'api_key', 'api_token', 'secret',
            'credit_card', 'card_number', 'cvv', 'ssn', 'access_token'
        ];

        foreach ($data as $key => $value) {
            foreach ($sensitiveKeys as $sensitive) {
                if (stripos($key, $sensitive) !== false) {
                    $data[$key] = '[REDACTED]';
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Notify admins of critical errors
     */
    private static function notifyAdmins(
        string $errorType,
        string $errorMessage,
        ?string $errorFile,
        ?int $errorLine
    ): void {
        // Check if email notifications are enabled
        $emailEnabled = Database::fetchOne(
            "SELECT setting_value FROM system_settings
             WHERE setting_key = 'email_critical_errors' AND setting_value = 'true'"
        );

        if (!$emailEnabled) {
            return;
        }

        // Get admin email
        $adminEmail = Database::fetchOne(
            "SELECT setting_value FROM system_settings WHERE setting_key = 'error_admin_email'"
        );

        if (!$adminEmail || empty($adminEmail['setting_value'])) {
            return;
        }

        // Send email (implement your email service here)
        $subject = "[$errorType] Application Error - Nautilus";
        $body = "Error: $errorMessage\nFile: $errorFile\nLine: $errorLine\nTime: " . date('Y-m-d H:i:s');

        // TODO: Implement email sending
        // mail($adminEmail['setting_value'], $subject, $body);
    }
}
