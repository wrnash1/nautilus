<?php

namespace App\Services\POS;

use App\Core\Database;
use App\Services\Email\EmailService;
use App\Core\Logger;

/**
 * Layaway Service
 * Handles layaway transactions, payments, and inventory management
 */
class LayawayService
{
    private EmailService $emailService;
    private Logger $logger;

    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->logger = new Logger();
    }

    /**
     * Get layaway settings
     */
    public function getSettings(): array
    {
        $settings = Database::fetchOne("SELECT * FROM layaway_settings LIMIT 1");
        return $settings ?? [];
    }

    /**
     * Create a new layaway transaction
     */
    public function createLayaway(array $data): int
    {
        try {
            $settings = $this->getSettings();

            // Generate layaway number
            $layawayNumber = $this->generateLayawayNumber();

            // Calculate amounts
            $totalAmount = $data['total_amount'];
            $depositAmount = $data['deposit_amount'];
            $taxAmount = $data['tax_amount'] ?? 0;
            $balanceDue = $totalAmount - $depositAmount;

            // Calculate payment schedule
            $paymentAmount = $this->calculatePaymentAmount(
                $balanceDue,
                $data['payment_schedule'] ?? $settings['default_payment_schedule'],
                $data['start_date'],
                $data['due_date']
            );

            // Create layaway record
            Database::execute(
                "INSERT INTO layaway
                (layaway_number, customer_id, total_amount, deposit_amount, amount_paid,
                 balance_due, tax_amount, payment_schedule, payment_amount, start_date,
                 due_date, status, notes, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, NOW())",
                [
                    $layawayNumber,
                    $data['customer_id'],
                    $totalAmount,
                    $depositAmount,
                    $depositAmount, // Initial deposit counts as first payment
                    $balanceDue,
                    $taxAmount,
                    $data['payment_schedule'] ?? $settings['default_payment_schedule'],
                    $paymentAmount,
                    $data['start_date'],
                    $data['due_date'],
                    $data['notes'] ?? null,
                    $_SESSION['user_id'] ?? 1
                ]
            );

            $layawayId = Database::lastInsertId();

            // Add items
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $this->addItem($layawayId, $item);
                }
            }

            // Record deposit payment
            if ($depositAmount > 0) {
                $this->recordPayment($layawayId, [
                    'amount' => $depositAmount,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'notes' => 'Initial deposit'
                ]);
            }

            // Log creation
            $this->logHistory($layawayId, 'created', "Layaway {$layawayNumber} created");

            // Send confirmation email
            $this->sendConfirmationEmail($layawayId);

            // Schedule reminders
            if ($settings['send_payment_reminders']) {
                $this->scheduleReminders($layawayId);
            }

            $this->logger->info('Layaway created', [
                'layaway_id' => $layawayId,
                'layaway_number' => $layawayNumber,
                'customer_id' => $data['customer_id']
            ]);

            return $layawayId;

        } catch (\Exception $e) {
            $this->logger->error('Failed to create layaway', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Add item to layaway
     */
    public function addItem(int $layawayId, array $item): void
    {
        $subtotal = $item['quantity'] * $item['unit_price'];

        Database::execute(
            "INSERT INTO layaway_items
            (layaway_id, product_id, product_name, product_sku, quantity, unit_price,
             subtotal, discount_amount, inventory_reserved, item_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE, 'reserved')",
            [
                $layawayId,
                $item['product_id'],
                $item['product_name'],
                $item['product_sku'] ?? null,
                $item['quantity'],
                $item['unit_price'],
                $subtotal,
                $item['discount_amount'] ?? 0
            ]
        );

        // Reserve inventory
        if ($item['product_id']) {
            $this->reserveInventory($item['product_id'], $item['quantity']);
        }

        $this->logHistory($layawayId, 'item_added', "Added {$item['product_name']} x{$item['quantity']}");
    }

    /**
     * Record a payment
     */
    public function recordPayment(int $layawayId, array $paymentData): int
    {
        // Insert payment record
        Database::execute(
            "INSERT INTO layaway_payments
            (layaway_id, amount, payment_method, payment_reference, card_last_four,
             card_type, receipt_number, received_by, notes, paid_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $layawayId,
                $paymentData['amount'],
                $paymentData['payment_method'],
                $paymentData['payment_reference'] ?? null,
                $paymentData['card_last_four'] ?? null,
                $paymentData['card_type'] ?? null,
                $paymentData['receipt_number'] ?? null,
                $_SESSION['user_id'] ?? 1,
                $paymentData['notes'] ?? null
            ]
        );

        $paymentId = Database::lastInsertId();

        // Update layaway amounts
        Database::execute(
            "UPDATE layaway
            SET amount_paid = amount_paid + ?,
                balance_due = balance_due - ?,
                updated_at = NOW(),
                updated_by = ?
            WHERE id = ?",
            [
                $paymentData['amount'],
                $paymentData['amount'],
                $_SESSION['user_id'] ?? 1,
                $layawayId
            ]
        );

        // Check if fully paid
        $layaway = $this->getLayaway($layawayId);
        if ($layaway['balance_due'] <= 0) {
            $this->completeLayaway($layawayId);
        }

        // Log payment
        $this->logHistory(
            $layawayId,
            'payment_received',
            "Payment received: $" . number_format($paymentData['amount'], 2),
            $paymentId,
            null,
            null,
            $paymentData['amount']
        );

        // Send payment confirmation
        $this->sendPaymentConfirmation($layawayId, $paymentData['amount']);

        return $paymentId;
    }

    /**
     * Complete a layaway (mark as paid in full)
     */
    public function completeLayaway(int $layawayId): void
    {
        Database::execute(
            "UPDATE layaway
            SET status = 'completed',
                completed_date = CURDATE(),
                updated_at = NOW(),
                updated_by = ?
            WHERE id = ?",
            [$_SESSION['user_id'] ?? 1, $layawayId]
        );

        // Update items to sold status
        Database::execute(
            "UPDATE layaway_items
            SET item_status = 'sold'
            WHERE layaway_id = ?",
            [$layawayId]
        );

        // Release reserved inventory and complete sale
        $items = $this->getLayawayItems($layawayId);
        foreach ($items as $item) {
            if ($item['product_id']) {
                // The inventory was already reserved, now convert to actual sale
                Database::execute(
                    "UPDATE products
                    SET stock_quantity = stock_quantity - ?
                    WHERE id = ?",
                    [$item['quantity'], $item['product_id']]
                );
            }
        }

        $this->logHistory($layawayId, 'completed', 'Layaway completed - paid in full');

        // Send completion email
        $this->sendCompletionEmail($layawayId);
    }

    /**
     * Cancel a layaway
     */
    public function cancelLayaway(int $layawayId, string $reason = null, bool $refund = false): void
    {
        $layaway = $this->getLayaway($layawayId);

        Database::execute(
            "UPDATE layaway
            SET status = 'cancelled',
                cancellation_reason = ?,
                cancelled_at = NOW(),
                cancelled_by = ?,
                updated_at = NOW()
            WHERE id = ?",
            [$reason, $_SESSION['user_id'] ?? 1, $layawayId]
        );

        // Release inventory
        $settings = $this->getSettings();
        if ($settings['release_on_cancellation']) {
            $this->releaseInventory($layawayId);
        }

        // Update items
        Database::execute(
            "UPDATE layaway_items
            SET item_status = 'released', inventory_reserved = FALSE
            WHERE layaway_id = ?",
            [$layawayId]
        );

        $this->logHistory($layawayId, 'cancelled', "Layaway cancelled: " . ($reason ?? 'No reason provided'));

        // Send cancellation email
        $this->sendCancellationEmail($layawayId, $reason);
    }

    /**
     * Get layaway details
     */
    public function getLayaway(int $layawayId): ?array
    {
        return Database::fetchOne(
            "SELECT l.*, CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                    c.email as customer_email, c.phone as customer_phone
            FROM layaway l
            LEFT JOIN customers c ON l.customer_id = c.id
            WHERE l.id = ?",
            [$layawayId]
        );
    }

    /**
     * Get layaway items
     */
    public function getLayawayItems(int $layawayId): array
    {
        return Database::fetchAll(
            "SELECT * FROM layaway_items WHERE layaway_id = ? ORDER BY id",
            [$layawayId]
        ) ?? [];
    }

    /**
     * Get layaway payments
     */
    public function getLayawayPayments(int $layawayId): array
    {
        return Database::fetchAll(
            "SELECT lp.*, CONCAT(u.first_name, ' ', u.last_name) as received_by_name
            FROM layaway_payments lp
            LEFT JOIN users u ON lp.received_by = u.id
            WHERE lp.layaway_id = ?
            ORDER BY lp.paid_at DESC",
            [$layawayId]
        ) ?? [];
    }

    /**
     * Get layaway history
     */
    public function getLayawayHistory(int $layawayId): array
    {
        return Database::fetchAll(
            "SELECT lh.*, CONCAT(u.first_name, ' ', u.last_name) as created_by_name
            FROM layaway_history lh
            LEFT JOIN users u ON lh.created_by = u.id
            WHERE lh.layaway_id = ?
            ORDER BY lh.created_at DESC",
            [$layawayId]
        ) ?? [];
    }

    /**
     * Get all layaways with filters
     */
    public function getLayawayList(array $filters = []): array
    {
        $sql = "SELECT * FROM layaway_summary WHERE 1=1";
        $params = [];

        if (!empty($filters['customer_id'])) {
            $sql .= " AND customer_id = ?";
            $params[] = $filters['customer_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_status'])) {
            $sql .= " AND payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (layaway_number LIKE ? OR customer_name LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }

        return Database::fetchAll($sql, $params) ?? [];
    }

    /**
     * Generate unique layaway number
     */
    private function generateLayawayNumber(): string
    {
        $prefix = 'LAY';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));

        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Calculate payment amount based on schedule
     */
    private function calculatePaymentAmount(float $balance, string $schedule, string $startDate, string $dueDate): float
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($dueDate);
        $days = $start->diff($end)->days;

        switch ($schedule) {
            case 'weekly':
                $periods = ceil($days / 7);
                break;
            case 'biweekly':
                $periods = ceil($days / 14);
                break;
            case 'monthly':
                $periods = ceil($days / 30);
                break;
            default:
                $periods = 1;
        }

        return $periods > 0 ? round($balance / $periods, 2) : $balance;
    }

    /**
     * Reserve inventory for layaway items
     */
    private function reserveInventory(int $productId, int $quantity): void
    {
        // Note: We're not actually reducing stock_quantity yet
        // Just marking it as reserved in layaway_items table
        // Stock will be reduced when layaway is completed
    }

    /**
     * Release reserved inventory
     */
    private function releaseInventory(int $layawayId): void
    {
        // Inventory is released by updating item_status to 'released'
        // No stock adjustments needed since we didn't reduce stock when reserving
    }

    /**
     * Log history event
     */
    private function logHistory(
        int $layawayId,
        string $eventType,
        string $description,
        ?int $paymentId = null,
        ?string $oldStatus = null,
        ?string $newStatus = null,
        ?float $amount = null
    ): void {
        Database::execute(
            "INSERT INTO layaway_history
            (layaway_id, event_type, event_description, payment_id, old_status, new_status, amount, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $layawayId,
                $eventType,
                $description,
                $paymentId,
                $oldStatus,
                $newStatus,
                $amount,
                $_SESSION['user_id'] ?? null
            ]
        );
    }

    /**
     * Schedule payment reminders
     */
    private function scheduleReminders(int $layawayId): void
    {
        $layaway = $this->getLayaway($layawayId);
        $settings = $this->getSettings();

        if (!$layaway || !$settings['send_payment_reminders']) {
            return;
        }

        // Calculate reminder dates based on payment schedule
        $dueDate = new \DateTime($layaway['due_date']);
        $reminderDate = clone $dueDate;
        $reminderDate->modify('-' . $settings['reminder_days_before_due'] . ' days');

        // Create reminder
        Database::execute(
            "INSERT INTO layaway_reminders
            (layaway_id, reminder_type, reminder_method, scheduled_date, recipient_email, recipient_phone, status)
            VALUES (?, 'payment_due', 'email', ?, ?, ?, 'pending')",
            [
                $layawayId,
                $reminderDate->format('Y-m-d'),
                $layaway['customer_email'],
                $layaway['customer_phone']
            ]
        );
    }

    /**
     * Send confirmation email
     */
    private function sendConfirmationEmail(int $layawayId): void
    {
        $layaway = $this->getLayaway($layawayId);
        $items = $this->getLayawayItems($layawayId);

        if (!$layaway || !$layaway['customer_email']) {
            return;
        }

        $data = [
            'subject' => 'Layaway Confirmation - ' . $layaway['layaway_number'],
            'customer_name' => $layaway['customer_name'],
            'layaway_number' => $layaway['layaway_number'],
            'total_amount' => $layaway['total_amount'],
            'deposit_amount' => $layaway['deposit_amount'],
            'balance_due' => $layaway['balance_due'],
            'payment_schedule' => ucfirst($layaway['payment_schedule']),
            'payment_amount' => $layaway['payment_amount'],
            'due_date' => date('F j, Y', strtotime($layaway['due_date'])),
            'items' => $items
        ];

        $this->emailService->sendTemplate(
            $layaway['customer_email'],
            'layaway_confirmation',
            $data
        );
    }

    /**
     * Send payment confirmation
     */
    private function sendPaymentConfirmation(int $layawayId, float $amount): void
    {
        $layaway = $this->getLayaway($layawayId);

        if (!$layaway || !$layaway['customer_email']) {
            return;
        }

        $data = [
            'subject' => 'Payment Received - ' . $layaway['layaway_number'],
            'customer_name' => $layaway['customer_name'],
            'layaway_number' => $layaway['layaway_number'],
            'payment_amount' => $amount,
            'balance_due' => $layaway['balance_due'],
            'amount_paid' => $layaway['amount_paid']
        ];

        $this->emailService->sendTemplate(
            $layaway['customer_email'],
            'layaway_payment',
            $data
        );
    }

    /**
     * Send completion email
     */
    private function sendCompletionEmail(int $layawayId): void
    {
        $layaway = $this->getLayaway($layawayId);
        $items = $this->getLayawayItems($layawayId);

        if (!$layaway || !$layaway['customer_email']) {
            return;
        }

        $data = [
            'subject' => 'Layaway Complete - ' . $layaway['layaway_number'],
            'customer_name' => $layaway['customer_name'],
            'layaway_number' => $layaway['layaway_number'],
            'total_amount' => $layaway['total_amount'],
            'items' => $items
        ];

        $this->emailService->sendTemplate(
            $layaway['customer_email'],
            'layaway_complete',
            $data
        );
    }

    /**
     * Send cancellation email
     */
    private function sendCancellationEmail(int $layawayId, ?string $reason): void
    {
        $layaway = $this->getLayaway($layawayId);

        if (!$layaway || !$layaway['customer_email']) {
            return;
        }

        $data = [
            'subject' => 'Layaway Cancelled - ' . $layaway['layaway_number'],
            'customer_name' => $layaway['customer_name'],
            'layaway_number' => $layaway['layaway_number'],
            'reason' => $reason,
            'amount_paid' => $layaway['amount_paid']
        ];

        $this->emailService->sendTemplate(
            $layaway['customer_email'],
            'layaway_cancelled',
            $data
        );
    }
}
