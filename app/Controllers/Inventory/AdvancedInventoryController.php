<?php

namespace App\Controllers\Inventory;

use App\Services\Inventory\AdvancedInventoryService;
use App\Services\Audit\AuditService;
use App\Helpers\Auth;

/**
 * Advanced Inventory Controller
 *
 * Manages advanced inventory features including reorder automation
 */
class AdvancedInventoryController
{
    private AdvancedInventoryService $inventoryService;
    private AuditService $auditService;

    public function __construct()
    {
        $this->inventoryService = new AdvancedInventoryService();
        $this->auditService = new AuditService();
    }

    /**
     * Show advanced inventory dashboard
     */
    public function index()
    {
        if (!Auth::check() || !Auth::hasPermission('inventory.manage')) {
            redirect('/login');
            return;
        }

        $statistics = $this->inventoryService->getStatistics();
        $needingReorder = $this->inventoryService->getProductsNeedingReorder();
        $slowMoving = $this->inventoryService->getSlowMovingInventory(90, 2);
        $fastMoving = $this->inventoryService->getFastMovingInventory(30, 10);

        require __DIR__ . '/../../Views/inventory/advanced/index.php';
    }

    /**
     * Show reorder management
     */
    public function reorderManagement()
    {
        if (!Auth::check() || !Auth::hasPermission('inventory.reorder')) {
            redirect('/login');
            return;
        }

        $productsNeedingReorder = $this->inventoryService->getProductsNeedingReorder();

        require __DIR__ . '/../../Views/inventory/advanced/reorder.php';
    }

    /**
     * Create automatic purchase order
     */
    public function createAutoPO()
    {
        if (!Auth::check() || !Auth::hasPermission('inventory.purchase_orders')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $vendorId = (int)($input['vendor_id'] ?? 0);
        $productIds = $input['product_ids'] ?? [];

        if (!$vendorId || empty($productIds)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Vendor and products required'], 400);
        }

        $poId = $this->inventoryService->createAutomaticPurchaseOrder($vendorId, $productIds);

        if ($poId) {
            // Log action
            $this->auditService->log(
                Auth::userId(),
                'create',
                'purchase_order',
                $poId,
                ['vendor_id' => $vendorId, 'product_count' => count($productIds), 'auto_generated' => true]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Purchase order created successfully',
                'po_id' => $poId
            ]);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to create purchase order'], 500);
        }
    }

    /**
     * Set reorder rule for product
     */
    public function setReorderRule(int $productId)
    {
        if (!Auth::check() || !Auth::hasPermission('inventory.manage')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $ruleData = [
            'reorder_point' => (int)$_POST['reorder_point'],
            'suggested_reorder_quantity' => (int)$_POST['suggested_reorder_quantity'],
            'lead_time_days' => (int)($_POST['lead_time_days'] ?? 7),
            'safety_stock_days' => (int)($_POST['safety_stock_days'] ?? 7),
            'auto_reorder_enabled' => isset($_POST['auto_reorder_enabled']) ? 1 : 0
        ];

        $success = $this->inventoryService->setReorderRule($productId, $ruleData);

        if ($success) {
            // Log action
            $this->auditService->log(
                Auth::userId(),
                'update',
                'reorder_rule',
                $productId,
                $ruleData
            );

            if ($this->isAjaxRequest()) {
                return $this->jsonResponse(['success' => true, 'message' => 'Reorder rule updated']);
            } else {
                $_SESSION['success'] = 'Reorder rule updated successfully';
                redirect('/inventory/advanced');
            }
        } else {
            if ($this->isAjaxRequest()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Failed to update rule'], 500);
            } else {
                $_SESSION['error'] = 'Failed to update reorder rule';
                redirect('/inventory/advanced');
            }
        }
    }

    /**
     * Show cycle count interface
     */
    public function cycleCount()
    {
        if (!Auth::check() || !Auth::hasPermission('inventory.cycle_count')) {
            redirect('/login');
            return;
        }

        require __DIR__ . '/../../Views/inventory/advanced/cycle_count.php';
    }

    /**
     * Record cycle count
     */
    public function recordCycleCount()
    {
        if (!Auth::check() || !Auth::hasPermission('inventory.cycle_count')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $productId = (int)$_POST['product_id'];
        $countedQuantity = (int)$_POST['counted_quantity'];
        $notes = $_POST['notes'] ?? '';

        $success = $this->inventoryService->recordCycleCount($productId, $countedQuantity, $notes);

        if ($success) {
            // Log action
            $this->auditService->log(
                Auth::userId(),
                'cycle_count',
                'inventory',
                $productId,
                ['counted_quantity' => $countedQuantity, 'notes' => $notes]
            );

            return $this->jsonResponse(['success' => true, 'message' => 'Cycle count recorded']);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to record count'], 500);
        }
    }

    /**
     * Get inventory forecast for product
     */
    public function forecast(int $productId)
    {
        if (!Auth::check() || !Auth::hasPermission('inventory.view')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $forecastDays = (int)($_GET['days'] ?? 30);
        $forecast = $this->inventoryService->getForecast($productId, $forecastDays);

        return $this->jsonResponse(['success' => true, 'forecast' => $forecast]);
    }

    /**
     * Show inventory valuation report
     */
    public function valuation()
    {
        if (!Auth::check() || !Auth::hasPermission('inventory.view')) {
            redirect('/login');
            return;
        }

        $valuation = $this->inventoryService->getInventoryValuation();

        require __DIR__ . '/../../Views/inventory/advanced/valuation.php';
    }

    /**
     * Calculate suggested reorder quantity
     */
    public function calculateReorderQuantity(int $productId)
    {
        if (!Auth::check() || !Auth::hasPermission('inventory.view')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $days = (int)($_GET['days'] ?? 30);
        $quantity = $this->inventoryService->calculateReorderQuantity($productId, $days);

        return $this->jsonResponse([
            'success' => true,
            'suggested_quantity' => $quantity
        ]);
    }

    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
