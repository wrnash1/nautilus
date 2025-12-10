<?php

namespace App\Services\Payment;

use App\Core\TenantDatabase;
use App\Services\Email\EmailService;
use App\Services\Audit\AuditService;

/**
 * Subscription Billing and Usage Metering Service
 *
 * Features:
 * - Flexible subscription plans
 * - Usage-based billing
 * - Metered billing
 * - Proration
 * - Trial periods
 * - Add-ons and upgrades
 * - Automatic payment retry
 * - Dunning management
 */
class SubscriptionBillingService
{
    private EmailService $emailService;
    private AuditService $auditService;
    private PaymentGatewayService $paymentGateway;

    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->auditService = new AuditService();
        $this->paymentGateway = new PaymentGatewayService();
    }

    /**
     * Create subscription for tenant
     */
    public function createSubscription(int $tenantId, int $planId, array $paymentMethod): array
    {
        $plan = TenantDatabase::fetchOneTenant(
            "SELECT * FROM subscription_plans WHERE id = ?",
            [$planId]
        );

        if (!$plan) {
            return ['success' => false, 'error' => 'Plan not found'];
        }

        // Calculate trial end date
        $trialEndDate = null;
        if ($plan['trial_days'] > 0) {
            $trialEndDate = date('Y-m-d', strtotime("+{$plan['trial_days']} days"));
        }

        // Calculate next billing date
        $nextBillingDate = $trialEndDate ?? date('Y-m-d', strtotime("+1 {$plan['billing_period']}"));

        // Create subscription
        $subscriptionId = TenantDatabase::insertTenant('tenant_subscriptions', [
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => 'active',
            'current_period_start' => date('Y-m-d'),
            'current_period_end' => $nextBillingDate,
            'trial_end' => $trialEndDate,
            'quantity' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Store payment method
        TenantDatabase::insertTenant('payment_methods', [
            'tenant_id' => $tenantId,
            'type' => $paymentMethod['type'],
            'last_four' => $paymentMethod['last_four'],
            'exp_month' => $paymentMethod['exp_month'] ?? null,
            'exp_year' => $paymentMethod['exp_year'] ?? null,
            'gateway_token' => $paymentMethod['gateway_token'],
            'is_default' => true,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Log subscription creation
        $this->auditService->log('subscription', 'created', null, [
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'subscription_id' => $subscriptionId
        ]);

        // Send confirmation email
        $this->sendSubscriptionEmail($tenantId, 'subscription_created', $plan);

        return [
            'success' => true,
            'subscription_id' => $subscriptionId,
            'trial_end' => $trialEndDate,
            'next_billing_date' => $nextBillingDate
        ];
    }

    /**
     * Process recurring billing
     */
    public function processRecurringBilling(): array
    {
        // Get subscriptions due for billing
        $dueSubscriptions = TenantDatabase::fetchAllTenant("
            SELECT s.*, p.amount, p.name as plan_name, p.billing_period
            FROM tenant_subscriptions s
            JOIN subscription_plans p ON s.plan_id = p.id
            WHERE s.status = 'active'
            AND s.current_period_end <= CURDATE()
            AND (s.trial_end IS NULL OR s.trial_end < CURDATE())
        ") ?? [];

        $results = [
            'processed' => 0,
            'succeeded' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($dueSubscriptions as $subscription) {
            $results['processed']++;

            try {
                $result = $this->billSubscription($subscription);

                if ($result['success']) {
                    $results['succeeded']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'tenant_id' => $subscription['tenant_id'],
                        'error' => $result['error']
                    ];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'tenant_id' => $subscription['tenant_id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Bill a single subscription
     */
    private function billSubscription(array $subscription): array
    {
        // Get payment method
        $paymentMethod = TenantDatabase::fetchOneTenant(
            "SELECT * FROM payment_methods WHERE tenant_id = ? AND is_default = 1",
            [$subscription['tenant_id']]
        );

        if (!$paymentMethod) {
            $this->handleFailedPayment($subscription['id'], 'No payment method');
            return ['success' => false, 'error' => 'No payment method'];
        }

        // Calculate amount including usage charges
        $amount = $this->calculateSubscriptionAmount($subscription);

        // Process payment
        $paymentResult = $this->paymentGateway->processPayment('stripe', [
            'amount' => $amount,
            'currency' => 'USD',
            'payment_method' => $paymentMethod['gateway_token'],
            'description' => "Subscription: {$subscription['plan_name']}"
        ]);

        if ($paymentResult['success']) {
            // Record invoice
            $invoiceId = TenantDatabase::insertTenant('subscription_invoices', [
                'tenant_id' => $subscription['tenant_id'],
                'subscription_id' => $subscription['id'],
                'amount' => $amount,
                'status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
                'period_start' => $subscription['current_period_start'],
                'period_end' => $subscription['current_period_end'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Update subscription period
            $nextPeriodEnd = date('Y-m-d', strtotime($subscription['current_period_end'] . " +1 {$subscription['billing_period']}"));

            TenantDatabase::updateTenant('tenant_subscriptions', [
                'current_period_start' => $subscription['current_period_end'],
                'current_period_end' => $nextPeriodEnd,
                'last_billing_date' => date('Y-m-d'),
                'failed_payment_count' => 0
            ], 'id = ?', [$subscription['id']]);

            // Reset usage meters for the new period
            $this->resetUsageMeters($subscription['id']);

            // Send receipt
            $this->sendSubscriptionEmail($subscription['tenant_id'], 'payment_successful', [
                'amount' => $amount,
                'invoice_id' => $invoiceId
            ]);

            return ['success' => true, 'invoice_id' => $invoiceId];
        } else {
            $this->handleFailedPayment($subscription['id'], $paymentResult['error']);
            return ['success' => false, 'error' => $paymentResult['error']];
        }
    }

    /**
     * Calculate subscription amount including metered usage
     */
    private function calculateSubscriptionAmount(array $subscription): float
    {
        $baseAmount = $subscription['amount'] * $subscription['quantity'];

        // Get metered usage charges
        $usageCharges = $this->calculateUsageCharges($subscription['id']);

        return $baseAmount + $usageCharges;
    }

    /**
     * Calculate usage charges
     */
    private function calculateUsageCharges(int $subscriptionId): float
    {
        $totalCharges = 0;

        $usage = TenantDatabase::fetchAllTenant("
            SELECT um.*, spm.price_per_unit
            FROM usage_meters um
            JOIN subscription_plan_meters spm ON um.meter_type = spm.meter_type
            WHERE um.subscription_id = ?
            AND um.billed = 0
        ", [$subscriptionId]) ?? [];

        foreach ($usage as $meter) {
            $charges = $meter['quantity'] * $meter['price_per_unit'];
            $totalCharges += $charges;

            // Mark as billed
            TenantDatabase::updateTenant('usage_meters', [
                'billed' => 1,
                'billed_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$meter['id']]);
        }

        return $totalCharges;
    }

    /**
     * Record usage
     */
    public function recordUsage(int $tenantId, string $meterType, float $quantity, array $metadata = []): bool
    {
        $subscription = TenantDatabase::fetchOneTenant(
            "SELECT id FROM tenant_subscriptions WHERE tenant_id = ? AND status = 'active'",
            [$tenantId]
        );

        if (!$subscription) {
            return false;
        }

        TenantDatabase::insertTenant('usage_meters', [
            'subscription_id' => $subscription['id'],
            'tenant_id' => $tenantId,
            'meter_type' => $meterType,
            'quantity' => $quantity,
            'metadata' => json_encode($metadata),
            'recorded_at' => date('Y-m-d H:i:s'),
            'billed' => 0
        ]);

        return true;
    }

    /**
     * Upgrade subscription
     */
    public function upgradeSubscription(int $tenantId, int $newPlanId): array
    {
        $currentSubscription = TenantDatabase::fetchOneTenant(
            "SELECT * FROM tenant_subscriptions WHERE tenant_id = ? AND status = 'active'",
            [$tenantId]
        );

        if (!$currentSubscription) {
            return ['success' => false, 'error' => 'No active subscription'];
        }

        $newPlan = TenantDatabase::fetchOneTenant(
            "SELECT * FROM subscription_plans WHERE id = ?",
            [$newPlanId]
        );

        if (!$newPlan) {
            return ['success' => false, 'error' => 'Plan not found'];
        }

        // Calculate proration
        $proration = $this->calculateProration($currentSubscription, $newPlan);

        // Update subscription
        TenantDatabase::updateTenant('tenant_subscriptions', [
            'plan_id' => $newPlanId,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$currentSubscription['id']]);

        // Charge proration if applicable
        if ($proration > 0) {
            $this->chargeProration($tenantId, $proration);
        }

        $this->auditService->log('subscription', 'upgraded', null, [
            'tenant_id' => $tenantId,
            'old_plan_id' => $currentSubscription['plan_id'],
            'new_plan_id' => $newPlanId,
            'proration' => $proration
        ]);

        return [
            'success' => true,
            'proration' => $proration,
            'new_plan' => $newPlan
        ];
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(int $tenantId, bool $immediate = false): bool
    {
        $subscription = TenantDatabase::fetchOneTenant(
            "SELECT * FROM tenant_subscriptions WHERE tenant_id = ? AND status = 'active'",
            [$tenantId]
        );

        if (!$subscription) {
            return false;
        }

        if ($immediate) {
            TenantDatabase::updateTenant('tenant_subscriptions', [
                'status' => 'canceled',
                'canceled_at' => date('Y-m-d H:i:s'),
                'ends_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$subscription['id']]);
        } else {
            // Cancel at end of current period
            TenantDatabase::updateTenant('tenant_subscriptions', [
                'cancel_at_period_end' => 1,
                'ends_at' => $subscription['current_period_end']
            ], 'id = ?', [$subscription['id']]);
        }

        $this->sendSubscriptionEmail($tenantId, 'subscription_canceled', $subscription);

        return true;
    }

    /**
     * Handle failed payment
     */
    private function handleFailedPayment(int $subscriptionId, string $error): void
    {
        $subscription = TenantDatabase::fetchOneTenant(
            "SELECT * FROM tenant_subscriptions WHERE id = ?",
            [$subscriptionId]
        );

        $failedCount = ($subscription['failed_payment_count'] ?? 0) + 1;

        TenantDatabase::updateTenant('tenant_subscriptions', [
            'failed_payment_count' => $failedCount,
            'last_payment_error' => $error
        ], 'id = ?', [$subscriptionId]);

        // Send dunning email
        $this->sendSubscriptionEmail($subscription['tenant_id'], 'payment_failed', [
            'error' => $error,
            'attempt' => $failedCount
        ]);

        // Suspend after 3 failed attempts
        if ($failedCount >= 3) {
            TenantDatabase::updateTenant('tenant_subscriptions', [
                'status' => 'past_due'
            ], 'id = ?', [$subscriptionId]);

            $this->sendSubscriptionEmail($subscription['tenant_id'], 'subscription_suspended', []);
        }
    }

    /**
     * Calculate proration
     */
    private function calculateProration(array $currentSubscription, array $newPlan): float
    {
        $daysRemaining = (strtotime($currentSubscription['current_period_end']) - time()) / 86400;
        $totalDays = (strtotime($currentSubscription['current_period_end']) - strtotime($currentSubscription['current_period_start'])) / 86400;

        $unusedAmount = ($currentSubscription['amount'] / $totalDays) * $daysRemaining;
        $newAmount = ($newPlan['amount'] / $totalDays) * $daysRemaining;

        return max(0, $newAmount - $unusedAmount);
    }

    /**
     * Charge proration
     */
    private function chargeProration(int $tenantId, float $amount): void
    {
        $paymentMethod = TenantDatabase::fetchOneTenant(
            "SELECT * FROM payment_methods WHERE tenant_id = ? AND is_default = 1",
            [$tenantId]
        );

        if ($paymentMethod) {
            $this->paymentGateway->processPayment('stripe', [
                'amount' => $amount,
                'currency' => 'USD',
                'payment_method' => $paymentMethod['gateway_token'],
                'description' => 'Proration charge'
            ]);
        }
    }

    /**
     * Reset usage meters for new billing period
     */
    private function resetUsageMeters(int $subscriptionId): void
    {
        // Archive current period usage
        $usage = TenantDatabase::fetchAllTenant(
            "SELECT * FROM usage_meters WHERE subscription_id = ? AND billed = 1",
            [$subscriptionId]
        ) ?? [];

        foreach ($usage as $meter) {
            TenantDatabase::insertTenant('usage_meter_archive', array_merge($meter, [
                'archived_at' => date('Y-m-d H:i:s')
            ]));
        }

        // Delete billed meters
        TenantDatabase::deleteTenant('usage_meters', 'subscription_id = ? AND billed = 1', [$subscriptionId]);
    }

    /**
     * Get subscription status
     */
    public function getSubscriptionStatus(int $tenantId): ?array
    {
        $subscription = TenantDatabase::fetchOneTenant("
            SELECT s.*, p.name as plan_name, p.amount as plan_amount, p.billing_period
            FROM tenant_subscriptions s
            JOIN subscription_plans p ON s.plan_id = p.id
            WHERE s.tenant_id = ?
            ORDER BY s.created_at DESC
            LIMIT 1
        ", [$tenantId]);

        if ($subscription) {
            // Get current usage
            $usage = TenantDatabase::fetchAllTenant(
                "SELECT meter_type, SUM(quantity) as total_quantity
                 FROM usage_meters
                 WHERE subscription_id = ? AND billed = 0
                 GROUP BY meter_type",
                [$subscription['id']]
            ) ?? [];

            $subscription['current_usage'] = $usage;
        }

        return $subscription;
    }

    /**
     * Send subscription email
     */
    private function sendSubscriptionEmail(int $tenantId, string $type, array $data): void
    {
        // Implementation would use EmailService
    }

    /**
     * Get available plans
     */
    public function getAvailablePlans(): array
    {
        return TenantDatabase::fetchAllTenant(
            "SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY amount ASC"
        ) ?? [];
    }
}
