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
        $this->productService->updateStock($product['id'], 20, 'restock');

        $stmt = $this->db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $stmt->execute([$product['id']]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals(120, $result['stock_quantity']);

        // Subtract stock
        $this->productService->updateStock($product['id'], -30, 'adjustment');

        $stmt = $this->db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $stmt->execute([$product['id']]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals(90, $result['stock_quantity']);
    }

    public function testCheckLowStock(): void
    {
        // Create products with varying stock levels
        $this->createTestProduct(['name' => 'Low Stock Item 1', 'stock_quantity' => 5, 'low_stock_threshold' => 10]);
        $this->createTestProduct(['name' => 'Low Stock Item 2', 'stock_quantity' => 3, 'low_stock_threshold' => 10]);
        $this->createTestProduct(['name' => 'Normal Stock Item', 'stock_quantity' => 50, 'low_stock_threshold' => 10]);

        $lowStockProducts = $this->productService->checkLowStock();

        $this->assertIsArray($lowStockProducts);
        $this->assertGreaterThanOrEqual(2, count($lowStockProducts));
    }
}
