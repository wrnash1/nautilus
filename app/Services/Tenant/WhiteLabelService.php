<?php

namespace App\Services\Tenant;

use App\Core\TenantDatabase;
use App\Core\Cache;

/**
 * White-Label Customization Service
 *
 * Allows tenants to fully customize their branding:
 * - Custom domain names
 * - Logo and favicon
 * - Color schemes and themes
 * - Custom CSS
 * - Email templates
 * - Custom terminology
 * - Multi-language support
 */
class WhiteLabelService
{
    private Cache $cache;

    public function __construct()
    {
        $this->cache = new Cache();
    }

    /**
     * Get tenant branding configuration
     */
    public function getBranding(int $tenantId): array
    {
        $cacheKey = "branding_{$tenantId}";
        $cached = $this->cache->get($cacheKey);

        if ($cached !== false) {
            return json_decode($cached, true);
        }

        $branding = TenantDatabase::fetchOneTenant(
            "SELECT * FROM tenant_branding WHERE tenant_id = ?",
            [$tenantId]
        );

        if (!$branding) {
            $branding = $this->getDefaultBranding($tenantId);
        } else {
            $branding['theme_settings'] = json_decode($branding['theme_settings'] ?? '{}', true);
            $branding['email_settings'] = json_decode($branding['email_settings'] ?? '{}', true);
            $branding['custom_terminology'] = json_decode($branding['custom_terminology'] ?? '{}', true);
        }

        $this->cache->set($cacheKey, json_encode($branding), 3600);

        return $branding;
    }

    /**
     * Update tenant branding
     */
    public function updateBranding(int $tenantId, array $data): bool
    {
        $existing = TenantDatabase::fetchOneTenant(
            "SELECT id FROM tenant_branding WHERE tenant_id = ?",
            [$tenantId]
        );

        $brandingData = [
            'tenant_id' => $tenantId,
            'company_name' => $data['company_name'] ?? null,
            'logo_url' => $data['logo_url'] ?? null,
            'favicon_url' => $data['favicon_url'] ?? null,
            'primary_color' => $data['primary_color'] ?? '#1976d2',
            'secondary_color' => $data['secondary_color'] ?? '#dc004e',
            'accent_color' => $data['accent_color'] ?? '#f50057',
            'theme_mode' => $data['theme_mode'] ?? 'light',
            'custom_css' => $data['custom_css'] ?? null,
            'custom_domain' => $data['custom_domain'] ?? null,
            'theme_settings' => json_encode($data['theme_settings'] ?? []),
            'email_settings' => json_encode($data['email_settings'] ?? []),
            'custom_terminology' => json_encode($data['custom_terminology'] ?? []),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            TenantDatabase::updateTenant('tenant_branding', $brandingData, 'id = ?', [$existing['id']]);
        } else {
            $brandingData['created_at'] = date('Y-m-d H:i:s');
            TenantDatabase::insertTenant('tenant_branding', $brandingData);
        }

        // Clear cache
        $this->cache->delete("branding_{$tenantId}");

        return true;
    }

    /**
     * Upload and set logo
     */
    public function setLogo(int $tenantId, array $file): array
    {
        $uploadDir = __DIR__ . '/../../../public/uploads/logos/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = "logo_{$tenantId}_" . time() . ".{$extension}";
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $logoUrl = "/uploads/logos/{$filename}";

            $this->updateBranding($tenantId, ['logo_url' => $logoUrl]);

            return ['success' => true, 'url' => $logoUrl];
        }

        return ['success' => false, 'error' => 'Upload failed'];
    }

    /**
     * Set custom domain
     */
    public function setCustomDomain(int $tenantId, string $domain): array
    {
        // Validate domain
        if (!$this->validateDomain($domain)) {
            return ['success' => false, 'error' => 'Invalid domain format'];
        }

        // Check if domain is already in use
        $existing = TenantDatabase::fetchOneTenant(
            "SELECT tenant_id FROM tenant_branding WHERE custom_domain = ? AND tenant_id != ?",
            [$domain, $tenantId]
        );

        if ($existing) {
            return ['success' => false, 'error' => 'Domain already in use'];
        }

        // Update branding
        $this->updateBranding($tenantId, ['custom_domain' => $domain]);

        // Create DNS verification token
        $verificationToken = bin2hex(random_bytes(16));

        TenantDatabase::updateTenant('tenant_branding', [
            'domain_verification_token' => $verificationToken,
            'domain_verified' => false
        ], 'tenant_id = ?', [$tenantId]);

        return [
            'success' => true,
            'domain' => $domain,
            'verification_token' => $verificationToken,
            'instructions' => "Add TXT record: nautilus-verify={$verificationToken}"
        ];
    }

    /**
     * Verify custom domain
     */
    public function verifyCustomDomain(int $tenantId): array
    {
        $branding = TenantDatabase::fetchOneTenant(
            "SELECT custom_domain, domain_verification_token FROM tenant_branding WHERE tenant_id = ?",
            [$tenantId]
        );

        if (!$branding || !$branding['custom_domain']) {
            return ['success' => false, 'error' => 'No custom domain configured'];
        }

        // Check DNS TXT record
        $txtRecords = @dns_get_record($branding['custom_domain'], DNS_TXT);
        $verified = false;

        if ($txtRecords) {
            foreach ($txtRecords as $record) {
                if (isset($record['txt']) && $record['txt'] === "nautilus-verify={$branding['domain_verification_token']}") {
                    $verified = true;
                    break;
                }
            }
        }

        if ($verified) {
            TenantDatabase::updateTenant('tenant_branding', [
                'domain_verified' => true,
                'domain_verified_at' => date('Y-m-d H:i:s')
            ], 'tenant_id = ?', [$tenantId]);

            return ['success' => true, 'verified' => true];
        }

        return ['success' => false, 'verified' => false, 'error' => 'Verification record not found'];
    }

    /**
     * Get custom CSS for tenant
     */
    public function getCustomCSS(int $tenantId): string
    {
        $branding = $this->getBranding($tenantId);

        $css = ":root {\n";
        $css .= "  --primary-color: {$branding['primary_color']};\n";
        $css .= "  --secondary-color: {$branding['secondary_color']};\n";
        $css .= "  --accent-color: {$branding['accent_color']};\n";
        $css .= "}\n\n";

        if (!empty($branding['custom_css'])) {
            $css .= $branding['custom_css'];
        }

        return $css;
    }

    /**
     * Update email template
     */
    public function updateEmailTemplate(int $tenantId, string $templateName, string $content): bool
    {
        $existing = TenantDatabase::fetchOneTenant(
            "SELECT id FROM email_templates WHERE tenant_id = ? AND template_name = ?",
            [$tenantId, $templateName]
        );

        $data = [
            'tenant_id' => $tenantId,
            'template_name' => $templateName,
            'content' => $content,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            TenantDatabase::updateTenant('email_templates', $data, 'id = ?', [$existing['id']]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            TenantDatabase::insertTenant('email_templates', $data);
        }

        return true;
    }

    /**
     * Get email template
     */
    public function getEmailTemplate(int $tenantId, string $templateName): ?string
    {
        $template = TenantDatabase::fetchOneTenant(
            "SELECT content FROM email_templates WHERE tenant_id = ? AND template_name = ?",
            [$tenantId, $templateName]
        );

        return $template ? $template['content'] : $this->getDefaultEmailTemplate($templateName);
    }

    /**
     * Set custom terminology
     */
    public function setCustomTerminology(int $tenantId, array $terms): bool
    {
        $branding = $this->getBranding($tenantId);
        $currentTerms = $branding['custom_terminology'] ?? [];

        $updatedTerms = array_merge($currentTerms, $terms);

        return $this->updateBranding($tenantId, [
            'custom_terminology' => $updatedTerms
        ]);
    }

    /**
     * Get term translation
     */
    public function getTerm(int $tenantId, string $key): string
    {
        $branding = $this->getBranding($tenantId);
        $terms = $branding['custom_terminology'] ?? [];

        return $terms[$key] ?? $this->getDefaultTerm($key);
    }

    /**
     * Generate theme configuration
     */
    public function generateThemeConfig(int $tenantId): array
    {
        $branding = $this->getBranding($tenantId);

        return [
            'palette' => [
                'mode' => $branding['theme_mode'],
                'primary' => [
                    'main' => $branding['primary_color']
                ],
                'secondary' => [
                    'main' => $branding['secondary_color']
                ],
                'error' => [
                    'main' => '#f44336'
                ],
                'warning' => [
                    'main' => '#ff9800'
                ],
                'info' => [
                    'main' => '#2196f3'
                ],
                'success' => [
                    'main' => '#4caf50'
                ]
            ],
            'typography' => [
                'fontFamily' => $branding['theme_settings']['font_family'] ?? 'Roboto, sans-serif',
                'fontSize' => $branding['theme_settings']['font_size'] ?? 14
            ],
            'shape' => [
                'borderRadius' => $branding['theme_settings']['border_radius'] ?? 4
            ],
            'components' => $branding['theme_settings']['components'] ?? []
        ];
    }

    /**
     * Export branding configuration
     */
    public function exportBranding(int $tenantId): array
    {
        return $this->getBranding($tenantId);
    }

    /**
     * Import branding configuration
     */
    public function importBranding(int $tenantId, array $config): bool
    {
        return $this->updateBranding($tenantId, $config);
    }

    /**
     * Reset to default branding
     */
    public function resetToDefault(int $tenantId): bool
    {
        TenantDatabase::deleteTenant('tenant_branding', 'tenant_id = ?', [$tenantId]);
        $this->cache->delete("branding_{$tenantId}");

        return true;
    }

    // Private helper methods

    private function getDefaultBranding(int $tenantId): array
    {
        return [
            'tenant_id' => $tenantId,
            'company_name' => 'Nautilus Dive Shop',
            'logo_url' => '/assets/img/logo.png',
            'favicon_url' => '/assets/img/favicon.ico',
            'primary_color' => '#1976d2',
            'secondary_color' => '#dc004e',
            'accent_color' => '#f50057',
            'theme_mode' => 'light',
            'custom_css' => null,
            'custom_domain' => null,
            'theme_settings' => [],
            'email_settings' => [],
            'custom_terminology' => []
        ];
    }

    private function validateDomain(string $domain): bool
    {
        return preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i', $domain);
    }

    private function getDefaultEmailTemplate(string $templateName): string
    {
        $templates = [
            'welcome' => '<h1>Welcome to {{company_name}}!</h1><p>Thank you for joining us.</p>',
            'invoice' => '<h1>Invoice #{{invoice_number}}</h1><p>Total: {{total}}</p>',
            'receipt' => '<h1>Receipt</h1><p>Thank you for your purchase!</p>',
            'password_reset' => '<h1>Password Reset</h1><p>Click the link to reset your password: {{reset_link}}</p>'
        ];

        return $templates[$templateName] ?? '';
    }

    private function getDefaultTerm(string $key): string
    {
        $terms = [
            'customer' => 'Customer',
            'customers' => 'Customers',
            'product' => 'Product',
            'products' => 'Products',
            'order' => 'Order',
            'orders' => 'Orders',
            'rental' => 'Rental',
            'rentals' => 'Rentals',
            'course' => 'Course',
            'courses' => 'Courses',
            'trip' => 'Trip',
            'trips' => 'Trips',
            'invoice' => 'Invoice',
            'receipt' => 'Receipt',
            'checkout' => 'Checkout'
        ];

        return $terms[$key] ?? ucfirst($key);
    }
}
