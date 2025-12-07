<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

class StorefrontConfigController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
    }

    /**
     * Storefront Configuration Dashboard
     */
    public function index(): void
    {
        // Get carousel slides
        $carouselSlides = Database::fetchAll("
            SELECT * FROM storefront_carousel_slides
            ORDER BY display_order ASC
        ") ?? [];

        // Get service boxes
        $serviceBoxes = Database::fetchAll("
            SELECT * FROM storefront_service_boxes
            ORDER BY display_order ASC
        ") ?? [];

        $data = [
            'carousel_slides' => $carouselSlides,
            'service_boxes' => $serviceBoxes,
            'page_title' => 'Storefront Configuration'
        ];

        $this->view('admin/storefront/index', $data);
    }

    /**
     * Carousel Slides Management
     */
    public function carouselSlides(): void
    {
        $slides = Database::fetchAll("
            SELECT * FROM storefront_carousel_slides
            ORDER BY display_order ASC
        ") ?? [];

        $data = [
            'slides' => $slides,
            'page_title' => 'Carousel Slides'
        ];

        $this->view('admin/storefront/carousel', $data);
    }

    /**
     * Create Carousel Slide
     */
    public function createCarouselSlide(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreateCarouselSlide();
            return;
        }

        $data = [
            'page_title' => 'Add Carousel Slide'
        ];

        $this->view('admin/storefront/carousel-form', $data);
    }

    /**
     * Edit Carousel Slide
     */
    public function editCarouselSlide(int $id): void
    {
        $slide = Database::fetchOne("SELECT * FROM storefront_carousel_slides WHERE id = ?", [$id]);

        if (!$slide) {
            $this->redirect('/admin/storefront/carousel?error=Slide not found');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdateCarouselSlide($id);
            return;
        }

        $data = [
            'slide' => $slide,
            'page_title' => 'Edit Carousel Slide'
        ];

        $this->view('admin/storefront/carousel-form', $data);
    }

    /**
     * Delete Carousel Slide
     */
    public function deleteCarouselSlide(int $id): void
    {
        Database::query("DELETE FROM storefront_carousel_slides WHERE id = ?", [$id]);
        $this->redirect('/admin/storefront/carousel?success=Slide deleted');
    }

    /**
     * Service Boxes Management
     */
    public function serviceBoxes(): void
    {
        $boxes = Database::fetchAll("
            SELECT * FROM storefront_service_boxes
            ORDER BY display_order ASC
        ") ?? [];

        $data = [
            'boxes' => $boxes,
            'page_title' => 'Service Boxes'
        ];

        $this->view('admin/storefront/service-boxes', $data);
    }

    /**
     * Create Service Box
     */
    public function createServiceBox(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreateServiceBox();
            return;
        }

        $data = [
            'page_title' => 'Add Service Box'
        ];

        $this->view('admin/storefront/service-box-form', $data);
    }

    /**
     * Edit Service Box
     */
    public function editServiceBox(int $id): void
    {
        $box = Database::fetchOne("SELECT * FROM storefront_service_boxes WHERE id = ?", [$id]);

        if (!$box) {
            $this->redirect('/admin/storefront/service-boxes?error=Service box not found');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdateServiceBox($id);
            return;
        }

        $data = [
            'box' => $box,
            'page_title' => 'Edit Service Box'
        ];

        $this->view('admin/storefront/service-box-form', $data);
    }

    /**
     * Delete Service Box
     */
    public function deleteServiceBox(int $id): void
    {
        Database::query("DELETE FROM storefront_service_boxes WHERE id = ?", [$id]);
        $this->redirect('/admin/storefront/service-boxes?success=Service box deleted');
    }

    // Private handler methods

    private function handleCreateCarouselSlide(): void
    {
        Database::query("
            INSERT INTO storefront_carousel_slides
            (tenant_id, title, description, image_url, button_text, button_link, display_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $_SESSION['tenant_id'] ?? 1,
            $_POST['title'],
            $_POST['description'] ?? null,
            $_POST['image_url'],
            $_POST['button_text'] ?? null,
            $_POST['button_link'] ?? null,
            $_POST['display_order'] ?? 0,
            isset($_POST['is_active']) ? 1 : 0
        ]);

        $this->redirect('/admin/storefront/carousel?success=Slide created');
    }

    private function handleUpdateCarouselSlide(int $id): void
    {
        Database::query("
            UPDATE storefront_carousel_slides
            SET title = ?, description = ?, image_url = ?, button_text = ?,
                button_link = ?, display_order = ?, is_active = ?
            WHERE id = ?
        ", [
            $_POST['title'],
            $_POST['description'] ?? null,
            $_POST['image_url'],
            $_POST['button_text'] ?? null,
            $_POST['button_link'] ?? null,
            $_POST['display_order'] ?? 0,
            isset($_POST['is_active']) ? 1 : 0,
            $id
        ]);

        $this->redirect('/admin/storefront/carousel?success=Slide updated');
    }

    private function handleCreateServiceBox(): void
    {
        Database::query("
            INSERT INTO storefront_service_boxes
            (tenant_id, icon, title, description, image, link, display_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $_SESSION['tenant_id'] ?? 1,
            $_POST['icon'],
            $_POST['title'],
            $_POST['description'],
            $_POST['image'],
            $_POST['link'],
            $_POST['display_order'] ?? 0,
            isset($_POST['is_active']) ? 1 : 0
        ]);

        $this->redirect('/admin/storefront/service-boxes?success=Service box created');
    }

    private function handleUpdateServiceBox(int $id): void
    {
        Database::query("
            UPDATE storefront_service_boxes
            SET icon = ?, title = ?, description = ?, image = ?,
                link = ?, display_order = ?, is_active = ?
            WHERE id = ?
        ", [
            $_POST['icon'],
            $_POST['title'],
            $_POST['description'],
            $_POST['image'],
            $_POST['link'],
            $_POST['display_order'] ?? 0,
            isset($_POST['is_active']) ? 1 : 0,
            $id
        ]);

        $this->redirect('/admin/storefront/service-boxes?success=Service box updated');
    }
}
