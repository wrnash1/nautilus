<?php

namespace App\Controllers\CRM;

use App\Core\Database;
use PDO;

class CustomerTagController
{
    /**
     * List all customer tags
     */
    public function index()
    {
        if (!hasPermission('customers.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/store/customers');
        }

        $db = Database::getInstance();
        $tags = [];

        try {
            $stmt = $db->query("SELECT * FROM customer_tags ORDER BY display_order, name");
            $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get tag usage counts
            foreach ($tags as &$tag) {
                try {
                    $stmt = $db->getConnection()->prepare("SELECT COUNT(*) as count FROM customer_tag_assignments WHERE tag_id = ?");
                    $stmt->execute([$tag['id']]);
                    $tag['customer_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                } catch (\PDOException $e) {
                    $tag['customer_count'] = 0;
                }
            }
        } catch (\PDOException $e) {
            // Table might not exist yet
        }

        // Set variables for layout
        $pageTitle = 'Customer Tags';
        $activeMenu = 'customers';
        $user = \App\Core\Auth::user();

        // Start output buffering for the view
        ob_start();
        require __DIR__ . '/../../Views/customers/tags/index.php';
        $content = ob_get_clean();

        // Load layout
        require BASE_PATH . '/app/Views/layouts/app.php';
    }

    /**
     * Get list of tags for AJAX
     */
    public function list()
    {
        if (!hasPermission('customers.view')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        $db = Database::getInstance();
        try {
            $stmt = $db->query("SELECT id, name, color, icon FROM customer_tags WHERE is_active = 1 ORDER BY name");
            jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            jsonResponse([], 500);
        }
    }


    /**
     * Show create tag form
     */
    public function create()
    {
        if (!hasPermission('customers.edit')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/store/customers/tags');
        }

        $pageTitle = 'Create Customer Tag';
        $activeMenu = 'customers';

        ob_start();
        require __DIR__ . '/../../Views/customers/tags/create.php';
        $content = ob_get_clean();

        require BASE_PATH . '/app/Views/layouts/app.php';
    }

    /**
     * Store new tag
     */
    public function store()
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $name = sanitizeInput($_POST['name'] ?? '');
            $slug = strtolower(str_replace(' ', '-', $name));
            $color = sanitizeInput($_POST['color'] ?? '#3498db');
            $icon = sanitizeInput($_POST['icon'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');

            if (empty($name)) {
                throw new \Exception('Tag name is required');
            }

            $db = Database::getInstance();
            $stmt = $db->getConnection()->prepare("
                INSERT INTO customer_tags (name, slug, color, icon, description, created_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $slug, $color, $icon, $description, $_SESSION['user_id']]);

            $_SESSION['flash_success'] = 'Tag created successfully';
            redirect('/store/customers/tags');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/store/customers/tags/create');
        }
    }

    /**
     * Assign tag to customer
     */
    public function assignToCustomer(int $id)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $tagId = (int)($_POST['tag_id'] ?? 0);
            $notes = sanitizeInput($_POST['notes'] ?? '');

            if (empty($tagId)) {
                throw new \Exception('Tag is required');
            }

            $db = Database::getInstance();
            $stmt = $db->getConnection()->prepare("
                INSERT INTO customer_tag_assignments (customer_id, tag_id, assigned_by, notes)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE notes = VALUES(notes)
            ");
            $stmt->execute([$id, $tagId, $_SESSION['user_id'], $notes]);

            jsonResponse(['success' => true, 'message' => 'Tag assigned successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove tag from customer
     */
    public function removeFromCustomer(int $id, int $tagId)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->getConnection()->prepare("DELETE FROM customer_tag_assignments WHERE customer_id = ? AND tag_id = ?");
            $stmt->execute([$id, $tagId]);

            jsonResponse(['success' => true, 'message' => 'Tag removed successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get customer's tags
     */
    public function getCustomerTags(int $id)
    {
        $db = Database::getInstance();
        $stmt = $db->getConnection()->prepare("
            SELECT t.*, cta.assigned_at, cta.notes,
                   CONCAT(u.first_name, ' ', u.last_name) as assigned_by_name
            FROM customer_tag_assignments cta
            INNER JOIN customer_tags t ON cta.tag_id = t.id
            LEFT JOIN users u ON cta.assigned_by = u.id
            WHERE cta.customer_id = ?
            ORDER BY t.display_order, t.name
        ");
        $stmt->execute([$id]);

        jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Update tag
     */
    public function update(int $id)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $name = sanitizeInput($_POST['name'] ?? '');
            $color = sanitizeInput($_POST['color'] ?? '#3498db');
            $icon = sanitizeInput($_POST['icon'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if (empty($name)) {
                throw new \Exception('Tag name is required');
            }

            $db = Database::getInstance();
            $stmt = $db->getConnection()->prepare("
                UPDATE customer_tags
                SET name = ?, color = ?, icon = ?, description = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $color, $icon, $description, $isActive, $id]);

            $_SESSION['flash_success'] = 'Tag updated successfully';
            redirect('/store/customers/tags');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/store/customers/tags');
        }
    }

    /**
     * Delete tag
     */
    public function delete(int $id)
    {
        if (!hasPermission('customers.delete')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = Database::getInstance();

            // Check if tag is in use
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM customer_tag_assignments WHERE tag_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($count > 0) {
                throw new \Exception("Cannot delete tag that is assigned to {$count} customer(s)");
            }

            $stmt = $db->prepare("DELETE FROM customer_tags WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['flash_success'] = 'Tag deleted successfully';
            redirect('/store/customers/tags');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/store/customers/tags');
        }
    }
}
