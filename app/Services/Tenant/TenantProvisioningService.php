<?php

namespace App\Services\Tenant;

use App\Core\Database;
use App\Core\TenantDatabase;
use App\Services\Email\EmailService;

/**
 * Tenant Provisioning and Onboarding Service
 *
 * Handles:
 * - Automated tenant creation
 * - Database provisioning
 * - Default data seeding
 * - Onboarding workflow
 * - Setup wizard
 * - Demo data creation
 * - Tenant suspension/reactivation
 */
class TenantProvisioningService
{
    private EmailService $emailService;
    private Database $db;

    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->db = new Database();
    }

    /**
     * Provision a new tenant
     */
    public function provisionTenant(array $data): array
    {
        try {
            // Validate required fields
            if (empty($data['company_name']) || empty($data['email']) || empty($data['subdomain'])) {
                return ['success' => false, 'error' => 'Missing required fields'];
            }

            // Validate subdomain
            if (!$this->validateSubdomain($data['subdomain'])) {
                return ['success' => false, 'error' => 'Invalid subdomain format'];
            }

            // Check if subdomain is available
            if ($this->subdomainExists($data['subdomain'])) {
                return ['success' => false, 'error' => 'Subdomain already taken'];
            }

            // Create tenant record
            $tenantId = $this->createTenantRecord($data);

            // Set tenant context
            $_SESSION['tenant_id'] = $tenantId;

            // Create default database structure
            $this->createTenantSchema($tenantId);

            // Seed default data
            $this->seedDefaultData($tenantId, $data);

            // Create admin user
            $userId = $this->createAdminUser($tenantId, $data);

            // Initialize onboarding
            $this->initializeOnboarding($tenantId);

            // Send welcome email
            $this->sendWelcomeEmail($data['email'], [
                'company_name' => $data['company_name'],
                'subdomain' => $data['subdomain'],
                'login_url' => "https://{$data['subdomain']}.nautilusdive.com"
            ]);

            return [
                'success' => true,
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'subdomain' => $data['subdomain'],
                'login_url' => "https://{$data['subdomain']}.nautilusdive.com"
            ];

        } catch (\Exception $e) {
            error_log("Tenant provisioning failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create tenant database record
     */
    private function createTenantRecord(array $data): int
    {
        $query = "INSERT INTO tenants (
            company_name, subdomain, status, plan_id,
            created_at, settings
        ) VALUES (?, ?, 'active', ?, NOW(), ?)";

        $settings = json_encode([
            'timezone' => $data['timezone'] ?? 'America/New_York',
            'currency' => $data['currency'] ?? 'USD',
            'language' => $data['language'] ?? 'en'
        ]);

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $data['company_name'],
            $data['subdomain'],
            $data['plan_id'] ?? 1,
            $settings
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Create tenant-specific schema
     */
    private function createTenantSchema(int $tenantId): void
    {
        // All tables already exist with tenant_id column
        // Just need to ensure indexes are created
        $tables = [
            'users', 'customers', 'products', 'categories',
            'transactions', 'transaction_items',
            'rentals', 'courses', 'course_enrollments',
            'trips', 'work_orders'
        ];

        foreach ($tables as $table) {
            // Create tenant-specific indexes if not exists
            $indexName = "idx_{$table}_tenant";
            $query = "CREATE INDEX IF NOT EXISTS {$indexName} ON {$table}(tenant_id)";

            try {
                $this->db->exec($query);
            } catch (\Exception $e) {
                // Index may already exist
            }
        }
    }

    /**
     * Seed default data for tenant
     */
    private function seedDefaultData(int $tenantId, array $data): void
    {
        // Create default roles
        $this->createDefaultRoles($tenantId);

        // Create default permissions
        $this->createDefaultPermissions($tenantId);

        // Create default categories
        $this->createDefaultCategories($tenantId);

        // Create default settings
        $this->createDefaultSettings($tenantId, $data);

        // Optionally create demo data
        if ($data['include_demo_data'] ?? false) {
            $this->createDemoData($tenantId);
        }
    }

    /**
     * Create default roles
     */
    private function createDefaultRoles(int $tenantId): void
    {
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Full system access'],
            ['name' => 'manager', 'display_name' => 'Manager', 'description' => 'Management access'],
            ['name' => 'instructor', 'display_name' => 'Instructor', 'description' => 'Course instructor'],
            ['name' => 'staff', 'display_name' => 'Staff', 'description' => 'General staff member'],
            ['name' => 'sales', 'display_name' => 'Sales', 'description' => 'Sales and POS access']
        ];

        foreach ($roles as $role) {
            TenantDatabase::insertTenant('roles', array_merge($role, [
                'tenant_id' => $tenantId,
                'created_at' => date('Y-m-d H:i:s')
            ]));
        }
    }

    /**
     * Create default permissions
     */
    private function createDefaultPermissions(int $tenantId): void
    {
        $permissions = [
            'view_dashboard', 'manage_users', 'manage_customers', 'manage_products',
            'view_reports', 'manage_pos', 'manage_rentals', 'manage_courses',
            'manage_trips', 'manage_settings', 'view_analytics', 'manage_inventory'
        ];

        foreach ($permissions as $permission) {
            TenantDatabase::insertTenant('permissions', [
                'tenant_id' => $tenantId,
                'name' => $permission,
                'display_name' => ucwords(str_replace('_', ' ', $permission)),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Assign all permissions to admin role
        $adminRole = TenantDatabase::fetchOneTenant(
            "SELECT id FROM roles WHERE tenant_id = ? AND name = 'admin'",
            [$tenantId]
        );

        $allPermissions = TenantDatabase::fetchAllTenant(
            "SELECT id FROM permissions WHERE tenant_id = ?",
            [$tenantId]
        ) ?? [];

        foreach ($allPermissions as $perm) {
            TenantDatabase::insertTenant('role_permissions', [
                'role_id' => $adminRole['id'],
                'permission_id' => $perm['id']
            ]);
        }
    }

    /**
     * Create default categories
     */
    private function createDefaultCategories(int $tenantId): void
    {
        $categories = [
            ['name' => 'Regulators', 'type' => 'product'],
            ['name' => 'BCDs', 'type' => 'product'],
            ['name' => 'Masks & Fins', 'type' => 'product'],
            ['name' => 'Wetsuits', 'type' => 'product'],
            ['name' => 'Dive Computers', 'type' => 'product'],
            ['name' => 'Accessories', 'type' => 'product'],
            ['name' => 'Open Water', 'type' => 'course'],
            ['name' => 'Advanced', 'type' => 'course'],
            ['name' => 'Specialty', 'type' => 'course']
        ];

        foreach ($categories as $category) {
            TenantDatabase::insertTenant('categories', array_merge($category, [
                'tenant_id' => $tenantId,
                'created_at' => date('Y-m-d H:i:s')
            ]));
        }
    }

    /**
     * Create default settings
     */
    private function createDefaultSettings(int $tenantId, array $data): void
    {
        $settings = [
            ['key' => 'company_name', 'value' => $data['company_name']],
            ['key' => 'timezone', 'value' => $data['timezone'] ?? 'America/New_York'],
            ['key' => 'currency', 'value' => $data['currency'] ?? 'USD'],
            ['key' => 'language', 'value' => $data['language'] ?? 'en'],
            ['key' => 'date_format', 'value' => 'Y-m-d'],
            ['key' => 'time_format', 'value' => 'H:i'],
            ['key' => 'tax_rate', 'value' => '0.00'],
            ['key' => 'email_from', 'value' => $data['email']],
            ['key' => 'low_stock_threshold', 'value' => '5']
        ];

        foreach ($settings as $setting) {
            TenantDatabase::insertTenant('settings', array_merge($setting, [
                'tenant_id' => $tenantId,
                'created_at' => date('Y-m-d H:i:s')
            ]));
        }
    }

    /**
     * Create admin user
     */
    private function createAdminUser(int $tenantId, array $data): int
    {
        $password = $data['password'] ?? bin2hex(random_bytes(8));
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $userId = TenantDatabase::insertTenant('users', [
            'tenant_id' => $tenantId,
            'email' => $data['email'],
            'password' => $hashedPassword,
            'first_name' => $data['first_name'] ?? 'Admin',
            'last_name' => $data['last_name'] ?? 'User',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Assign admin role
        $adminRole = TenantDatabase::fetchOneTenant(
            "SELECT id FROM roles WHERE tenant_id = ? AND name = 'admin'",
            [$tenantId]
        );

        TenantDatabase::insertTenant('user_roles', [
            'user_id' => $userId,
            'role_id' => $adminRole['id']
        ]);

        return $userId;
    }

    /**
     * Initialize onboarding workflow
     */
    private function initializeOnboarding(int $tenantId): void
    {
        $steps = [
            ['step' => 'company_info', 'title' => 'Company Information', 'completed' => true],
            ['step' => 'branding', 'title' => 'Upload Logo & Branding', 'completed' => false],
            ['step' => 'payment', 'title' => 'Payment Setup', 'completed' => false],
            ['step' => 'products', 'title' => 'Add Products', 'completed' => false],
            ['step' => 'staff', 'title' => 'Invite Staff', 'completed' => false],
            ['step' => 'integration', 'title' => 'Connect Integrations', 'completed' => false]
        ];

        foreach ($steps as $index => $step) {
            TenantDatabase::insertTenant('onboarding_steps', array_merge($step, [
                'tenant_id' => $tenantId,
                'order' => $index + 1,
                'created_at' => date('Y-m-d H:i:s')
            ]));
        }
    }

    /**
     * Create demo data
     */
    private function createDemoData(int $tenantId): void
    {
        // Create sample customers
        $this->createDemoCustomers($tenantId, 10);

        // Create sample products
        $this->createDemoProducts($tenantId, 20);

        // Create sample transactions
        $this->createDemoTransactions($tenantId, 15);
    }

    /**
     * Create demo customers
     */
    private function createDemoCustomers(int $tenantId, int $count): void
    {
        $firstNames = ['John', 'Jane', 'Michael', 'Sarah', 'David', 'Emily', 'Robert', 'Lisa'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis'];

        for ($i = 0; $i < $count; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];

            TenantDatabase::insertTenant('customers', [
                'tenant_id' => $tenantId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName . '.' . $lastName . $i . '@example.com'),
                'phone' => '555-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
                'certification_level' => ['Open Water', 'Advanced', 'Rescue', 'Divemaster'][array_rand(['Open Water', 'Advanced', 'Rescue', 'Divemaster'])],
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 365) . ' days'))
            ]);
        }
    }

    /**
     * Create demo products
     */
    private function createDemoProducts(int $tenantId, int $count): void
    {
        $products = [
            ['name' => 'Regulator Set', 'price' => 599.99, 'category' => 'Regulators'],
            ['name' => 'BCD - Large', 'price' => 449.99, 'category' => 'BCDs'],
            ['name' => 'Dive Computer', 'price' => 299.99, 'category' => 'Dive Computers'],
            ['name' => 'Full Face Mask', 'price' => 89.99, 'category' => 'Masks & Fins'],
            ['name' => 'Wetsuit 3mm', 'price' => 199.99, 'category' => 'Wetsuits'],
            ['name' => 'Dive Light', 'price' => 79.99, 'category' => 'Accessories']
        ];

        $categories = TenantDatabase::fetchAllTenant(
            "SELECT id, name FROM categories WHERE tenant_id = ? AND type = 'product'",
            [$tenantId]
        ) ?? [];

        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat['name']] = $cat['id'];
        }

        foreach ($products as $product) {
            TenantDatabase::insertTenant('products', [
                'tenant_id' => $tenantId,
                'name' => $product['name'],
                'sku' => 'DEMO-' . strtoupper(substr(md5($product['name']), 0, 8)),
                'price' => $product['price'],
                'cost' => $product['price'] * 0.6,
                'category_id' => $categoryMap[$product['category']] ?? null,
                'stock_quantity' => rand(5, 50),
                'reorder_point' => 5,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Create demo transactions
     */
    private function createDemoTransactions(int $tenantId, int $count): void
    {
        $customers = TenantDatabase::fetchAllTenant(
            "SELECT id FROM customers WHERE tenant_id = ? LIMIT 10",
            [$tenantId]
        ) ?? [];

        $products = TenantDatabase::fetchAllTenant(
            "SELECT id, price FROM products WHERE tenant_id = ? LIMIT 20",
            [$tenantId]
        ) ?? [];

        if (empty($customers) || empty($products)) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $customer = $customers[array_rand($customers)];
            $itemCount = rand(1, 4);
            $subtotal = 0;

            $transactionId = TenantDatabase::insertTenant('transactions', [
                'tenant_id' => $tenantId,
                'customer_id' => $customer['id'],
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'payment_method' => ['cash', 'card', 'card'][array_rand(['cash', 'card', 'card'])],
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 90) . ' days'))
            ]);

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products[array_rand($products)];
                $quantity = rand(1, 3);
                $price = $product['price'];
                $itemSubtotal = $price * $quantity;
                $subtotal += $itemSubtotal;

                TenantDatabase::insertTenant('transaction_items', [
                    'transaction_id' => $transactionId,
                    'product_id' => $product['id'],
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $itemSubtotal
                ]);
            }

            $tax = $subtotal * 0.08;
            $total = $subtotal + $tax;

            TenantDatabase::updateTenant('transactions', [
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total
            ], 'id = ?', [$transactionId]);
        }
    }

    /**
     * Update onboarding step
     */
    public function completeOnboardingStep(int $tenantId, string $step): bool
    {
        TenantDatabase::updateTenant('onboarding_steps', [
            'completed' => true,
            'completed_at' => date('Y-m-d H:i:s')
        ], 'tenant_id = ? AND step = ?', [$tenantId, $step]);

        return true;
    }

    /**
     * Get onboarding progress
     */
    public function getOnboardingProgress(int $tenantId): array
    {
        $steps = TenantDatabase::fetchAllTenant(
            "SELECT * FROM onboarding_steps WHERE tenant_id = ? ORDER BY `order` ASC",
            [$tenantId]
        ) ?? [];

        $totalSteps = count($steps);
        $completedSteps = count(array_filter($steps, fn($s) => $s['completed']));

        return [
            'steps' => $steps,
            'total' => $totalSteps,
            'completed' => $completedSteps,
            'percentage' => $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100, 2) : 0
        ];
    }

    /**
     * Suspend tenant
     */
    public function suspendTenant(int $tenantId, string $reason): bool
    {
        $query = "UPDATE tenants SET status = 'suspended', suspended_reason = ?, suspended_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$reason, $tenantId]);
    }

    /**
     * Reactivate tenant
     */
    public function reactivateTenant(int $tenantId): bool
    {
        $query = "UPDATE tenants SET status = 'active', suspended_reason = NULL, suspended_at = NULL WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$tenantId]);
    }

    /**
     * Delete tenant (soft delete)
     */
    public function deleteTenant(int $tenantId): bool
    {
        $query = "UPDATE tenants SET status = 'deleted', deleted_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$tenantId]);
    }

    // Helper methods

    private function validateSubdomain(string $subdomain): bool
    {
        return preg_match('/^[a-z0-9][a-z0-9-]{1,61}[a-z0-9]$/', $subdomain);
    }

    private function subdomainExists(string $subdomain): bool
    {
        $query = "SELECT COUNT(*) as count FROM tenants WHERE subdomain = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$subdomain]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }

    private function sendWelcomeEmail(string $email, array $data): void
    {
        $this->emailService->send($email, 'Welcome to Nautilus', 'welcome', $data);
    }
}
