<?php

namespace Tests\Unit\Services\Inventory;

use Tests\TestCase;
use App\Services\Inventory\ProductService;

class ProductServiceTest extends TestCase
{
    private ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = new ProductService();
    }

    public function testCreateProduct(): void
    {
        $productData = [
            'name' => 'Scuba Mask',
            'sku' => 'MASK-001',
            'category_id' => 1,
            'price' => 79.99,
            'cost' => 40.00,
            'stock_quantity' => 50
        ];

        $productId = $this->productService->createProduct($productData);

        $this->assertIsInt($productId);
        $this->assertGreaterThan(0, $productId);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'sku' => 'MASK-001'
        ]);
    }

    public function testUpdateStock(): void
    {
        $product = $this->createTestProduct(['stock_quantity' => 100]);

        // Add stock
        $this->productService->updateStock($product['id'], 20, 'add');

        $updatedProduct = $this->productService->getProductById($product['id']);
        $this->assertEquals(120, $updatedProduct['stock_quantity']);

        // Subtract stock
        $this->productService->updateStock($product['id'], 30, 'subtract');

        $updatedProduct = $this->productService->getProductById($product['id']);
        $this->assertEquals(90, $updatedProduct['stock_quantity']);
    }

    public function testGetLowStockProducts(): void
    {
        // Create products with varying stock levels
        $this->createTestProduct(['name' => 'Low Stock Item 1', 'stock_quantity' => 5, 'low_stock_threshold' => 10]);
        $this->createTestProduct(['name' => 'Low Stock Item 2', 'stock_quantity' => 3, 'low_stock_threshold' => 10]);
        $this->createTestProduct(['name' => 'Normal Stock Item', 'stock_quantity' => 50, 'low_stock_threshold' => 10]);

        $lowStockProducts = $this->productService->getLowStockProducts();

        $this->assertIsArray($lowStockProducts);
        $this->assertGreaterThanOrEqual(2, count($lowStockProducts));
    }

    public function testCalculateInventoryValue(): void
    {
        // Create products with known costs
        $this->createTestProduct(['cost' => 50.00, 'stock_quantity' => 10]);
        $this->createTestProduct(['cost' => 100.00, 'stock_quantity' => 5]);

        $totalValue = $this->productService->calculateInventoryValue();

        // 50*10 + 100*5 = 500 + 500 = 1000
        $this->assertGreaterThanOrEqual(1000, $totalValue);
    }
}
