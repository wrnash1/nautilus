<?php

namespace App\Services\RMA;

use App\Core\Database;

/**
 * RMA (Return Merchandise Authorization) Service
 * Handles customer returns, vendor returns, warranty claims, and exchanges
 */
class RMAService
{
    /**
     * Create a new RMA request
     *
     * @param array $data RMA request data
     * @return int RMA request ID
     */
    public function createRMARequest(array $data): int
    {
        // Generate unique RMA number
        $rmaNumber = $this->generateRMANumber();

        // Calculate total amount from items
        $totalAmount = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $totalAmount += ($item['quantity'] * $item['unit_price']);
            }
        }

        // Calculate refund amount (may include restocking fee)
        $restockingFee = $this->calculateRestockingFee($totalAmount, $data['reason'] ?? 'other');
        $refundAmount = $totalAmount - $restockingFee;

        // Insert RMA request
        $rmaId = Database::insert(
            "INSERT INTO rma_requests (
                rma_number, customer_id, transaction_id, vendor_id, rma_type,
                status, reason, reason_notes, requested_resolution,
                total_amount, refund_amount, restocking_fee,
                customer_notes, requires_inspection, requested_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $rmaNumber,
                $data['customer_id'],
                $data['transaction_id'] ?? null,
                $data['vendor_id'] ?? null,
                $data['rma_type'] ?? 'customer_return',
                'pending',
                $data['reason'],
                $data['reason_notes'] ?? null,
                $data['requested_resolution'] ?? 'refund',
                $totalAmount,
                $refundAmount,
                $restockingFee,
                $data['customer_notes'] ?? null,
                isset($data['requires_inspection']) ? 1 : 0
            ]
        );

        // Add RMA items
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $this->addRMAItem($rmaId, $item);
            }
        }

        // Log status change
        $this->logStatusChange($rmaId, null, 'pending', currentUser()['id'] ?? null, 'RMA request created');

        // Send notification email
        if ($this->getSetting('email_notifications')) {
            $this->sendRMANotification($rmaId, 'created');
        }

        return $rmaId;
    }

    /**
     * Add an item to an RMA request
     */
    private function addRMAItem(int $rmaId, array $item): int
    {
        $totalPrice = $item['quantity'] * $item['unit_price'];

        return Database::insert(
            "INSERT INTO rma_items (
                rma_request_id, transaction_item_id, product_id, variant_id,
                quantity, unit_price, total_price, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $rmaId,
                $item['transaction_item_id'] ?? null,
                $item['product_id'],
                $item['variant_id'] ?? null,
                $item['quantity'],
                $item['unit_price'],
                $totalPrice,
                $item['notes'] ?? null
            ]
        );
    }

    /**
     * Approve an RMA request
     */
    public function approveRMA(int $rmaId, ?int $approvedBy = null): bool
    {
        $result = Database::execute(
            "UPDATE rma_requests
             SET status = 'approved', approved_date = NOW(), approved_by = ?
             WHERE id = ?",
            [$approvedBy, $rmaId]
        );

        if ($result) {
            $this->logStatusChange($rmaId, 'pending', 'approved', $approvedBy, 'RMA approved');
            $this->sendRMANotification($rmaId, 'approved');
        }

        return $result;
    }

    /**
     * Reject an RMA request
     */
    public function rejectRMA(int $rmaId, string $reason, ?int $rejectedBy = null): bool
    {
        $result = Database::execute(
            "UPDATE rma_requests
             SET status = 'rejected', internal_notes = ?
             WHERE id = ?",
            [$reason, $rmaId]
        );

        if ($result) {
            $this->logStatusChange($rmaId, 'pending', 'rejected', $rejectedBy, $reason);
            $this->sendRMANotification($rmaId, 'rejected');
        }

        return $result;
    }

    /**
     * Mark RMA items as received and inspect
     */
    public function receiveRMA(int $rmaId, array $itemConditions, string $inspectionNotes): bool
    {
        // Update RMA status to received
        Database::execute(
            "UPDATE rma_requests
             SET status = 'received', received_date = NOW(), inspection_notes = ?
             WHERE id = ?",
            [$inspectionNotes, $rmaId]
        );

        // Update item conditions and dispositions
        foreach ($itemConditions as $itemId => $condition) {
            Database::execute(
                "UPDATE rma_items
                 SET condition_received = ?, disposition = ?
                 WHERE id = ? AND rma_request_id = ?",
                [
                    $condition['condition'],
                    $this->determineDisposition($condition['condition']),
                    $itemId,
                    $rmaId
                ]
            );
        }

        $this->logStatusChange($rmaId, 'approved', 'received', currentUser()['id'] ?? null, 'Items received and inspected');

        return true;
    }

    /**
     * Process refund for an RMA
     */
    public function processRefund(int $rmaId, float $refundAmount, ?int $processedBy = null): bool
    {
        $result = Database::execute(
            "UPDATE rma_requests
             SET status = 'refunded', refund_amount = ?, completed_date = NOW(), processed_by = ?
             WHERE id = ?",
            [$refundAmount, $processedBy, $rmaId]
        );

        if ($result) {
            // Restock items if applicable
            $this->restockRMAItems($rmaId);

            $this->logStatusChange($rmaId, 'received', 'refunded', $processedBy, "Refund processed: $" . number_format($refundAmount, 2));
            $this->sendRMANotification($rmaId, 'refunded');
        }

        return $result;
    }

    /**
     * Restock returned items that are in good condition
     */
    private function restockRMAItems(int $rmaId): void
    {
        $items = Database::fetchAll(
            "SELECT * FROM rma_items
             WHERE rma_request_id = ? AND disposition = 'restock' AND restocked = 0",
            [$rmaId]
        );

        foreach ($items as $item) {
            // Increase product stock
            Database::execute(
                "UPDATE products
                 SET stock_quantity = stock_quantity + ?
                 WHERE id = ?",
                [$item['quantity'], $item['product_id']]
            );

            // Log inventory transaction
            Database::execute(
                "INSERT INTO inventory_transactions (
                    product_id, transaction_type, quantity_change,
                    quantity_before, quantity_after, reference_type, reference_id,
                    notes, user_id, created_at
                ) SELECT
                    id, 'return', ?, stock_quantity - ?, stock_quantity,
                    'rma', ?, 'RMA restocking', ?, NOW()
                FROM products WHERE id = ?",
                [
                    $item['quantity'],
                    $item['quantity'],
                    $rmaId,
                    currentUser()['id'] ?? null,
                    $item['product_id']
                ]
            );

            // Mark item as restocked
            Database::execute(
                "UPDATE rma_items
                 SET restocked = 1, restocked_date = NOW()
                 WHERE id = ?",
                [$item['id']]
            );
        }
    }

    /**
     * Create vendor RMA for defective items
     */
    public function createVendorRMA(int $customerRmaId, int $vendorId, array $itemIds): int
    {
        $customerRma = $this->getRMAById($customerRmaId);

        // Generate vendor RMA number
        $rmaNumber = $this->generateRMANumber('VENDOR');

        $vendorRmaId = Database::insert(
            "INSERT INTO rma_requests (
                rma_number, customer_id, vendor_id, rma_type, status,
                reason, reason_notes, total_amount, internal_notes
            ) VALUES (?, ?, ?, 'vendor_return', 'pending', ?, ?, ?, ?)",
            [
                $rmaNumber,
                $customerRma['customer_id'],
                $vendorId,
                'defective',
                'Items from customer RMA #' . $customerRma['rma_number'],
                0, // Will calculate from items
                'Vendor return from customer RMA #' . $customerRma['rma_number']
            ]
        );

        // Copy items to vendor RMA
        Database::execute(
            "INSERT INTO rma_items (rma_request_id, product_id, variant_id, quantity, unit_price, total_price, notes)
             SELECT ?, product_id, variant_id, quantity, unit_price, total_price, notes
             FROM rma_items
             WHERE id IN (" . implode(',', array_fill(0, count($itemIds), '?')) . ")",
            array_merge([$vendorRmaId], $itemIds)
        );

        return $vendorRmaId;
    }

    /**
     * Get RMA request by ID with all details
     */
    public function getRMAById(int $id): ?array
    {
        $rma = Database::fetchOne(
            "SELECT r.*, c.first_name, c.last_name, c.email,
                    v.name as vendor_name,
                    u1.first_name as approved_by_name,
                    u2.first_name as processed_by_name
             FROM rma_requests r
             LEFT JOIN customers c ON r.customer_id = c.id
             LEFT JOIN vendors v ON r.vendor_id = v.id
             LEFT JOIN users u1 ON r.approved_by = u1.id
             LEFT JOIN users u2 ON r.processed_by = u2.id
             WHERE r.id = ?",
            [$id]
        );

        if ($rma) {
            $rma['items'] = $this->getRMAItems($id);
            $rma['status_history'] = $this->getRMAStatusHistory($id);
        }

        return $rma;
    }

    /**
     * Get items for an RMA
     */
    private function getRMAItems(int $rmaId): array
    {
        return Database::fetchAll(
            "SELECT ri.*, p.name as product_name, p.sku, pv.variant_name
             FROM rma_items ri
             JOIN products p ON ri.product_id = p.id
             LEFT JOIN product_variants pv ON ri.variant_id = pv.id
             WHERE ri.rma_request_id = ?",
            [$rmaId]
        ) ?? [];
    }

    /**
     * Get status history for an RMA
     */
    private function getRMAStatusHistory(int $rmaId): array
    {
        return Database::fetchAll(
            "SELECT h.*, u.first_name, u.last_name
             FROM rma_status_history h
             LEFT JOIN users u ON h.changed_by = u.id
             WHERE h.rma_request_id = ?
             ORDER BY h.changed_at DESC",
            [$rmaId]
        ) ?? [];
    }

    /**
     * Get all RMAs with filters
     */
    public function getAllRMAs(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "r.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['customer_id'])) {
            $where[] = "r.customer_id = ?";
            $params[] = $filters['customer_id'];
        }

        if (!empty($filters['rma_type'])) {
            $where[] = "r.rma_type = ?";
            $params[] = $filters['rma_type'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "r.requested_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "r.requested_date <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $params[] = $limit;
        $params[] = $offset;

        return Database::fetchAll(
            "SELECT r.*, c.first_name, c.last_name, c.email,
                    (SELECT COUNT(*) FROM rma_items WHERE rma_request_id = r.id) as item_count
             FROM rma_requests r
             LEFT JOIN customers c ON r.customer_id = c.id
             {$whereClause}
             ORDER BY r.requested_date DESC
             LIMIT ? OFFSET ?",
            $params
        ) ?? [];
    }

    /**
     * Generate unique RMA number
     */
    private function generateRMANumber(string $prefix = 'RMA'): string
    {
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $rmaNumber = "{$prefix}-{$date}-{$random}";

        // Ensure uniqueness
        $exists = Database::fetchOne(
            "SELECT id FROM rma_requests WHERE rma_number = ?",
            [$rmaNumber]
        );

        if ($exists) {
            return $this->generateRMANumber($prefix); // Recursively try again
        }

        return $rmaNumber;
    }

    /**
     * Calculate restocking fee
     */
    private function calculateRestockingFee(float $amount, string $reason): float
    {
        // No restocking fee for defective items
        if (in_array($reason, ['defective', 'wrong_item', 'not_as_described', 'damaged_shipping'])) {
            return 0.00;
        }

        $feePercentage = (int)$this->getSetting('restocking_fee_percentage');

        if ($feePercentage > 0) {
            return round($amount * ($feePercentage / 100), 2);
        }

        return 0.00;
    }

    /**
     * Determine disposition based on item condition
     */
    private function determineDisposition(string $condition): string
    {
        return match($condition) {
            'unopened', 'opened_unused' => 'restock',
            'defective', 'damaged' => 'vendor_return',
            'used_good', 'used_fair' => 'pending', // Manager decision
            default => 'pending'
        };
    }

    /**
     * Log status change
     */
    private function logStatusChange(int $rmaId, ?string $oldStatus, string $newStatus, ?int $changedBy, ?string $notes = null): void
    {
        Database::execute(
            "INSERT INTO rma_status_history (rma_request_id, old_status, new_status, changed_by, notes, changed_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$rmaId, $oldStatus, $newStatus, $changedBy, $notes]
        );
    }

    /**
     * Send RMA notification email
     */
    private function sendRMANotification(int $rmaId, string $event): void
    {
        // Placeholder for email notification
        // TODO: Implement email sending
        error_log("RMA Notification: RMA #{$rmaId} - Event: {$event}");
    }

    /**
     * Get RMA setting
     */
    private function getSetting(string $key)
    {
        $result = Database::fetchOne(
            "SELECT setting_value, setting_type FROM settings WHERE category = 'rma' AND setting_key = ?",
            [$key]
        );

        if (!$result) {
            return null;
        }

        return $result['setting_type'] === 'boolean'
            ? ($result['setting_value'] === '1' || $result['setting_value'] === 'true')
            : $result['setting_value'];
    }

    /**
     * Check if return is within allowed window
     */
    public function isWithinReturnWindow(string $purchaseDate): bool
    {
        $returnWindowDays = (int)$this->getSetting('return_window_days') ?: 30;
        $purchaseTimestamp = strtotime($purchaseDate);
        $windowEndTimestamp = $purchaseTimestamp + ($returnWindowDays * 86400);

        return time() <= $windowEndTimestamp;
    }

    /**
     * Get RMA statistics
     */
    public function getRMAStatistics(array $dateRange = []): array
    {
        $where = '';
        $params = [];

        if (!empty($dateRange['from'])) {
            $where .= " AND requested_date >= ?";
            $params[] = $dateRange['from'];
        }

        if (!empty($dateRange['to'])) {
            $where .= " AND requested_date <= ?";
            $params[] = $dateRange['to'];
        }

        $stats = Database::fetchOne(
            "SELECT
                COUNT(*) as total_rmas,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(total_amount) as total_amount,
                SUM(refund_amount) as total_refunded,
                SUM(restocking_fee) as total_restocking_fees
             FROM rma_requests
             WHERE 1=1 {$where}",
            $params
        );

        return $stats ?? [];
    }
}
