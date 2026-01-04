<?php

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('dd')) {
    function dd($var): void
    {
        var_dump($var);
        die();
    }
}

if (!function_exists('url')) {
    function url(string $path): string
    {
        $baseUrl = $_ENV['APP_URL'] ?? '';

        // Remove trailing slash from base URL
        $baseUrl = rtrim($baseUrl, '/');

        // Ensure path has leading slash
        if (!empty($path) && strpos($path, '/') !== 0) {
            $path = '/' . $path;
        }

        return $baseUrl . $path;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        $fullUrl = url($path);

        header("Location: {$fullUrl}");
        exit;
    }
}

if (!function_exists('jsonResponse')) {
    function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input)
    {
        if (is_array($input)) {
            return array_map('sanitizeInput', $input);
        }

        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency(float $amount): string
    {
        return '$' . number_format($amount, 2);
    }
}

if (!function_exists('generateSku')) {
    function generateSku(string $prefix = ''): string
    {
        return strtoupper($prefix . uniqid());
    }
}

if (!function_exists('logActivity')) {
    function logActivity(string $action, string $module, ?int $entityId = null): void
    {
        if (isset($_SESSION['user_id'])) {
            \App\Core\Database::query(
                "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, user_agent)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $_SESSION['user_id'],
                    $action,
                    $module,
                    $entityId,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                ]
            );
        }
    }
}

if (!function_exists('hasPermission')) {
    function hasPermission(string $permission): bool
    {
        return \App\Core\Auth::hasPermission($permission);
    }
}

if (!function_exists('currentUser')) {
    function currentUser(): ?array
    {
        return \App\Core\Auth::user();
    }
}

if (!function_exists('getSettingValue')) {
    /**
     * Get a setting value from database
     *
     * @param string $key The setting key
     * @param string $category The setting category (default: 'general')
     * @param mixed $default Default value if setting not found
     * @return mixed
     */
    function getSettingValue(string $key, string $category = 'general', $default = null)
    {
        try {
            $settingsService = new \App\Services\Settings\SettingsService();
            return $settingsService->get($key, $category, $default);
        } catch (\Exception $e) {
            // If database not connected yet (during install), return default
            return $default;
        }
    }
}

if (!function_exists('setSettingValue')) {
    /**
     * Set a setting value in database
     *
     * @param string $key The setting key
     * @param mixed $value The value to set
     * @param string $category The setting category (default: 'general')
     * @param string $type The value type (string, integer, boolean, json)
     * @param string|null $description Setting description
     * @return bool
     */
    function setSettingValue(string $key, $value, string $category = 'general', string $type = 'string', ?string $description = null): bool
    {
        try {
            $settingsService = new \App\Services\Settings\SettingsService();
            $userId = $_SESSION['user_id'] ?? null;
            return $settingsService->set($key, $value, $category, $type, $description, $userId);
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('getCompanyInfo')) {
    /**
     * Get company/business information
     *
     * @return array Company information array
     */
    function getCompanyInfo(): array
    {
        try {
            $settings = \App\Core\Settings::getInstance();
            return $settings->getCompanyInfo();
        } catch (\Exception $e) {
            // Return defaults if database not available
            return [
                'name' => 'Nautilus Dive Shop',
                'email' => '',
                'phone' => '',
                'address' => '',
                'city' => '',
                'state' => '',
                'zip' => '',
                'country' => 'US',
                'logo' => '',
                'logo_small' => '',
                'favicon' => '',
                'primary_color' => '#0066cc',
                'secondary_color' => '#003366',
            ];
        }
    }
}

if (!function_exists('setFlashMessage')) {
    /**
     * Set a flash message to display on next page load
     *
     * @param string $type The message type (success, error, warning, info)
     * @param string $message The message content
     * @return void
     */
    function setFlashMessage(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_messages'][$type][] = $message;
    }
}

if (!function_exists('getFlashMessages')) {
    /**
     * Get and clear all flash messages
     *
     * @return array Flash messages by type
     */
    function getFlashMessages(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }
}

if (!function_exists('hasFlashMessages')) {
    /**
     * Check if there are any flash messages
     *
     * @return bool
     */
    function hasFlashMessages(): bool
    {
        return !empty($_SESSION['flash_messages']);
    }
}

if (!function_exists('logAudit')) {
    /**
     * Log an audit entry for entity changes
     *
     * @param string $entityType The type of entity (user, customer, order, etc.)
     * @param string $action The action performed (create, update, delete, etc.)
     * @param int $entityId The ID of the entity
     * @param array $data Optional additional data to log
     * @return void
     */
    function logAudit(string $entityType, string $action, int $entityId, array $data = []): void
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;
            $details = !empty($data) ? json_encode($data) : null;

            \App\Core\Database::query(
                "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $userId,
                    $action,
                    $entityType,
                    $entityId,
                    $details,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                ]
            );
        } catch (\Exception $e) {
            // Silently fail - don't break the main operation for audit logging
            error_log('Audit log failed: ' . $e->getMessage());
        }
    }
}
