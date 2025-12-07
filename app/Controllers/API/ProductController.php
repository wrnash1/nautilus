<?php

namespace App\Controllers\API;

use App\Services\Inventory\ProductService;

class ProductController
{
    private $productService;
    
    public function __construct()
    {
        $this->productService = new ProductService();
    }
    
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $categoryId = $_GET['category_id'] ?? null;
        
        $products = $this->productService->getAllProducts($page, $limit, $categoryId);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $products]);
    }
    
    public function show($id)
    {
        $product = $this->productService->getProductById($id);
        
        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found', 'message' => 'Product not found']);
            return;
        }
        
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $product]);
    }
    
    public function store()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $productId = $this->productService->createProduct($input);
        
        if ($productId) {
            $product = $this->productService->getProductById($productId);
            http_response_code(201);
            echo json_encode(['success' => true, 'data' => $product]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'Failed to create product']);
        }
    }
    
    public function update($id)
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $success = $this->productService->updateProduct($id, $input);
        
        if ($success) {
            $product = $this->productService->getProductById($id);
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $product]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'Failed to update product']);
        }
    }
    
    public function destroy($id)
    {
        $success = $this->productService->deleteProduct($id);
        
        if ($success) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'Failed to delete product']);
        }
    }
}
