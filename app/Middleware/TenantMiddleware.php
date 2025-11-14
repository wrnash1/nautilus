<?php

namespace App\Middleware;

use App\Services\Tenant\TenantService;

/**
 * Tenant Middleware
 *
 * Identifies the current tenant based on subdomain or custom domain
 * and sets up tenant context for the request
 */
class TenantMiddleware
{
    private TenantService $tenantService;
    private static ?array $currentTenant = null;

    public function __construct()
    {
        $this->tenantService = new TenantService();
    }

    /**
     * Handle incoming request
     */
    public function handle(): bool
    {
        // Identify tenant from domain
        $tenant = $this->identifyTenant();

        if (!$tenant) {
            // No tenant found - redirect to main site or show error
            return false;
        }

        // Check if tenant is active
        if (!$this->isTenantActive($tenant)) {
            // Tenant is suspended or cancelled
            $this->showTenantInactivePage($tenant);
            return false;
        }

        // Store tenant in session and static property
        $_SESSION['tenant_id'] = $tenant['id'];
        $_SESSION['tenant_uuid'] = $tenant['tenant_uuid'];
        $_SESSION['tenant_data'] = $tenant;
        self::$currentTenant = $tenant;

        // Set tenant-specific configuration
        $this->configureTenant($tenant);

        return true;
    }

    /**
     * Identify tenant from request
     */
    private function identifyTenant(): ?array
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';

        // Remove port if present
        $host = explode(':', $host)[0];

        // Check for custom domain first
        $tenant = $this->tenantService->getTenantByDomain($host);

        if ($tenant) {
            return $tenant;
        }

        // Extract subdomain
        $subdomain = $this->extractSubdomain($host);

        if ($subdomain) {
            $tenant = $this->tenantService->getTenantBySubdomain($subdomain);

            if ($tenant) {
                return $tenant;
            }
        }

        // No tenant found
        return null;
    }

    /**
     * Extract subdomain from host
     */
    private function extractSubdomain(string $host): ?string
    {
        // Get the base domain from environment or configuration
        $baseDomain = $_ENV['BASE_DOMAIN'] ?? 'nautilus.local';

        // If host equals base domain, no subdomain
        if ($host === $baseDomain) {
            return null;
        }

        // Check if host ends with base domain
        if (str_ends_with($host, '.' . $baseDomain)) {
            // Extract subdomain
            $subdomain = substr($host, 0, -(strlen($baseDomain) + 1));

            // Validate subdomain (only one level)
            if (str_contains($subdomain, '.')) {
                return null;
            }

            return $subdomain;
        }

        return null;
    }

    /**
     * Check if tenant is active
     */
    private function isTenantActive(array $tenant): bool
    {
        if ($tenant['status'] === 'suspended' || $tenant['status'] === 'cancelled') {
            return false;
        }

        // Check if trial has expired
        if ($tenant['status'] === 'trial' && $tenant['trial_ends_at']) {
            if (strtotime($tenant['trial_ends_at']) < time()) {
                return false;
            }
        }

        // Check subscription status
        if ($tenant['subscription_status'] === 'cancelled') {
            return false;
        }

        return true;
    }

    /**
     * Configure application for tenant
     */
    private function configureTenant(array $tenant): void
    {
        // Set timezone
        if ($tenant['timezone']) {
            date_default_timezone_set($tenant['timezone']);
        }

        // Set locale
        if ($tenant['locale']) {
            setlocale(LC_ALL, $tenant['locale']);
        }

        // Store tenant configuration in constant for easy access
        if (!defined('CURRENT_TENANT_ID')) {
            define('CURRENT_TENANT_ID', $tenant['id']);
        }
    }

    /**
     * Show tenant inactive page
     */
    private function showTenantInactivePage(array $tenant): void
    {
        http_response_code(403);

        $reason = 'This account is currently inactive.';

        if ($tenant['status'] === 'trial' && $tenant['trial_ends_at']) {
            if (strtotime($tenant['trial_ends_at']) < time()) {
                $reason = 'Your trial period has expired. Please upgrade to continue using the service.';
            }
        } elseif ($tenant['status'] === 'suspended') {
            $reason = 'This account has been suspended. Please contact support for assistance.';
        } elseif ($tenant['subscription_status'] === 'past_due') {
            $reason = 'Your subscription payment is past due. Please update your payment method.';
        }

        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Account Inactive</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0;
                }
                .container {
                    background: white;
                    padding: 50px;
                    border-radius: 10px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    text-align: center;
                    max-width: 500px;
                }
                h1 {
                    color: #dc3545;
                    margin-bottom: 20px;
                }
                p {
                    color: #666;
                    line-height: 1.6;
                    margin-bottom: 30px;
                }
                .btn {
                    display: inline-block;
                    padding: 12px 30px;
                    background: #0066cc;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: 600;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Account Inactive</h1>
                <p><?= htmlspecialchars($reason) ?></p>
                <a href="mailto:support@example.com" class="btn">Contact Support</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    /**
     * Get current tenant
     */
    public static function getCurrentTenant(): ?array
    {
        return self::$currentTenant ?? $_SESSION['tenant_data'] ?? null;
    }

    /**
     * Get current tenant ID
     */
    public static function getCurrentTenantId(): ?int
    {
        if (defined('CURRENT_TENANT_ID')) {
            return CURRENT_TENANT_ID;
        }

        // Check session first (set during login)
        if (isset($_SESSION['tenant_id'])) {
            return (int)$_SESSION['tenant_id'];
        }

        $tenant = self::getCurrentTenant();
        return $tenant['id'] ?? null;
    }

    /**
     * Require tenant context (throw exception if not in tenant context)
     */
    public static function requireTenant(): void
    {
        if (!self::getCurrentTenantId()) {
            throw new \Exception('Tenant context required');
        }
    }
}
