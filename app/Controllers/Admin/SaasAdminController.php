<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\Tenant\TenantProvisioningService;
use App\Services\Payment\SubscriptionBillingService;
use App\Services\Analytics\AdvancedAnalyticsService;
use App\Services\System\HealthCheckService;

/**
 * SaaS Administration Controller
 *
 * Platform-level administration for multi-tenant SaaS
 */
class SaasAdminController extends Controller
{
    private Database $db;
    private TenantProvisioningService $provisioningService;
    private SubscriptionBillingService $billingService;
    private AdvancedAnalyticsService $analyticsService;
    private HealthCheckService $healthCheckService;

    public function __construct()
    {
        parent::__construct();
        $this->db = new Database();
        $this->provisioningService = new TenantProvisioningService();
        $this->billingService = new SubscriptionBillingService();
        $this->analyticsService = new AdvancedAnalyticsService();
        $this->healthCheckService = new HealthCheckService();
    }

    /**
     * SaaS Dashboard
     */
    public function index(): void
    {
        $this->requirePlatformAdmin();

        $data = [
            'title' => 'SaaS Administration',
            'stats' => $this->getPlatformStats(),
            'recent_tenants' => $this->getRecentTenants(),
            'system_health' => $this->healthCheckService->checkHealth()
        ];

        $this->view('admin/saas/dashboard', $data);
    }

    /**
     * Tenants Management
     */
    public function tenants(): void
    {
        $this->requirePlatformAdmin();

        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $tenants = $this->db->query("
            SELECT
                t.*,
                sp.name as plan_name,
                COUNT(DISTINCT u.id) as user_count,
                ts.status as subscription_status
            FROM tenants t
            LEFT JOIN subscription_plans sp ON t.plan_id = sp.id
            LEFT JOIN users u ON t.id = u.tenant_id
            LEFT JOIN tenant_subscriptions ts ON t.id = ts.tenant_id AND ts.status = 'active'
            GROUP BY t.id
            ORDER BY t.created_at DESC
            LIMIT ? OFFSET ?
        ", [$limit, $offset])->fetchAll(\PDO::FETCH_ASSOC);

        $totalCount = $this->db->query("SELECT COUNT(*) as count FROM tenants")->fetch(\PDO::FETCH_ASSOC);

        $data = [
            'title' => 'Manage Tenants',
            'tenants' => $tenants,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalCount['count'] / $limit),
                'total_records' => $totalCount['count']
            ]
        ];

        $this->view('admin/saas/tenants', $data);
    }

    /**
     * Create New Tenant
     */
    public function createTenant(): void
    {
        $this->requirePlatformAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->provisioningService->provisionTenant($_POST);

            if ($result['success']) {
                $this->redirect('/admin/saas/tenants?success=Tenant created successfully');
            } else {
                $this->view('admin/saas/create_tenant', [
                    'title' => 'Create Tenant',
                    'error' => $result['error'],
                    'plans' => $this->getSubscriptionPlans()
                ]);
            }
        } else {
            $this->view('admin/saas/create_tenant', [
                'title' => 'Create Tenant',
                'plans' => $this->getSubscriptionPlans()
            ]);
        }
    }

    /**
     * View Tenant Details
     */
    public function viewTenant(int $tenantId): void
    {
        $this->requirePlatformAdmin();

        $tenant = $this->db->query(
            "SELECT * FROM tenants WHERE id = ?",
            [$tenantId]
        )->fetch(\PDO::FETCH_ASSOC);

        if (!$tenant) {
            $this->redirect('/admin/saas/tenants?error=Tenant not found');
            return;
        }

        $subscription = $this->billingService->getSubscriptionStatus($tenantId);
        $users = $this->getTenantUsers($tenantId);
        $usage = $this->getTenantUsage($tenantId);

        $data = [
            'title' => 'Tenant Details',
            'tenant' => $tenant,
            'subscription' => $subscription,
            'users' => $users,
            'usage' => $usage
        ];

        $this->view('admin/saas/tenant_details', $data);
    }

    /**
     * Suspend Tenant
     */
    public function suspendTenant(): void
    {
        $this->requirePlatformAdmin();

        $tenantId = $_POST['tenant_id'] ?? 0;
        $reason = $_POST['reason'] ?? 'Administrative action';

        $result = $this->provisioningService->suspendTenant($tenantId, $reason);

        echo json_encode(['success' => $result]);
    }

    /**
     * Reactivate Tenant
     */
    public function reactivateTenant(): void
    {
        $this->requirePlatformAdmin();

        $tenantId = $_POST['tenant_id'] ?? 0;

        $result = $this->provisioningService->reactivateTenant($tenantId);

        echo json_encode(['success' => $result]);
    }

    /**
     * Platform Analytics
     */
    public function analytics(): void
    {
        $this->requirePlatformAdmin();

        $period = $_GET['period'] ?? '30d';

        $data = [
            'title' => 'Platform Analytics',
            'revenue_metrics' => $this->getPlatformRevenue($period),
            'tenant_metrics' => $this->getTenantMetrics($period),
            'user_metrics' => $this->getUserMetrics($period),
            'churn_analysis' => $this->getChurnAnalysis()
        ];

        $this->view('admin/saas/analytics', $data);
    }

    /**
     * Subscription Plans Management
     */
    public function plans(): void
    {
        $this->requirePlatformAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->savePlan($_POST);
            $this->redirect('/admin/saas/plans?success=Plan saved');
            return;
        }

        $plans = $this->getSubscriptionPlans();

        $data = [
            'title' => 'Subscription Plans',
            'plans' => $plans
        ];

        $this->view('admin/saas/plans', $data);
    }

    /**
     * Billing Management
     */
    public function billing(): void
    {
        $this->requirePlatformAdmin();

        // Process recurring billing
        if (isset($_POST['process_billing'])) {
            $results = $this->billingService->processRecurringBilling();

            $data = [
                'title' => 'Billing Management',
                'billing_results' => $results,
                'invoices' => $this->getRecentInvoices()
            ];
        } else {
            $data = [
                'title' => 'Billing Management',
                'invoices' => $this->getRecentInvoices(),
                'due_subscriptions' => $this->getDueSubscriptions()
            ];
        }

        $this->view('admin/saas/billing', $data);
    }

    /**
     * System Health
     */
    public function health(): void
    {
        $this->requirePlatformAdmin();

        $health = $this->healthCheckService->checkHealth();
        $performance = $this->healthCheckService->getPerformanceMetrics();

        $data = [
            'title' => 'System Health',
            'health' => $health,
            'performance' => $performance
        ];

        $this->view('admin/saas/health', $data);
    }

    /**
     * API Health Check Endpoint (JSON)
     */
    public function apiHealth(): void
    {
        header('Content-Type: application/json');
        echo json_encode($this->healthCheckService->checkHealth());
    }

    /**
     * Liveness Probe
     */
    public function liveness(): void
    {
        header('Content-Type: application/json');
        echo json_encode($this->healthCheckService->liveness());
    }

    /**
     * Readiness Probe
     */
    public function readiness(): void
    {
        header('Content-Type: application/json');
        echo json_encode($this->healthCheckService->readiness());
    }

    // Private helper methods

    private function requirePlatformAdmin(): void
    {
        // Check if user is platform admin
        // This would integrate with your auth system
        if (!isset($_SESSION['is_platform_admin']) || !$_SESSION['is_platform_admin']) {
            http_response_code(403);
            die('Access denied');
        }
    }

    private function getPlatformStats(): array
    {
        $stats = $this->db->query("
            SELECT
                (SELECT COUNT(*) FROM tenants WHERE status = 'active') as active_tenants,
                (SELECT COUNT(*) FROM tenants WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_tenants_30d,
                (SELECT SUM(amount) FROM subscription_invoices WHERE status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as mrr,
                (SELECT COUNT(DISTINCT tenant_id) FROM users) as tenants_with_users
        ")->fetch(\PDO::FETCH_ASSOC);

        return $stats;
    }

    private function getRecentTenants(int $limit = 10): array
    {
        return $this->db->query("
            SELECT * FROM tenants ORDER BY created_at DESC LIMIT ?
        ", [$limit])->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getSubscriptionPlans(): array
    {
        return $this->db->query("
            SELECT * FROM subscription_plans ORDER BY amount ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getTenantUsers(int $tenantId): array
    {
        return $this->db->query("
            SELECT * FROM users WHERE tenant_id = ? ORDER BY created_at DESC LIMIT 20
        ", [$tenantId])->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getTenantUsage(int $tenantId): array
    {
        return [
            'storage_mb' => 0,
            'api_calls' => 0,
            'users' => 0
        ];
    }

    private function getPlatformRevenue(string $period): array
    {
        return [];
    }

    private function getTenantMetrics(string $period): array
    {
        return [];
    }

    private function getUserMetrics(string $period): array
    {
        return [];
    }

    private function getChurnAnalysis(): array
    {
        return [];
    }

    private function savePlan(array $data): void
    {
        if (isset($data['id'])) {
            $this->db->query("
                UPDATE subscription_plans
                SET name = ?, amount = ?, billing_period = ?, trial_days = ?, features = ?
                WHERE id = ?
            ", [
                $data['name'],
                $data['amount'],
                $data['billing_period'],
                $data['trial_days'] ?? 0,
                $data['features'] ?? '',
                $data['id']
            ]);
        } else {
            $this->db->query("
                INSERT INTO subscription_plans (name, amount, billing_period, trial_days, features, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
            ", [
                $data['name'],
                $data['amount'],
                $data['billing_period'],
                $data['trial_days'] ?? 0,
                $data['features'] ?? ''
            ]);
        }
    }

    private function getRecentInvoices(int $limit = 50): array
    {
        return $this->db->query("
            SELECT
                i.*,
                t.company_name as tenant_name
            FROM subscription_invoices i
            JOIN tenants t ON i.tenant_id = t.id
            ORDER BY i.created_at DESC
            LIMIT ?
        ", [$limit])->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getDueSubscriptions(): array
    {
        return $this->db->query("
            SELECT
                s.*,
                t.company_name,
                p.name as plan_name
            FROM tenant_subscriptions s
            JOIN tenants t ON s.tenant_id = t.id
            JOIN subscription_plans p ON s.plan_id = p.id
            WHERE s.status = 'active'
            AND s.current_period_end <= CURDATE()
            ORDER BY s.current_period_end ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);
    }
}
