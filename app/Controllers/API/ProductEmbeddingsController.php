<?php

namespace App\Controllers\API;

use App\Core\Database;
use App\Core\Auth;

class ProductEmbeddingsController
{
    /**
     * Get all product embeddings for AI search
     * Returns optimized payload with only necessary data
     */
    public function getEmbeddings()
    {
        header('Content-Type: application/json');

        try {
            $db = Database::getInstance();

            // Get product embeddings with product info
            $stmt = $db->query("
                SELECT
                    pe.product_id,
                    pe.embedding_vector,
                    p.name,
                    p.sku,
                    p.retail_price as price,
                    p.category,
                    pe.image_path
                FROM product_image_embeddings pe
                INNER JOIN products p ON pe.product_id = p.id
                WHERE p.is_active = 1
                AND p.visual_search_enabled = 1
                ORDER BY p.name
            ");

            $embeddings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Decode JSON embedding vectors
            foreach ($embeddings as &$embedding) {
                $embedding['embedding_vector'] = json_decode($embedding['embedding_vector']);
            }

            echo json_encode($embeddings);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Failed to fetch embeddings',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Save product embedding
     */
    public function saveEmbedding()
    {
        if (!Auth::hasPermission('inventory.edit')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['product_id']) || !isset($data['embedding_vector'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                return;
            }

            $db = Database::getInstance();

            // Check if embedding already exists for this product/image
            $stmt = $db->prepare("
                SELECT id FROM product_image_embeddings
                WHERE product_id = ? AND image_path = ?
            ");
            $stmt->execute([$data['product_id'], $data['image_path'] ?? '']);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                // Update existing
                $stmt = $db->prepare("
                    UPDATE product_image_embeddings
                    SET embedding_vector = ?,
                        embedding_quality_score = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    json_encode($data['embedding_vector']),
                    $data['embedding_quality_score'] ?? 1.0,
                    $existing['id']
                ]);
            } else {
                // Insert new
                $stmt = $db->prepare("
                    INSERT INTO product_image_embeddings
                    (product_id, image_path, embedding_vector, embedding_model, image_angle, embedding_quality_score)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $data['product_id'],
                    $data['image_path'] ?? '',
                    json_encode($data['embedding_vector']),
                    $data['embedding_model'] ?? 'mobilenet_v2',
                    $data['image_angle'] ?? 'front',
                    $data['embedding_quality_score'] ?? 1.0
                ]);
            }

            // Update product's last embedding generated timestamp
            $stmt = $db->prepare("
                UPDATE products
                SET last_embedding_generated = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$data['product_id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Embedding saved successfully'
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Failed to save embedding',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log visual search history
     */
    public function logSearch()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $db = Database::getInstance();
            $userId = Auth::id();

            $stmt = $db->prepare("
                INSERT INTO visual_search_history
                (user_id, top_result_product_id, similarity_score, results_count, search_time_ms)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $userId,
                $data['top_result_product_id'] ?? null,
                $data['similarity_score'] ?? null,
                $data['results_count'] ?? 0,
                $data['search_time_ms'] ?? null
            ]);

            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            // Don't fail the request if logging fails
            http_response_code(200);
            echo json_encode(['success' => false, 'logged' => false]);
        }
    }

    /**
     * Get products without embeddings (for admin tool)
     */
    public function getProductsWithoutEmbeddings()
    {
        if (!Auth::hasPermission('inventory.view')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        header('Content-Type: application/json');

        try {
            $db = Database::getInstance();

            $stmt = $db->query("
                SELECT
                    p.id,
                    p.name,
                    p.sku,
                    p.primary_image_path,
                    p.category
                FROM products p
                LEFT JOIN product_image_embeddings pe ON p.id = pe.product_id
                WHERE p.is_active = 1
                AND p.visual_search_enabled = 1
                AND pe.id IS NULL
                ORDER BY p.name
                LIMIT 100
            ");

            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode($products);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Failed to fetch products',
                'message' => $e->getMessage()
            ]);
        }
    }
}
