<?php

namespace App\Services\Tenant;

use App\Core\Database;
use App\Core\Logger;

/**
 * Tenant Service
 *
 * Manages multi-tenant functionality including:
 * - Tenant creation and management
 * - Subscription management
 * - Usage tracking
 * - Quota enforcement
 * - White-label customization
 */
class TenantService
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Create a new tenant
     */
    public function createTenant(array $data): array
    {
        try {
            // Generate UUID for tenant
            $uuid = $this->generateUUID();

            // Generate subdomain from company name if not provided
            $subdomain = $data['subdomain'] ?? $this->generateSubdomain($data['company_name']);

            // Validate subdomain is available
            if (!$this->isSubdomainAvailable($subdomain)) {
                throw new \Exception('Subdomain is already taken');
            }

            // Insert tenant
            Database::query(
                "INSERT INTO tenants (
                    tenant_uuid, company_name, subdomain, custom_domain,
                    contact_name, contact_email, contact_phone,
                    plan_id, status, subscription_status,
                    timezone, locale, currency,
                    address_line1, address_line2, city, state, postal_code, country,
                    trial_ends_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $uuid,
                    $data['company_name'],
                    $subdomain,
                    $data['custom_domain'] ?? null,
                    $data['contact_name'] ?? null,
                    $data['contact_email'],
                    $data['contact_phone'] ?? null,
                    $data['plan_id'] ?? 1, // Default to Starter plan
                    'trial',
                    'trialing',
                    $data['timezone'] ?? 'UTC',
                    $data['locale'] ?? 'en_US',
                    $data['currency'] ?? 'USD',
                    $data['address_line1'] ?? null,
                    $data['address_line2'] ?? null,
                    $data['city'] ?? null,
                    $data['state'] ?? null,
                    $data['postal_code'] ?? null,
                    $data['country'] ?? null,
                    date('Y-m-d H:i:s', strtotime('+14 days')) // 14-day trial
                ]
            );

            $tenantId = Database::lastInsertId();

            // Initialize onboarding
            $this->initializeOnboarding($tenantId);

            // Create default settings
            $this->createDefaultSettings($tenantId);

            $this->logger->info("Created new tenant: {$data['company_name']}", [
                'tenant_id' => $tenantId,
                'subdomain' => $subdomain
            ]);

            return [
                'success' => true,
                'tenant_id' => $tenantId,
                'tenant_uuid' => $uuid,
                'subdomain' => $subdomain
            ];

        } catch (\Exception $e) {
            $this->logger->error('Failed to create tenant', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get tenant by ID
     */
    public function getTenantById(int $tenantId): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM tenants WHERE id = ? AND deleted_at IS NULL",
            [$tenantId]
        );
    }

    /**
     * Get tenant by UUID
     */
    public function getTenantByUUID(string $uuid): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM tenants WHERE tenant_uuid = ? AND deleted_at IS NULL",
            [$uuid]
        );
    }

    /**
     * Get tenant by subdomain
     */
    public function getTenantBySubdomain(string $subdomain): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM tenants WHERE subdomain = ? AND deleted_at IS NULL",
            [$subdomain]
        );
    }

    /**
     * Get tenant by custom domain
     */
    public function getTenantByDomain(string $domain): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM tenants WHERE custom_domain = ? AND deleted_at IS NULL",
            [$domain]
        );
    }

    /**
     * Update tenant information
     */
    public function updateTenant(int $tenantId, array $data): bool
    {
        $updates = [];
        $params = [];

        $allowedFields = [
            'company_name', 'contact_name', 'contact_email', 'contact_phone',
            'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country',
            'timezone', 'locale', 'currency',
            'logo_url', 'favicon_url', 'primary_color', 'secondary_color'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $params[] = $tenantId;

        Database::query(
            "UPDATE tenants SET " . implode(', ', $updates) . " WHERE id = ?",
            $params
        );

        $this->logger->info("Updated tenant {$tenantId}");
        return true;
    }

    /**
     * Update tenant subscription
     */
    public function updateSubscription(int $tenantId, int $planId): bool
    {
        $plan = $this->getSubscriptionPlan($planId);

        if (!$plan) {
            return false;
        }

        Database::query(
            "UPDATE tenants SET
                plan_id = ?,
                max_users = ?,
                max_storage_mb = ?,
                max_products = ?,
                max_transactions_per_month = ?,
                updated_at = NOW()
            WHERE id = ?",
            [
                $planId,
                $plan['max_users'],
                $plan['max_storage_mb'],
                $plan['max_products'],
                $plan['max_transactions_per_month'],
                $tenantId
            ]
        );

        $this->logger->info("Updated subscription for tenant {$tenantId} to plan {$planId}");
        return true;
    }

    /**
     * Get subscription plan
     */
    public function getSubscriptionPlan(int $planId): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM subscription_plans WHERE id = ? AND is_active = 1",
            [$planId]
        );
    }

    /**
     * Get all subscription plans
     */
    public function getAllPlans(): array
    {
        return Database::fetchAll(
            "SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY display_order, monthly_price"
        ) ?? [];
    }

    /**
     * Check if tenant is within usage quotas
     */
    public function checkQuota(int $tenantId, string $quotaType): bool
    {
        $tenant = $this->getTenantById($tenantId);

        if (!$tenant) {
            return false;
        }

        switch ($quotaType) {
            case 'users':
                $currentCount = Database::fetchOne(
                    "SELECT COUNT(*) as count FROM users WHERE tenant_id = ? AND is_active = 1",
                    [$tenantId]
                )['count'] ?? 0;

                return $currentCount < $tenant['max_users'];

            case 'products':
                $currentCount = Database::fetchOne(
                    "SELECT COUNT(*) as count FROM products WHERE tenant_id = ? AND is_active = 1",
                    [$tenantId]
                )['count'] ?? 0;

                return $currentCount < $tenant['max_products'];

            case 'transactions':
                $startOfMonth = date('Y-m-01');
                $currentCount = Database::fetchOne(
                    "SELECT COUNT(*) as count FROM transactions
                     WHERE tenant_id = ? AND created_at >= ?",
                    [$tenantId, $startOfMonth]
                )['count'] ?? 0;

                return $currentCount < $tenant['max_transactions_per_month'];

            case 'storage':
                // Calculate storage usage
                $usage = $this->calculateStorageUsage($tenantId);
                return $usage < ($tenant['max_storage_mb'] * 1024 * 1024); // Convert MB to bytes

            default:
                return false;
        }
    }

    /**
     * Calculate storage usage for tenant
     */
    private function calculateStorageUsage(int $tenantId): int
    {
        // This is a simplified version
        // In production, you'd want to sum up actual file sizes
        return 0;
    }

    /**
     * Track usage for billing
     */
    public function trackUsage(int $tenantId): void
    {
        $usageDate = date('Y-m-d');

        // Get current metrics
        $activeUsers = Database::fetchOne(
            "SELECT COUNT(DISTINCT u.id) as count
             FROM users u
             JOIN tenant_users tu ON u.id = tu.user_id
             WHERE tu.tenant_id = ? AND u.is_active = 1",
            [$tenantId]
        )['count'] ?? 0;

        $transactionsToday = Database::fetchOne(
            "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as value
             FROM transactions
             WHERE tenant_id = ? AND DATE(created_at) = ?",
            [$tenantId, $usageDate]
        );

        $productsCount = Database::fetchOne(
            "SELECT COUNT(*) as count FROM products WHERE tenant_id = ? AND is_active = 1",
            [$tenantId]
        )['count'] ?? 0;

        $customersCount = Database::fetchOne(
            "SELECT COUNT(*) as count FROM customers WHERE tenant_id = ? AND is_active = 1",
            [$tenantId]
        )['count'] ?? 0;

        // Insert or update usage record
        Database::query(
            "INSERT INTO tenant_usage (
                tenant_id, usage_date, active_users,
                transactions_count, transactions_value,
                products_count, customers_count
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                active_users = VALUES(active_users),
                transactions_count = VALUES(transactions_count),
                transactions_value = VALUES(transactions_value),
                products_count = VALUES(products_count),
                customers_count = VALUES(customers_count),
                updated_at = NOW()",
            [
                $tenantId,
                $usageDate,
                $activeUsers,
                $transactionsToday['count'] ?? 0,
                $transactionsToday['value'] ?? 0,
                $productsCount,
                $customersCount
            ]
        );
    }

    /**
     * Log tenant activity
     */
    public function logActivity(int $tenantId, ?int $userId, string $activityType, ?string $entityType = null, ?int $entityId = null, ?string $description = null, ?array $metadata = null): void
    {
        Database::query(
            "INSERT INTO tenant_activity_log (
                tenant_id, user_id, activity_type, entity_type, entity_id,
                description, ip_address, user_agent, metadata
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $tenantId,
                $userId,
                $activityType,
                $entityType,
                $entityId,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $metadata ? json_encode($metadata) : null
            ]
        );
    }

    /**
     * Generate unique UUID
     */
    private function generateUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Generate subdomain from company name
     */
    private function generateSubdomain(string $companyName): string
    {
        // Convert to lowercase and remove special characters
        $subdomain = strtolower($companyName);
        $subdomain = preg_replace('/[^a-z0-9-]/', '-', $subdomain);
        $subdomain = preg_replace('/-+/', '-', $subdomain);
        $subdomain = trim($subdomain, '-');

        // Ensure uniqueness
        $original = $subdomain;
        $counter = 1;

        while (!$this->isSubdomainAvailable($subdomain)) {
            $subdomain = $original . '-' . $counter;
            $counter++;
        }

        return $subdomain;
    }

    /**
     * Check if subdomain is available
     */
    private function isSubdomainAvailable(string $subdomain): bool
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count FROM tenants WHERE subdomain = ?",
            [$subdomain]
        );

        return ($result['count'] ?? 0) == 0;
    }

    /**
     * Initialize onboarding for new tenant
     */
    private function initializeOnboarding(int $tenantId): void
    {
        Database::query(
            "INSERT INTO tenant_onboarding (tenant_id) VALUES (?)",
            [$tenantId]
        );
    }

    /**
     * Create default settings for tenant
     */
    private function createDefaultSettings(int $tenantId): void
    {
        $defaultSettings = [
            ['notifications_enabled', 'true', 'boolean'],
            ['low_stock_threshold', '5', 'number'],
            ['default_tax_rate', '0', 'number'],
            ['receipt_footer', 'Thank you for your business!', 'string'],
            ['allow_backorders', 'false', 'boolean']
        ];

        foreach ($defaultSettings as $setting) {
            Database::query(
                "INSERT INTO tenant_settings (tenant_id, setting_key, setting_value, setting_type)
                 VALUES (?, ?, ?, ?)",
                [$tenantId, $setting[0], $setting[1], $setting[2]]
            );
        }
    }

    /**
     * Get tenant setting
     */
    public function getSetting(int $tenantId, string $key, $default = null)
    {
        $setting = Database::fetchOne(
            "SELECT setting_value, setting_type FROM tenant_settings
             WHERE tenant_id = ? AND setting_key = ?",
            [$tenantId, $key]
        );

        if (!$setting) {
            return $default;
        }

        // Cast to appropriate type
        switch ($setting['setting_type']) {
            case 'boolean':
                return filter_var($setting['setting_value'], FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($setting['setting_value']) ? (float)$setting['setting_value'] : $default;
            case 'json':
                return json_decode($setting['setting_value'], true);
            default:
                return $setting['setting_value'];
        }
    }

    /**
     * Set tenant setting
     */
    public function setSetting(int $tenantId, string $key, $value, string $type = 'string'): void
    {
        if ($type === 'json') {
            $value = json_encode($value);
        } elseif ($type === 'boolean') {
            $value = $value ? 'true' : 'false';
        }

        Database::query(
            "INSERT INTO tenant_settings (tenant_id, setting_key, setting_value, setting_type)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()",
            [$tenantId, $key, $value, $type]
        );
    }
}
