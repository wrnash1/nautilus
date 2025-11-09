<?php

namespace App\Controllers\API\V1;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Services\Inventory\ProductService;

/**
 * Product API Controller
 *
 * RESTful API endpoints for product management
 */
class ProductApiController
{
    private ProductService $productService;

    public function __construct()
    {
        $this->productService = new ProductService();
    }

    /**
     * GET /api/v1/products
     * List all products with pagination
     */
    public function index(): void
    {
        try {
            ApiAuthController::requirePermission('products.read');

            $page = (int)($_GET['page'] ?? 1);
            $perPage = min((int)($_GET['per_page'] ?? 50), 100); // Max 100 per page
            $offset = ($page - 1) * $perPage;

            // Build filters
            $where = ['is_active = 1'];
            $params = [];

            if (isset($_GET['category_id'])) {
                $where[] = 'category_id = ?';
                $params[] = $_GET['category_id'];
            }

            if (isset($_GET['search'])) {
                $where[] = '(name LIKE ? OR sku LIKE ? OR description LIKE ?)';
                $searchTerm = '%' . $_GET['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (isset($_GET['min_price'])) {
                $where[] = 'price >= ?';
                $params[] = $_GET['min_price'];
            }

            if (isset($_GET['max_price'])) {
                $where[] = 'price <= ?';
                $params[] = $_GET['max_price'];
            }

            if (isset($_GET['in_stock'])) {
                $where[] = 'stock_quantity > 0';
            }

            $whereClause = implode(' AND ', $where);

            // Get total count
            $total = TenantDatabase::fetchOneTenant(
                "SELECT COUNT(*) as count FROM products WHERE {$whereClause}",
                $params
            )['count'] ?? 0;

            // Get products
            $products = TenantDatabase::fetchAllTenant(
                "SELECT p.*, pc.name as category_name
                 FROM products p
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 WHERE {$whereClause}
                 ORDER BY p.name
                 LIMIT ? OFFSET ?",
                array_merge($params, [$perPage, $offset])
            );

            $this->jsonResponse([
                'success' => true,
                'data' => $products,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/products/{id}
     * Get single product
     */
    public function show(int $id): void
    {
        try {
            ApiAuthController::requirePermission('products.read');

            $product = TenantDatabase::fetchOneTenant(
                "SELECT p.*, pc.name as category_name
                 FROM products p
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 WHERE p.id = ?",
                [$id]
            );

            if (!$product) {
                $this->jsonResponse(['error' => 'Product not found'], 404);
                return;
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $product
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/products
     * Create new product
     */
    public function store(): void
    {
        try {
            ApiAuthController::requirePermission('products.write');

            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            $errors = $this->validateProduct($input);
            if (!empty($errors)) {
                $this->jsonResponse(['error' => 'Validation failed', 'details' => $errors], 400);
                return;
            }

            // Create product
            $productId = TenantDatabase::insertTenant('products', [
                'name' => $input['name'],
                'sku' => $input['sku'],
                'description' => $input['description'] ?? null,
                'category_id' => $input['category_id'] ?? null,
                'price' => $input['price'],
                'cost' => $input['cost'] ?? 0,
                'stock_quantity' => $input['stock_quantity'] ?? 0,
                'low_stock_threshold' => $input['low_stock_threshold'] ?? 5,
                'barcode' => $input['barcode'] ?? null,
                'is_active' => $input['is_active'] ?? 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Get created product
            $product = TenantDatabase::fetchOneTenant(
                "SELECT * FROM products WHERE id = ?",
                [$productId]
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/v1/products/{id}
     * Update product
     */
    public function update(int $id): void
    {
        try {
            ApiAuthController::requirePermission('products.write');

            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            // Check if product exists
            $existing = TenantDatabase::fetchOneTenant(
                "SELECT id FROM products WHERE id = ?",
                [$id]
            );

            if (!$existing) {
                $this->jsonResponse(['error' => 'Product not found'], 404);
                return;
            }

            // Build update data
            $updateData = array_filter([
                'name' => $input['name'] ?? null,
                'sku' => $input['sku'] ?? null,
                'description' => $input['description'] ?? null,
                'category_id' => $input['category_id'] ?? null,
                'price' => $input['price'] ?? null,
                'cost' => $input['cost'] ?? null,
                'stock_quantity' => $input['stock_quantity'] ?? null,
                'low_stock_threshold' => $input['low_stock_threshold'] ?? null,
                'barcode' => $input['barcode'] ?? null,
                'is_active' => $input['is_active'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ], fn($value) => $value !== null);

            if (empty($updateData)) {
                $this->jsonResponse(['error' => 'No data to update'], 400);
                return;
            }

            // Update product
            TenantDatabase::updateTenant('products', $updateData, 'id = ?', [$id]);

            // Get updated product
            $product = TenantDatabase::fetchOneTenant(
                "SELECT * FROM products WHERE id = ?",
                [$id]
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/v1/products/{id}
     * Delete product (soft delete)
     */
    public function destroy(int $id): void
    {
        try {
            ApiAuthController::requirePermission('products.delete');

            // Check if product exists
            $product = TenantDatabase::fetchOneTenant(
                "SELECT id, name FROM products WHERE id = ?",
                [$id]
            );

            if (!$product) {
                $this->jsonResponse(['error' => 'Product not found'], 404);
                return;
            }

            // Soft delete
            TenantDatabase::updateTenant('products', [
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/products/{id}/stock
     * Update product stock
     */
    public function updateStock(int $id): void
    {
        try {
            ApiAuthController::requirePermission('products.write');

            $input = json_decode(file_get_contents('php://input'), true);

            $quantity = $input['quantity'] ?? null;
            $reason = $input['reason'] ?? 'API adjustment';
            $type = $input['type'] ?? 'adjustment'; // adjustment, restock, sale, damage

            if ($quantity === null) {
                $this->jsonResponse(['error' => 'Quantity is required'], 400);
                return;
            }

            // Get current stock
            $product = TenantDatabase::fetchOneTenant(
                "SELECT stock_quantity FROM products WHERE id = ?",
                [$id]
            );

            if (!$product) {
                $this->jsonResponse(['error' => 'Product not found'], 404);
                return;
            }

            $oldQuantity = $product['stock_quantity'];
            $newQuantity = $oldQuantity + $quantity;

            // Update stock
            TenantDatabase::updateTenant('products', [
                'stock_quantity' => max(0, $newQuantity),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);

            // Log inventory adjustment
            TenantDatabase::insertTenant('inventory_adjustments', [
                'product_id' => $id,
                'adjustment_type' => $type,
                'quantity_change' => $quantity,
                'quantity_before' => $oldQuantity,
                'quantity_after' => $newQuantity,
                'reason' => $reason,
                'adjusted_by' => $_SESSION['user_id'] ?? null,
                'adjusted_at' => date('Y-m-d H:i:s')
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Stock updated successfully',
                'data' => [
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'change' => $quantity
                ]
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/products/low-stock
     * Get low stock products
     */
    public function lowStock(): void
    {
        try {
            ApiAuthController::requirePermission('products.read');

            $products = TenantDatabase::fetchAllTenant(
                "SELECT p.*, pc.name as category_name
                 FROM products p
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 WHERE p.stock_quantity <= p.low_stock_threshold
                 AND p.is_active = 1
                 ORDER BY p.stock_quantity ASC"
            );

            $this->jsonResponse([
                'success' => true,
                'count' => count($products),
                'data' => $products
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validate product data
     */
    private function validateProduct(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Name is required';
        }

        if (empty($data['sku'])) {
            $errors[] = 'SKU is required';
        }

        if (!isset($data['price']) || $data['price'] < 0) {
            $errors[] = 'Valid price is required';
        }

        // Check for duplicate SKU
        if (!empty($data['sku'])) {
            $existing = TenantDatabase::fetchOneTenant(
                "SELECT id FROM products WHERE sku = ? AND id != ?",
                [$data['sku'], $data['id'] ?? 0]
            );

            if ($existing) {
                $errors[] = 'SKU already exists';
            }
        }

        return $errors;
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
