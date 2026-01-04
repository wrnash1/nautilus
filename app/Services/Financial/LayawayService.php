<?php

namespace App\Services\Financial;

use PDO;

/**
 * Layaway Service
 * Manage layaway agreements and payment plans for equipment purchases
 */
class LayawayService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create layaway agreement
     */
    public function createLayawayAgreement(array $agreementData): array
    {
        // Get layaway plan details
        $stmt = $this->db->prepare("
            SELECT * FROM layaway_plans WHERE id = ? AND is_active = TRUE
        ");
        $stmt->execute([$agreementData['layaway_plan_id']]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {
            return [
                'success' => false,
                'error' => 'Invalid or inactive layaway plan'
            ];
        }

        // Calculate totals
        $totalAmount = $agreementData['total_amount'];

        // Verify minimum purchase
        if ($totalAmount < $plan['min_purchase_amount']) {
            return [
                'success' => false,
                'error' => "Minimum purchase amount is $" . $plan['min_purchase_amount']
            ];
        }

        // Calculate down payment
        $downPaymentPercentage = ($totalAmount * $plan['down_payment_percentage'] / 100);
        $downPayment = max($downPaymentPercentage, $plan['down_payment_minimum']);

        // Calculate layaway fee
        $layawayFee = $plan['layaway_fee_type'] === 'percentage' ?
            ($totalAmount * $plan['layaway_fee'] / 100) :
            $plan['layaway_fee'];

        $totalDue = $totalAmount + $layawayFee;
        $balanceRemaining = $totalDue - $downPayment;

        // Calculate payment amount
        $paymentAmount = round($balanceRemaining / $plan['number_of_payments'], 2);

        // Generate agreement number
        $agreementNumber = $this->generateAgreementNumber($agreementData['tenant_id']);

        // Calculate payment dates
        $firstPaymentDate = $this->calculateNextPaymentDate(
            date('Y-m-d'),
            $plan['payment_frequency']
        );
        $finalPaymentDate = $this->calculateFinalPaymentDate(
            $firstPaymentDate,
            $plan['payment_frequency'],
            $plan['number_of_payments'] - 1
        );

        // Create agreement
        $stmt = $this->db->prepare("
            INSERT INTO layaway_agreements (
                tenant_id, agreement_number, layaway_plan_id, customer_id,
                items, total_amount, down_payment, layaway_fee, total_due,
                number_of_payments, payment_amount, payment_frequency,
                first_payment_date, final_payment_date, balance_remaining,
                created_by, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");

        $stmt->execute([
            $agreementData['tenant_id'],
            $agreementNumber,
            $agreementData['layaway_plan_id'],
            $agreementData['customer_id'],
            json_encode($agreementData['items']),
            $totalAmount,
            $downPayment,
            $layawayFee,
            $totalDue,
            $plan['number_of_payments'],
            $paymentAmount,
            $plan['payment_frequency'],
            $firstPaymentDate,
            $finalPaymentDate,
            $balanceRemaining,
            $agreementData['created_by'] ?? null
        ]);

        $agreementId = $this->db->lastInsertId();

        // Create payment schedule
        $this->createPaymentSchedule($agreementId, $agreementData['tenant_id'], [
            'first_payment_date' => $firstPaymentDate,
            'payment_amount' => $paymentAmount,
            'number_of_payments' => $plan['number_of_payments'],
            'payment_frequency' => $plan['payment_frequency']
        ]);

        // Reserve products if requested
        if ($agreementData['reserve_products'] ?? true) {
            $this->reserveProducts($agreementId, $agreementData['items']);
        }

        return [
            'success' => true,
            'agreement_id' => $agreementId,
            'agreement_number' => $agreementNumber,
            'down_payment' => $downPayment,
            'layaway_fee' => $layawayFee,
            'total_due' => $totalDue,
            'payment_amount' => $paymentAmount,
            'first_payment_date' => $firstPaymentDate,
            'final_payment_date' => $finalPaymentDate
        ];
    }

    /**
     * Record layaway payment
     */
    public function recordPayment(int $agreementId, float $amount, int $paymentId): array
    {
        // Get agreement
        $stmt = $this->db->prepare("
            SELECT * FROM layaway_agreements WHERE id = ?
        ");
        $stmt->execute([$agreementId]);
        $agreement = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$agreement) {
            return [
                'success' => false,
                'error' => 'Agreement not found'
            ];
        }

        // Update agreement
        $newAmountPaid = $agreement['amount_paid'] + $amount;
        $newBalance = $agreement['balance_remaining'] - $amount;
        $newPaymentsMade = $agreement['payments_made'] + 1;

        // Determine new status
        $newStatus = $agreement['status'];
        if ($newBalance <= 0) {
            $newStatus = 'completed';
        } elseif ($newStatus === 'pending') {
            $newStatus = 'active';
        }

        $this->db->prepare("
            UPDATE layaway_agreements
            SET amount_paid = ?,
                balance_remaining = ?,
                payments_made = ?,
                status = ?
            WHERE id = ?
        ")->execute([
            $newAmountPaid,
            $newBalance,
            $newPaymentsMade,
            $newStatus,
            $agreementId
        ]);

        // Update payment schedule
        $this->updatePaymentSchedule($agreementId, $amount, $paymentId);

        // If completed, release products
        if ($newStatus === 'completed') {
            $this->completeLayaway($agreementId);
        }

        return [
            'success' => true,
            'amount_paid' => $newAmountPaid,
            'balance_remaining' => $newBalance,
            'status' => $newStatus,
            'completed' => $newStatus === 'completed'
        ];
    }

    /**
     * Cancel layaway agreement
     */
    public function cancelAgreement(int $agreementId, string $reason, bool $processRefund = true): array
    {
        // Get agreement
        $stmt = $this->db->prepare("
            SELECT * FROM layaway_agreements WHERE id = ?
        ");
        $stmt->execute([$agreementId]);
        $agreement = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$agreement) {
            return [
                'success' => false,
                'error' => 'Agreement not found'
            ];
        }

        // Get layaway plan for fees
        $stmt = $this->db->prepare("
            SELECT * FROM layaway_plans WHERE id = ?
        ");
        $stmt->execute([$agreement['layaway_plan_id']]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate refund
        $refundAmount = 0;
        if ($processRefund) {
            $cancellationFee = $plan['cancellation_fee'] ?? 0;
            $restockingFee = ($agreement['total_amount'] * ($plan['restocking_fee_percentage'] / 100));
            $totalFees = $cancellationFee + $restockingFee;
            $refundAmount = max(0, $agreement['amount_paid'] - $totalFees);
        }

        // Update agreement
        $this->db->prepare("
            UPDATE layaway_agreements
            SET status = 'cancelled',
                cancelled_at = NOW(),
                cancellation_reason = ?,
                refund_amount = ?
            WHERE id = ?
        ")->execute([$reason, $refundAmount, $agreementId]);

        // Release reserved products
        $this->releaseProducts($agreementId);

        return [
            'success' => true,
            'refund_amount' => $refundAmount,
            'message' => 'Layaway agreement cancelled'
        ];
    }

    /**
     * Get customer's layaway agreements
     */
    public function getCustomerLayaways(int $customerId, int $tenantId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                la.*,
                lp.plan_name
            FROM layaway_agreements la
            JOIN layaway_plans lp ON la.layaway_plan_id = lp.id
            WHERE la.customer_id = ?
              AND la.tenant_id = ?
            ORDER BY la.created_at DESC
        ");
        $stmt->execute([$customerId, $tenantId]);
        $agreements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'agreements' => $agreements,
            'count' => count($agreements)
        ];
    }

    /**
     * Get upcoming payments due
     */
    public function getUpcomingPayments(int $tenantId, int $daysAhead = 7): array
    {
        $stmt = $this->db->prepare("
            SELECT
                lps.*,
                la.agreement_number,
                la.customer_id,
                c.first_name,
                c.last_name,
                c.email,
                c.phone
            FROM layaway_payment_schedules lps
            JOIN layaway_agreements la ON lps.agreement_id = la.id
            JOIN customers c ON la.customer_id = c.id
            WHERE la.tenant_id = ?
              AND lps.payment_status IN ('pending', 'late')
              AND lps.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY lps.due_date ASC
        ");
        $stmt->execute([$tenantId, $daysAhead]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'upcoming_payments' => $payments,
            'count' => count($payments)
        ];
    }

    /**
     * Create payment schedule
     */
    private function createPaymentSchedule(int $agreementId, int $tenantId, array $scheduleData): void
    {
        $currentDate = $scheduleData['first_payment_date'];

        for ($i = 1; $i <= $scheduleData['number_of_payments']; $i++) {
            $this->db->prepare("
                INSERT INTO layaway_payment_schedules (
                    tenant_id, agreement_id, payment_number, due_date, amount_due
                ) VALUES (?, ?, ?, ?, ?)
            ")->execute([
                $tenantId,
                $agreementId,
                $i,
                $currentDate,
                $scheduleData['payment_amount']
            ]);

            // Calculate next payment date
            if ($i < $scheduleData['number_of_payments']) {
                $currentDate = $this->calculateNextPaymentDate(
                    $currentDate,
                    $scheduleData['payment_frequency']
                );
            }
        }
    }

    /**
     * Update payment schedule when payment received
     */
    private function updatePaymentSchedule(int $agreementId, float $amount, int $paymentId): void
    {
        // Find next unpaid payment
        $stmt = $this->db->prepare("
            SELECT * FROM layaway_payment_schedules
            WHERE agreement_id = ?
              AND payment_status IN ('pending', 'late', 'partial')
            ORDER BY payment_number ASC
            LIMIT 1
        ");
        $stmt->execute([$agreementId]);
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($schedule) {
            $newAmountPaid = $schedule['amount_paid'] + $amount;
            $status = $newAmountPaid >= $schedule['amount_due'] ? 'paid' : 'partial';

            $this->db->prepare("
                UPDATE layaway_payment_schedules
                SET amount_paid = ?,
                    payment_status = ?,
                    paid_date = CURDATE(),
                    payment_id = ?
                WHERE id = ?
            ")->execute([
                $newAmountPaid,
                $status,
                $paymentId,
                $schedule['id']
            ]);
        }
    }

    /**
     * Calculate next payment date
     */
    private function calculateNextPaymentDate(string $currentDate, string $frequency): string
    {
        switch ($frequency) {
            case 'weekly':
                return date('Y-m-d', strtotime($currentDate . ' +1 week'));
            case 'biweekly':
                return date('Y-m-d', strtotime($currentDate . ' +2 weeks'));
            case 'monthly':
            default:
                return date('Y-m-d', strtotime($currentDate . ' +1 month'));
        }
    }

    /**
     * Calculate final payment date
     */
    private function calculateFinalPaymentDate(string $startDate, string $frequency, int $paymentsRemaining): string
    {
        $date = $startDate;
        for ($i = 0; $i < $paymentsRemaining; $i++) {
            $date = $this->calculateNextPaymentDate($date, $frequency);
        }
        return $date;
    }

    /**
     * Generate unique agreement number
     */
    private function generateAgreementNumber(int $tenantId): string
    {
        return 'LAY-' . $tenantId . '-' . strtoupper(substr(uniqid(), -8));
    }

    /**
     * Reserve products for layaway
     */
    private function reserveProducts(int $agreementId, array $items): void
    {
        // In production, would mark serialized inventory as reserved
        // and update stock levels
    }

    /**
     * Release reserved products
     */
    private function releaseProducts(int $agreementId): void
    {
        // In production, would unmark reserved inventory
        // and update stock levels
    }

    /**
     * Complete layaway and release products to customer
     */
    private function completeLayaway(int $agreementId): void
    {
        $this->db->prepare("
            UPDATE layaway_agreements
            SET completed_at = NOW(),
                products_reserved = FALSE
            WHERE id = ?
        ")->execute([$agreementId]);

        // In production, would create invoice for customer
        // and update inventory to mark products as sold
    }
}
