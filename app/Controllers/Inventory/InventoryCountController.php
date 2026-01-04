<?php

namespace App\Controllers\Inventory;

use App\Core\Database;

class InventoryCountController
{
    public function __construct()
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }
    }

    /**
     * List all inventory counts
     */
    public function index()
    {
        if (!hasPermission('inventory.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $counts = Database::fetchAll(
            "SELECT ic.*, 
                    u1.first_name as started_by_name,
                    u2.first_name as completed_by_name,
                    COUNT(ici.id) as item_count
             FROM inventory_counts ic
             LEFT JOIN users u1 ON ic.started_by = u1.id
             LEFT JOIN users u2 ON ic.completed_by = u2.id
             LEFT JOIN inventory_count_items ici ON ic.id = ici.count_id
             GROUP BY ic.id
             ORDER BY ic.created_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );

        $total = Database::fetchOne("SELECT COUNT(*) as total FROM inventory_counts")['total'] ?? 0;
        $totalPages = ceil($total / $limit);

        require __DIR__ . '/../../Views/inventory/counts/index.php';
    }

    /**
     * Create new inventory count
     */
    public function create()
    {
        if (!hasPermission('inventory.create')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/inventory/counts');
        }

        require __DIR__ . '/../../Views/inventory/counts/create.php';
    }

    /**
     * Store new inventory count
     */
    public function store()
    {
        if (!hasPermission('inventory.create')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            // Generate count number
            $countNumber = 'IC-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $data = [
                'count_number' => $countNumber,
                'count_type' => sanitizeInput($_POST['count_type'] ?? 'partial'),
                'location' => sanitizeInput($_POST['location'] ?? ''),
                'status' => 'planned',
                'notes' => sanitizeInput($_POST['notes'] ?? ''),
                'started_by' => currentUser()['id']
            ];

            $countId = Database::insert('inventory_counts', $data);

            // If products selected, add them to count
            if (!empty($_POST['products'])) {
                $productIds = json_decode($_POST['products'], true);
                foreach ($productIds as $productId) {
                    $product = Database::fetchOne("SELECT stock_quantity FROM products WHERE id = ?", [$productId]);
                    if ($product) {
                        Database::insert('inventory_count_items', [
                            'count_id' => $countId,
                            'product_id' => $productId,
                            'expected_quantity' => $product['stock_quantity']
                        ]);
                    }
                }
            }

            $_SESSION['flash_success'] = 'Inventory count created successfully';
            redirect("/inventory/counts/{$countId}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/inventory/counts/create');
        }
    }

    /**
     * Show count details and perform counting
     */
    public function show(int $id)
    {
        if (!hasPermission('inventory.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $count = Database::fetchOne(
            "SELECT ic.*,
                    u1.first_name as started_by_name,
                    u2.first_name as completed_by_name
             FROM inventory_counts ic
             LEFT JOIN users u1 ON ic.started_by = u1.id
             LEFT JOIN users u2 ON ic.completed_by = u2.id
             WHERE ic.id = ?",
            [$id]
        );

        if (!$count) {
            $_SESSION['flash_error'] = 'Count not found';
            redirect('/inventory/counts');
        }

        // Get count items
        $items = Database::fetchAll(
            "SELECT ici.*, 
                    p.name as product_name, 
                    p.sku,
                    p.barcode,
                    pv.variant_name
             FROM inventory_count_items ici
             INNER JOIN products p ON ici.product_id = p.id
             LEFT JOIN product_variants pv ON ici.variant_id = pv.id
             WHERE ici.count_id = ?
             ORDER BY p.name",
            [$id]
        );

        require __DIR__ . '/../../Views/inventory/counts/show.php';
    }

    /**
     * Start counting
     */
    public function start(int $id)
    {
        if (!hasPermission('inventory.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            Database::update('inventory_counts', [
                'status' => 'in_progress',
                'started_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]);

            $_SESSION['flash_success'] = 'Count started';
            redirect("/inventory/counts/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/inventory/counts/{$id}");
        }
    }

    /**
     * Update counted quantity (via barcode scan or manual entry)
     */
    public function updateCount()
    {
        if (!hasPermission('inventory.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $itemId = (int)$_POST['item_id'];
            $countedQty = (int)$_POST['counted_quantity'];
            $userId = currentUser()['id'];

            $item = Database::fetchOne("SELECT * FROM inventory_count_items WHERE id = ?", [$itemId]);
            if (!$item) {
                throw new \Exception('Item not found');
            }

            $difference = $countedQty - $item['expected_quantity'];

            Database::update('inventory_count_items', [
                'counted_quantity' => $countedQty,
                'difference' => $difference,
                'counted_by' => $userId,
                'counted_at' => date('Y-m-d H:i:s'),
                'notes' => sanitizeInput($_POST['notes'] ?? '')
            ], ['id' => $itemId]);

            jsonResponse(['success' => true, 'difference' => $difference]);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Scan barcode to find and update product count
     */
    public function scanBarcode()
    {
        if (!hasPermission('inventory.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $barcode = sanitizeInput($_POST['barcode'] ?? '');
            $countId = (int)$_POST['count_id'];

            if (empty($barcode)) {
                throw new \Exception('Barcode required');
            }

            // Find product by barcode or SKU
            $product = Database::fetchOne(
                "SELECT id, name, sku, barcode, stock_quantity FROM products 
                 WHERE barcode = ? OR sku = ?",
                [$barcode, $barcode]
            );

            if (!$product) {
                jsonResponse(['error' => 'Product not found'], 404);
                return;
            }

            // Check if item exists in count
            $item = Database::fetchOne(
                "SELECT * FROM inventory_count_items 
                 WHERE count_id = ? AND product_id = ?",
                [$countId, $product['id']]
            );

            if (!$item) {
                // Add to count
                $itemId = Database::insert('inventory_count_items', [
                    'count_id' => $countId,
                    'product_id' => $product['id'],
                    'expected_quantity' => $product['stock_quantity'],
                    'counted_quantity' => 1,
                    'difference' => 1 - $product['stock_quantity'],
                    'counted_by' => currentUser()['id'],
                    'counted_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                // Increment counted quantity
                $newQty = ($item['counted_quantity'] ?? 0) + 1;
                Database::update('inventory_count_items', [
                    'counted_quantity' => $newQty,
                    'difference' => $newQty - $item['expected_quantity'],
                    'counted_by' => currentUser()['id'],
                    'counted_at' => date('Y-m-d H:i:s')
                ], ['id' => $item['id']]);
                $itemId = $item['id'];
            }

            jsonResponse([
                'success' => true,
                'product' => $product,
                'item_id' => $itemId
            ]);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Complete count and apply adjustments
     */
    public function complete(int $id)
    {
        if (!hasPermission('inventory.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            Database::beginTransaction();

            // Get all counted items
            $items = Database::fetchAll(
                "SELECT * FROM inventory_count_items WHERE count_id = ? AND counted_quantity IS NOT NULL",
                [$id]
            );

            foreach ($items as $item) {
                if ($item['difference'] != 0) {
                    // Create inventory adjustment transaction
                    $product = Database::fetchOne("SELECT stock_quantity FROM products WHERE id = ?", [$item['product_id']]);
                    
                    Database::insert('inventory_transactions', [
                        'product_id' => $item['product_id'],
                        'variant_id' => $item['variant_id'],
                        'transaction_type' => 'adjustment',
                        'quantity_change' => $item['difference'],
                        'quantity_before' => $product['stock_quantity'],
                        'quantity_after' => $item['counted_quantity'],
                        'reference_type' => 'inventory_count',
                        'reference_id' => $id,
                        'notes' => 'Inventory count adjustment',
                        'user_id' => currentUser()['id']
                    ]);

                    // Update product quantity
                    Database::query(
                        "UPDATE products SET stock_quantity = ? WHERE id = ?",
                        [$item['counted_quantity'], $item['product_id']]
                    );
                }
            }

            // Mark count as completed
            Database::update('inventory_counts', [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
                'completed_by' => currentUser()['id']
            ], ['id' => $id]);

            Database::commit();

            $_SESSION['flash_success'] = 'Inventory count completed and adjustments applied';
            redirect("/inventory/counts/{$id}");
        } catch (\Exception $e) {
            Database::rollback();
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/inventory/counts/{$id}");
        }
    }
}
