<?php

namespace App\Controllers\Admin\Storefront;

use App\Core\Controller;
use App\Core\Database;

class CarouselController extends Controller
{
    /**
     * List all carousel slides
     */
    public function index(): void
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->query("
            SELECT * FROM storefront_carousel_slides 
            ORDER BY display_order ASC, id DESC
        ");
        $slides = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [
            'slides' => $slides,
            'page_title' => 'Manage Carousel Slides'
        ];

        $this->render('admin/storefront/carousel/index', $data);
    }

    /**
     * Create new carousel slide
     */
    public function create(): void
    {
        $data = ['page_title' => 'Add Carousel Slide'];
        $this->render('admin/storefront/carousel/create', $data);
    }

    /**
     * Store new carousel slide
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/storefront/carousel');
            return;
        }

        $db = Database::getInstance()->getConnection();

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $imageUrl = $_POST['image_url'] ?? '';
        $buttonText = $_POST['button_text'] ?? '';
        $buttonLink = $_POST['button_link'] ?? '';
        $displayOrder = (int) ($_POST['display_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $db->prepare("
            INSERT INTO storefront_carousel_slides 
            (title, description, image_url, button_text, button_link, display_order, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([$title, $description, $imageUrl, $buttonText, $buttonLink, $displayOrder, $isActive]);

        $_SESSION['success'] = 'Carousel slide created successfully';
        redirect('/store/storefront/carousel');
    }

    /**
     * Edit carousel slide
     */
    public function edit(int $id): void
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM storefront_carousel_slides WHERE id = ?");
        $stmt->execute([$id]);
        $slide = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$slide) {
            $_SESSION['error'] = 'Slide not found';
            redirect('/store/storefront/carousel');
            return;
        }

        $data = [
            'slide' => $slide,
            'page_title' => 'Edit Carousel Slide'
        ];

        $this->render('admin/storefront/carousel/edit', $data);
    }

    /**
     * Update carousel slide
     */
    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/storefront/carousel');
            return;
        }

        $db = Database::getInstance()->getConnection();

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $imageUrl = $_POST['image_url'] ?? '';
        $buttonText = $_POST['button_text'] ?? '';
        $buttonLink = $_POST['button_link'] ?? '';
        $displayOrder = (int) ($_POST['display_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $db->prepare("
            UPDATE storefront_carousel_slides 
            SET title = ?, description = ?, image_url = ?, button_text = ?, 
                button_link = ?, display_order = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$title, $description, $imageUrl, $buttonText, $buttonLink, $displayOrder, $isActive, $id]);

        $_SESSION['success'] = 'Carousel slide updated successfully';
        redirect('/store/storefront/carousel');
    }

    /**
     * Delete carousel slide
     */
    public function delete(int $id): void
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("DELETE FROM storefront_carousel_slides WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['success'] = 'Carousel slide deleted successfully';
        redirect('/store/storefront/carousel');
    }
}
