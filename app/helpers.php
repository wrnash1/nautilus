<?php

function env(string $key, $default = null)
{
    return $_ENV[$key] ?? $default;
}

function dd($var): void
{
    var_dump($var);
    die();
}

function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function sanitizeInput($input)
{
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function formatCurrency(float $amount): string
{
    return '$' . number_format($amount, 2);
}

function generateSku(string $prefix = ''): string
{
    return strtoupper($prefix . uniqid());
}

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
