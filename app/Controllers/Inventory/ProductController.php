<?php

namespace App\Controllers\Inventory;

use App\Services\Inventory\ProductService;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;

class ProductController
{
    private ProductService $productService;

    public function __construct()
    {
        $this->productService = new ProductService();
    }

    public function index()
    {
        if (!hasPermission('products.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $page = (int) ($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $search = sanitizeInput($_GET['search'] ?? '');

        if (!empty($search)) {
            $products = Product::search($search, $limit);
            $total = count($products);
        } else {
            $products = Product::limit($limit)->offset($offset)->get();
            $total = Product::count();
        }

        $categories = Category::where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get();
        $totalPages = ceil($total / $limit);

        require __DIR__ . '/../../Views/products/index.php';
    }

    public function create()
    {
        if (!hasPermission('products.create')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/store/products');
        }

        $categories = Category::where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get();
        $vendors = Vendor::where('is_active', 1)
            ->orderBy('vendor_name', 'ASC')
            ->get();

        require __DIR__ . '/../../Views/products/create.php';
    }

    public function store()
    {
        if (!hasPermission('products.create')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $data = [
                'category_id' => !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null,
                'vendor_id' => !empty($_POST['vendor_id']) ? (int) $_POST['vendor_id'] : null,
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'sku' => sanitizeInput($_POST['sku'] ?? ''),
                'barcode' => sanitizeInput($_POST['barcode'] ?? ''),
                'qr_code' => sanitizeInput($_POST['qr_code'] ?? ''),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'cost_price' => (float) ($_POST['cost_price'] ?? 0),
                'retail_price' => (float) ($_POST['retail_price'] ?? 0),
                'weight' => !empty($_POST['weight']) ? (float) $_POST['weight'] : null,
                'weight_unit' => sanitizeInput($_POST['weight_unit'] ?? 'lb'),
                'dimensions' => sanitizeInput($_POST['dimensions'] ?? ''),
                'color' => sanitizeInput($_POST['color'] ?? ''),
                'material' => sanitizeInput($_POST['material'] ?? ''),
                'manufacturer' => sanitizeInput($_POST['manufacturer'] ?? ''),
                'warranty_info' => sanitizeInput($_POST['warranty_info'] ?? ''),
                'location_in_store' => sanitizeInput($_POST['location_in_store'] ?? ''),
                'supplier_info' => sanitizeInput($_POST['supplier_info'] ?? ''),
                'expiration_date' => !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null,
                'stock_quantity' => (int) ($_POST['stock_quantity'] ?? 0),
                'low_stock_threshold' => (int) ($_POST['low_stock_threshold'] ?? 5),
                'track_inventory' => isset($_POST['track_inventory']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $productId = $this->productService->createProduct($data);

            $_SESSION['flash_success'] = 'Product created successfully';
            redirect("/store/products/{$productId}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/store/products/create');
        }
    }

    public function show(int $id)
    {
        if (!hasPermission('products.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $product = Product::find($id);

        if (!$product) {
            $_SESSION['flash_error'] = 'Product not found';
            redirect('/store/products');
        }

        $transactions = Product::getInventoryTransactions($id);

        require __DIR__ . '/../../Views/products/show.php';
    }

    public function edit(int $id)
    {
        if (!hasPermission('products.edit')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/store/products');
        }

        $product = Product::find($id);

        if (!$product) {
            $_SESSION['flash_error'] = 'Product not found';
            redirect('/store/products');
        }

        $categories = Category::where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get();
        $vendors = Vendor::where('is_active', 1)
            ->orderBy('vendor_name', 'ASC')
            ->get();

        require __DIR__ . '/../../Views/products/edit.php';
    }

    public function update(int $id)
    {
        if (!hasPermission('products.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $data = [
                'category_id' => !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null,
                'vendor_id' => !empty($_POST['vendor_id']) ? (int) $_POST['vendor_id'] : null,
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'sku' => sanitizeInput($_POST['sku'] ?? ''),
                'barcode' => sanitizeInput($_POST['barcode'] ?? ''),
                'qr_code' => sanitizeInput($_POST['qr_code'] ?? ''),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'cost_price' => (float) ($_POST['cost_price'] ?? 0),
                'retail_price' => (float) ($_POST['retail_price'] ?? 0),
                'weight' => !empty($_POST['weight']) ? (float) $_POST['weight'] : null,
                'weight_unit' => sanitizeInput($_POST['weight_unit'] ?? 'lb'),
                'dimensions' => sanitizeInput($_POST['dimensions'] ?? ''),
                'color' => sanitizeInput($_POST['color'] ?? ''),
                'material' => sanitizeInput($_POST['material'] ?? ''),
                'manufacturer' => sanitizeInput($_POST['manufacturer'] ?? ''),
                'warranty_info' => sanitizeInput($_POST['warranty_info'] ?? ''),
                'location_in_store' => sanitizeInput($_POST['location_in_store'] ?? ''),
                'supplier_info' => sanitizeInput($_POST['supplier_info'] ?? ''),
                'expiration_date' => !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null,
                'stock_quantity' => (int) ($_POST['stock_quantity'] ?? 0),
                'low_stock_threshold' => (int) ($_POST['low_stock_threshold'] ?? 5),
                'track_inventory' => isset($_POST['track_inventory']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $this->productService->updateProduct($id, $data);

            $_SESSION['flash_success'] = 'Product updated successfully';
            redirect("/store/products/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/store/products/{$id}/edit");
        }
    }

    public function delete(int $id)
    {
        if (!hasPermission('products.delete')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/store/products');
        }

        Product::destroy($id);

        $_SESSION['flash_success'] = 'Product deleted successfully';
        redirect('/store/products');
    }

    public function search()
    {
        if (!hasPermission('products.view')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        $query = sanitizeInput($_GET['q'] ?? '');

        if (empty($query)) {
            jsonResponse([]);
        }

        $products = Product::search($query);
        jsonResponse($products);
    }

    public function adjustStock(int $id)
    {
        if (!hasPermission('products.adjust_stock')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $quantity = (int) ($_POST['quantity'] ?? 0);
            $reason = sanitizeInput($_POST['reason'] ?? '');

            if ($quantity == 0) {
                throw new \Exception('Quantity cannot be zero');
            }

            $this->productService->updateStock($id, $quantity, $reason);

            $_SESSION['flash_success'] = 'Stock adjusted successfully';
            redirect("/store/products/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/store/products/{$id}");
        }
    }
}
