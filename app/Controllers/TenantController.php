<?php

namespace App\Controllers;

use App\Services\Tenant\TenantService;
use App\Middleware\TenantMiddleware;
use App\Core\Database;

/**
 * Tenant Controller
 *
 * Handles tenant registration, onboarding, and management
 */
class TenantController
{
    private TenantService $tenantService;

    public function __construct()
    {
        $this->tenantService = new TenantService();
    }

    /**
     * Show tenant registration form
     */
    public function showRegistration(): void
    {
        $plans = $this->tenantService->getAllPlans();

        include __DIR__ . '/../../public/views/tenant/register.php';
    }

    /**
     * Process tenant registration
     */
    public function register(): void
    {
        try {
            // Validate input
            $errors = $this->validateRegistration($_POST);

            if (!empty($errors)) {
                $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
                return;
            }

            // Create tenant
            $result = $this->tenantService->createTenant([
                'company_name' => $_POST['company_name'],
                'subdomain' => $_POST['subdomain'] ?? null,
                'contact_name' => $_POST['contact_name'],
                'contact_email' => $_POST['contact_email'],
                'contact_phone' => $_POST['contact_phone'] ?? null,
                'plan_id' => (int)$_POST['plan_id'],
                'address_line1' => $_POST['address_line1'] ?? null,
                'city' => $_POST['city'] ?? null,
                'state' => $_POST['state'] ?? null,
                'postal_code' => $_POST['postal_code'] ?? null,
                'country' => $_POST['country'] ?? 'US',
                'timezone' => $_POST['timezone'] ?? 'UTC',
                'locale' => $_POST['locale'] ?? 'en_US',
                'currency' => $_POST['currency'] ?? 'USD'
            ]);

            if (!$result['success']) {
                $this->jsonResponse(['success' => false, 'error' => $result['error']], 400);
                return;
            }

            // Create admin user for the tenant
            $userId = $this->createTenantAdmin($result['tenant_id'], [
                'username' => $_POST['username'],
                'email' => $_POST['contact_email'],
                'password' => $_POST['password'],
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name']
            ]);

            // Send welcome email
            $this->sendWelcomeEmail($result['tenant_id'], $_POST['contact_email']);

            $this->jsonResponse([
                'success' => true,
                'tenant_id' => $result['tenant_id'],
                'subdomain' => $result['subdomain'],
                'redirect_url' => 'https://' . $result['subdomain'] . '.' . ($_ENV['BASE_DOMAIN'] ?? 'nautilus.local')
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Tenant dashboard
     */
    public function dashboard(): void
    {
        $tenantId = TenantMiddleware::getCurrentTenantId();

        if (!$tenantId) {
            header('Location: /login');
            exit;
        }

        $tenant = $this->tenantService->getTenantById($tenantId);
        $plan = $this->tenantService->getSubscriptionPlan($tenant['plan_id']);

        // Get usage stats
        $usage = $this->getUsageStats($tenantId);

        // Get onboarding progress
        $onboarding = $this->getOnboardingProgress($tenantId);

        include __DIR__ . '/../../public/views/tenant/dashboard.php';
    }

    /**
     * Update tenant settings
     */
    public function updateSettings(): void
    {
        try {
            $tenantId = TenantMiddleware::getCurrentTenantId();

            if (!$tenantId) {
                $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
                return;
            }

            $success = $this->tenantService->updateTenant($tenantId, $_POST);

            $this->jsonResponse(['success' => $success]);

        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Upgrade subscription plan
     */
    public function upgradePlan(): void
    {
        try {
            $tenantId = TenantMiddleware::getCurrentTenantId();

            if (!$tenantId) {
                $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
                return;
            }

            $planId = (int)$_POST['plan_id'];

            $success = $this->tenantService->updateSubscription($tenantId, $planId);

            if ($success) {
                // Log activity
                $this->tenantService->logActivity(
                    $tenantId,
                    $_SESSION['user_id'] ?? null,
                    'subscription_upgraded',
                    'subscription',
                    $planId,
                    'Subscription plan upgraded'
                );
            }

            $this->jsonResponse(['success' => $success]);

        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get usage statistics
     */
    public function getUsage(): void
    {
        try {
            $tenantId = TenantMiddleware::getCurrentTenantId();

            if (!$tenantId) {
                $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
                return;
            }

            $usage = $this->getUsageStats($tenantId);

            $this->jsonResponse(['success' => true, 'data' => $usage]);

        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update onboarding step
     */
    public function updateOnboarding(): void
    {
        try {
            $tenantId = TenantMiddleware::getCurrentTenantId();

            if (!$tenantId) {
                $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
                return;
            }

            $step = $_POST['step'] ?? '';

            $stepColumns = [
                'company_info' => 'step_company_info',
                'users_invited' => 'step_users_invited',
                'products_added' => 'step_products_added',
                'payment_setup' => 'step_payment_setup',
                'customization' => 'step_customization'
            ];

            if (!isset($stepColumns[$step])) {
                $this->jsonResponse(['success' => false, 'error' => 'Invalid step'], 400);
                return;
            }

            Database::query(
                "UPDATE tenant_onboarding SET {$stepColumns[$step]} = 1 WHERE tenant_id = ?",
                [$tenantId]
            );

            // Calculate completion percentage
            $this->updateOnboardingProgress($tenantId);

            $this->jsonResponse(['success' => true]);

        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validate registration input
     */
    private function validateRegistration(array $data): array
    {
        $errors = [];

        if (empty($data['company_name'])) {
            $errors[] = 'Company name is required';
        }

        if (empty($data['contact_email']) || !filter_var($data['contact_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid contact email is required';
        }

        if (empty($data['username']) || strlen($data['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        if (empty($data['first_name']) || empty($data['last_name'])) {
            $errors[] = 'First and last name are required';
        }

        if (empty($data['plan_id'])) {
            $errors[] = 'Please select a subscription plan';
        }

        return $errors;
    }

    /**
     * Create admin user for tenant
     */
    private function createTenantAdmin(int $tenantId, array $data): int
    {
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

        Database::query(
            "INSERT INTO users (
                tenant_id, username, email, password_hash,
                first_name, last_name, role, is_active, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'admin', 1, NOW(), NOW())",
            [
                $tenantId,
                $data['username'],
                $data['email'],
                $passwordHash,
                $data['first_name'],
                $data['last_name']
            ]
        );

        $userId = Database::lastInsertId();

        // Link user to tenant as owner
        Database::query(
            "INSERT INTO tenant_users (tenant_id, user_id, role, is_owner)
             VALUES (?, ?, 'admin', 1)",
            [$tenantId, $userId]
        );

        return $userId;
    }

    /**
     * Get usage statistics for tenant
     */
    private function getUsageStats(int $tenantId): array
    {
        $tenant = $this->tenantService->getTenantById($tenantId);

        // Get current counts
        $userCount = Database::fetchOne(
            "SELECT COUNT(*) as count FROM users WHERE tenant_id = ? AND is_active = 1",
            [$tenantId]
        )['count'] ?? 0;

        $productCount = Database::fetchOne(
            "SELECT COUNT(*) as count FROM products WHERE tenant_id = ? AND is_active = 1",
            [$tenantId]
        )['count'] ?? 0;

        $transactionCount = Database::fetchOne(
            "SELECT COUNT(*) as count FROM pos_transactions
             WHERE tenant_id = ? AND created_at >= ?",
            [$tenantId, date('Y-m-01')]
        )['count'] ?? 0;

        return [
            'users' => [
                'current' => $userCount,
                'limit' => $tenant['max_users'],
                'percentage' => ($userCount / $tenant['max_users']) * 100
            ],
            'products' => [
                'current' => $productCount,
                'limit' => $tenant['max_products'],
                'percentage' => ($productCount / $tenant['max_products']) * 100
            ],
            'transactions' => [
                'current' => $transactionCount,
                'limit' => $tenant['max_transactions_per_month'],
                'percentage' => ($transactionCount / $tenant['max_transactions_per_month']) * 100
            ],
            'storage' => [
                'current' => 0, // TODO: Calculate actual storage
                'limit' => $tenant['max_storage_mb'],
                'percentage' => 0
            ]
        ];
    }

    /**
     * Get onboarding progress
     */
    private function getOnboardingProgress(int $tenantId): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM tenant_onboarding WHERE tenant_id = ?",
            [$tenantId]
        );
    }

    /**
     * Update onboarding progress percentage
     */
    private function updateOnboardingProgress(int $tenantId): void
    {
        $onboarding = $this->getOnboardingProgress($tenantId);

        if (!$onboarding) {
            return;
        }

        $steps = [
            'step_company_info',
            'step_users_invited',
            'step_products_added',
            'step_payment_setup',
            'step_customization'
        ];

        $completed = 0;
        foreach ($steps as $step) {
            if ($onboarding[$step]) {
                $completed++;
            }
        }

        $percentage = (int)(($completed / count($steps)) * 100);
        $allCompleted = $completed === count($steps);

        Database::query(
            "UPDATE tenant_onboarding SET
                completion_percentage = ?,
                step_completed = ?,
                completed_at = ?
             WHERE tenant_id = ?",
            [
                $percentage,
                $allCompleted ? 1 : 0,
                $allCompleted ? date('Y-m-d H:i:s') : null,
                $tenantId
            ]
        );
    }

    /**
     * Send welcome email
     */
    private function sendWelcomeEmail(int $tenantId, string $email): void
    {
        // TODO: Implement email sending
        // This would use the EmailService to send a welcome email
    }

    /**
     * JSON response helper
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
