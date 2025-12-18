<?php

namespace App\Services\AirFills;

use App\Core\Database;

class AirFillService
{
    /**
     * Get paginated air fills with filters
     */
    public function getAirFills(int $page = 1, int $perPage = 25, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];

        // Build WHERE clause
        if (!empty($filters['search'])) {
            $where[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['fill_type'])) {
            $where[] = "af.fill_type = ?";
            $params[] = $filters['fill_type'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(af.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(af.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count
        $countSql = "SELECT COUNT(*) as total
                     FROM air_fills af
                     LEFT JOIN customers c ON af.customer_id = c.id
                     $whereClause";

        $countResult = Database::fetchOne($countSql, $params);
        $totalRecords = (int)$countResult['total'];
        $totalPages = ceil($totalRecords / $perPage);

        // Get data
        $sql = "SELECT af.*,
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       c.email as customer_email,
                       CONCAT(u.first_name, ' ', u.last_name) as filled_by_name,
                       re.name as equipment_name,
                       re.equipment_code,
                       t.id as transaction_id
                FROM air_fills af
                LEFT JOIN customers c ON af.customer_id = c.id
                LEFT JOIN users u ON af.filled_by = u.id
                LEFT JOIN rental_equipment re ON af.equipment_id = re.id
                LEFT JOIN transactions t ON af.transaction_id = t.id
                $whereClause
                ORDER BY af.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $data = Database::fetchAll($sql, $params) ?? [];

        return [
            'data' => $data,
            'totalPages' => $totalPages,
            'totalRecords' => $totalRecords,
            'currentPage' => $page,
            'perPage' => $perPage
        ];
    }

    /**
     * Get all air fills (for export)
     */
    public function getAllAirFills(array $filters = []): array
    {
        $params = [];
        $where = [];

        if (!empty($filters['search'])) {
            $where[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['fill_type'])) {
            $where[] = "af.fill_type = ?";
            $params[] = $filters['fill_type'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(af.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(af.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT af.*,
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       CONCAT(u.first_name, ' ', u.last_name) as filled_by_name,
                       re.name as equipment_name
                FROM air_fills af
                LEFT JOIN customers c ON af.customer_id = c.id
                LEFT JOIN users u ON af.filled_by = u.id
                LEFT JOIN rental_equipment re ON af.equipment_id = re.id
                $whereClause
                ORDER BY af.created_at DESC";

        return Database::fetchAll($sql, $params) ?? [];
    }

    /**
     * Get air fill by ID
     */
    public function getAirFillById(int $id): ?array
    {
        $sql = "SELECT af.*,
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       c.email as customer_email,
                       c.phone as customer_phone,
                       CONCAT(u.first_name, ' ', u.last_name) as filled_by_name,
                       re.name as equipment_name,
                       re.equipment_code,
                       t.id as transaction_id,
                       t.total as transaction_total
                FROM air_fills af
                LEFT JOIN customers c ON af.customer_id = c.id
                LEFT JOIN users u ON af.filled_by = u.id
                LEFT JOIN rental_equipment re ON af.equipment_id = re.id
                LEFT JOIN transactions t ON af.transaction_id = t.id
                WHERE af.id = ?";

        return Database::fetchOne($sql, [$id]);
    }

    /**
     * Create new air fill
     */
    /**
     * Create new air fill
     */
    public function createAirFill(array $data): int
    {
        Database::beginTransaction();

        try {
            $sql = "INSERT INTO air_fills (
                        customer_id, equipment_id, customer_equipment_id, compressor_id, 
                        fill_type, fill_pressure, nitrox_percentage, cost, 
                        notes, filled_by, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $params = [
                $data['customer_id'],
                $data['equipment_id'] ?? null,
                $data['customer_equipment_id'] ?? null,
                $data['compressor_id'] ?? null,
                $data['fill_type'],
                $data['fill_pressure'],
                $data['nitrox_percentage'],
                $data['cost'],
                $data['notes'] ?? '',
                $data['filled_by']
            ];

            Database::execute($sql, $params);
            $airFillId = Database::lastInsertId();

            // Compressor Maintenance Logic
            if (!empty($data['compressor_id']) && !empty($data['run_time_minutes'])) {
                $hours = $data['run_time_minutes'] / 60;
                
                // Update Compressor Hours
                Database::execute(
                    "UPDATE compressors SET current_hours = current_hours + ? WHERE id = ?",
                    [$hours, $data['compressor_id']]
                );

                // Log Usage
                Database::execute(
                    "INSERT INTO compressor_logs (compressor_id, user_id, type, hours_recorded, description, created_at) 
                     VALUES (?, ?, 'fill_run', ?, ?, NOW())",
                    [
                        $data['compressor_id'],
                        $data['filled_by'],
                        $hours,
                        "Air Fill #$airFillId (" . $data['run_time_minutes'] . " mins)"
                    ]
                );
            }

            // Create transaction if requested
            if (!empty($data['create_transaction']) && $data['cost'] > 0 && $data['customer_id']) {
                $transactionId = $this->createTransaction($airFillId, $data);

                // Link air fill to transaction
                Database::execute(
                    "UPDATE air_fills SET transaction_id = ? WHERE id = ?",
                    [$transactionId, $airFillId]
                );
            }

            Database::commit();

            // Log the action (existing audit)
            if (function_exists('logAudit')) {
                logAudit('air_fill', 'create', $airFillId, [
                    'fill_type' => $data['fill_type'],
                    'cost' => $data['cost']
                ]);
            }

            return $airFillId;

        } catch (\Exception $e) {
            Database::rollback();
            throw $e;
        }
    }

    /**
     * Update air fill
     */
    public function updateAirFill(int $id, array $data): bool
    {
        $sql = "UPDATE air_fills SET
                    customer_id = ?,
                    equipment_id = ?,
                    fill_type = ?,
                    fill_pressure = ?,
                    nitrox_percentage = ?,
                    cost = ?,
                    notes = ?
                WHERE id = ? AND transaction_id IS NULL";

        $params = [
            $data['customer_id'],
            $data['equipment_id'] ?? null,
            $data['fill_type'],
            $data['fill_pressure'],
            $data['nitrox_percentage'],
            $data['cost'],
            $data['notes'] ?? '',
            $id
        ];

        $result = Database::execute($sql, $params);

        logAudit('air_fill', 'update', $id, $data);

        return $result;
    }

    /**
     * Delete air fill
     */
    public function deleteAirFill(int $id): bool
    {
        // Only delete if not linked to transaction
        $result = Database::execute(
            "DELETE FROM air_fills WHERE id = ? AND transaction_id IS NULL",
            [$id]
        );

        if ($result) {
            logAudit('air_fill', 'delete', $id);
        }

        return $result;
    }

    /**
     * Get statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $params = [];
        $where = [];

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['fill_type'])) {
            $where[] = "fill_type = ?";
            $params[] = $filters['fill_type'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Total fills and revenue
        $totals = Database::fetchOne(
            "SELECT COUNT(*) as total_fills,
                    COALESCE(SUM(cost), 0) as total_revenue
             FROM air_fills
             $whereClause",
            $params
        );

        // Breakdown by fill type
        $byType = Database::fetchAll(
            "SELECT fill_type,
                    COUNT(*) as count,
                    COALESCE(SUM(cost), 0) as revenue
             FROM air_fills
             $whereClause
             GROUP BY fill_type",
            $params
        );

        // Today's fills
        $today = Database::fetchOne(
            "SELECT COUNT(*) as count,
                    COALESCE(SUM(cost), 0) as revenue
             FROM air_fills
             WHERE DATE(created_at) = CURDATE()"
        );

        return [
            'total_fills' => (int)$totals['total_fills'],
            'total_revenue' => (float)$totals['total_revenue'],
            'by_type' => $byType,
            'today_count' => (int)$today['count'],
            'today_revenue' => (float)$today['revenue']
        ];
    }

    /**
     * Calculate pricing based on fill type and pressure
     */
    public function calculatePricing(string $fillType, int $pressure): array
    {
        // Default pricing (these should come from settings table)
        $basePrices = [
            'air' => 8.00,
            'nitrox' => 12.00,
            'trimix' => 25.00,
            'oxygen' => 15.00
        ];

        $basePrice = $basePrices[$fillType] ?? 8.00;

        // Adjust for pressure (base price is for 3000 PSI)
        $adjustedPrice = $basePrice * ($pressure / 3000);

        return [
            'base_price' => $basePrice,
            'adjusted_price' => round($adjustedPrice, 2),
            'pressure' => $pressure,
            'fill_type' => $fillType
        ];
    }

    /**
     * Create transaction for air fill
     */
    private function createTransaction(int $airFillId, array $data): int
    {
        // Create transaction
        $transactionSql = "INSERT INTO transactions (
                            customer_id, total, subtotal, tax,
                            transaction_type, status, created_at, created_by
                           ) VALUES (?, ?, ?, ?, 'sale', 'completed', NOW(), ?)";

        $subtotal = $data['cost'];
        $tax = 0; // Could calculate tax based on settings
        $total = $subtotal + $tax;

        Database::execute($transactionSql, [
            $data['customer_id'],
            $total,
            $subtotal,
            $tax,
            $data['filled_by']
        ]);

        $transactionId = Database::lastInsertId();

        // Create transaction item
        $itemSql = "INSERT INTO transaction_items (
                        transaction_id, product_id, description,
                        quantity, price, subtotal, created_at
                    ) VALUES (?, NULL, ?, 1, ?, ?, NOW())";

        $description = ucfirst($data['fill_type']) . ' Fill';
        if ($data['fill_type'] === 'nitrox' && $data['nitrox_percentage']) {
            $description .= ' (' . $data['nitrox_percentage'] . '% O2)';
        }
        $description .= ' - ' . $data['fill_pressure'] . ' PSI';

        Database::execute($itemSql, [
            $transactionId,
            $description,
            $data['cost'],
            $data['cost']
        ]);

        // Create payment
        $paymentSql = "INSERT INTO payments (
                            transaction_id, amount, payment_method,
                            status, created_at
                       ) VALUES (?, ?, 'cash', 'completed', NOW())";

        Database::execute($paymentSql, [$transactionId, $total]);

        return $transactionId;
    }

    /**
     * Get customer's fill history
     */
    public function getCustomerHistory(int $customerId, int $limit = 10): array
    {
        $sql = "SELECT af.*,
                       CONCAT(u.first_name, ' ', u.last_name) as filled_by_name,
                       re.name as equipment_name
                FROM air_fills af
                LEFT JOIN users u ON af.filled_by = u.id
                LEFT JOIN rental_equipment re ON af.equipment_id = re.id
                WHERE af.customer_id = ?
                ORDER BY af.created_at DESC
                LIMIT ?";

        return Database::fetchAll($sql, [$customerId, $limit]) ?? [];
    }
}
