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
