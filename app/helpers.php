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
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = str_replace('/index.php', '', $scriptName);

        if ($basePath && strpos($path, $basePath) !== 0) {
            $path = $basePath . $path;
        }

        return $path;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = str_replace('/index.php', '', $scriptName);

        if ($basePath && strpos($path, $basePath) !== 0) {
            $path = $basePath . $path;
        }

        header("Location: {$path}");
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
                "INSERT INTO audit_logs (user_id, action, module, entity_id, ip_address, user_agent)
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
