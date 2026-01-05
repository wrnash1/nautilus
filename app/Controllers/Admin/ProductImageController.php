<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\ProductImageService;

/**
 * ProductImageController
 * 
 * Handles AI-powered product image management
 */
class ProductImageController extends Controller
{
    private ProductImageService $imageService;

    public function __construct()
    {
        $this->imageService = new ProductImageService();
    }

    /**
     * Display image manager dashboard
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePermission('products.edit');

        $missingProducts = $this->imageService->getProductsWithoutImages();
        $missingCount = count($missingProducts);

        // Get total products count
        $totalProducts = \App\Core\Database::fetchOne("SELECT COUNT(*) as cnt FROM products")['cnt'] ?? 0;
        $productsWithImages = $totalProducts - $missingCount;

        // Placeholder for shared library count
        $sharedLibraryCount = 0;

        require __DIR__ . '/../../Views/admin/products/images.php';
    }

    /**
     * Find image for a product via AI search
     */
    public function findImage(): void
    {
        $this->requireAuth();
        $this->requirePermission('products.edit');

        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $productId = (int) ($input['product_id'] ?? 0);
        $query = $input['query'] ?? '';

        if (!$productId) {
            echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
            return;
        }

        try {
            // Get product details
            $product = \App\Core\Database::fetchOne(
                "SELECT * FROM products WHERE id = ?",
                [$productId]
            );

            if (!$product) {
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                return;
            }

            // Try shared library first
            $imageUrl = $this->imageService->searchSharedLibrary($product['name'], $product['sku'] ?? '');

            // If not found, use placeholder or web search
            if (!$imageUrl) {
                $imageUrl = $this->imageService->searchProductImage($product);
            }

            if ($imageUrl) {
                $this->imageService->updateProductImage($productId, $imageUrl);
                echo json_encode([
                    'success' => true,
                    'image_url' => $imageUrl,
                    'message' => 'Image found and saved'
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No suitable image found']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Upload custom image for product
     */
    public function uploadImage(): void
    {
        $this->requireAuth();
        $this->requirePermission('products.edit');

        header('Content-Type: application/json');

        $productId = (int) ($_POST['product_id'] ?? 0);

        if (!$productId) {
            echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
            return;
        }

        try {
            // Handle file upload
            if (!empty($_FILES['image']['tmp_name'])) {
                $uploadDir = BASE_PATH . '/public/assets/img/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = "product_{$productId}." . strtolower($extension);
                $filepath = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                    $imageUrl = "/assets/img/products/{$filename}";
                    $this->imageService->updateProductImage($productId, $imageUrl);

                    echo json_encode([
                        'success' => true,
                        'image_url' => $imageUrl,
                        'message' => 'Image uploaded successfully'
                    ]);
                    return;
                }
            }

            // Handle URL upload
            if (!empty($_POST['image_url'])) {
                $imageUrl = $this->imageService->downloadAndSaveImage(
                    $_POST['image_url'],
                    $productId
                );

                if ($imageUrl) {
                    echo json_encode([
                        'success' => true,
                        'image_url' => $imageUrl,
                        'message' => 'Image downloaded and saved'
                    ]);
                    return;
                }
            }

            echo json_encode(['success' => false, 'error' => 'No valid image provided']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Batch process multiple products
     */
    public function batchProcess(): void
    {
        $this->requireAuth();
        $this->requirePermission('products.edit');

        header('Content-Type: application/json');

        $limit = (int) ($_POST['limit'] ?? 20);

        try {
            $results = $this->imageService->processMissingImages($limit);
            echo json_encode([
                'success' => true,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
