<?php

namespace App\Controllers\Admin\Storefront;

use App\Core\Controller;
use App\Core\Database;

class ServiceBoxController extends Controller
{
    /**
     * List all service boxes
     */
    public function index(): void
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->query("
            SELECT * FROM storefront_service_boxes 
            ORDER BY display_order ASC, id DESC
        ");
        $boxes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [
            'boxes' => $boxes,
            'page_title' => 'Manage Service Boxes'
        ];

        $this->render('admin/storefront/service-boxes/index', $data);
    }

    /**
     * Create new service box
     */
    public function create(): void
    {
        $data = ['page_title' => 'Add Service Box'];
        $this->render('admin/storefront/service-boxes/create', $data);
    }

    /**
     * Store new service box
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/storefront/service-boxes');
            return;
        }

        $db = Database::getInstance()->getConnection();

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $icon = $_POST['icon'] ?? 'bi bi-star';
        $link = $_POST['link'] ?? '';
        $displayOrder = (int) ($_POST['display_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $db->prepare("
            INSERT INTO storefront_service_boxes 
            (title, description, icon, link, display_order, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([$title, $description, $icon, $link, $displayOrder, $isActive]);

        $_SESSION['success'] = 'Service box created successfully';
        redirect('/store/storefront/service-boxes');
    }

    /**
     * Edit service box
     */
    public function edit(int $id): void
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM storefront_service_boxes WHERE id = ?");
        $stmt->execute([$id]);
        $box = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$box) {
            $_SESSION['error'] = 'Service box not found';
            redirect('/store/storefront/service-boxes');
            return;
        }

        $data = [
            'box' => $box,
            'page_title' => 'Edit Service Box'
        ];

        $this->render('admin/storefront/service-boxes/edit', $data);
    }

    /**
     * Update service box
     */
    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/storefront/service-boxes');
            return;
        }

        $db = Database::getInstance()->getConnection();

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $icon = $_POST['icon'] ?? 'bi bi-star';
        $link = $_POST['link'] ?? '';
        $displayOrder = (int) ($_POST['display_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $db->prepare("
            UPDATE storefront_service_boxes 
            SET title = ?, description = ?, icon = ?, link = ?, 
                display_order = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$title, $description, $icon, $link, $displayOrder, $isActive, $id]);

        $_SESSION['success'] = 'Service box updated successfully';
        redirect('/store/storefront/service-boxes');
    }

    /**
     * Delete service box
     */
    public function delete(int $id): void
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("DELETE FROM storefront_service_boxes WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['success'] = 'Service box deleted successfully';
        redirect('/store/storefront/service-boxes');
    }
}
