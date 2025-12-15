<?php

namespace App\Controllers\Admin\Storefront;

use App\Services\Storefront\StorefrontSettingsService;

class AnnouncementController
{
    private $settingsService;

    public function __construct()
    {
        $this->settingsService = new StorefrontSettingsService();
    }

    /**
     * List all announcements
     */
    public function index()
    {
        $announcements = $this->settingsService->getAllBanners();
        ob_start();
        require __DIR__ . '/../../../Views/admin/storefront/announcements/index.php';
        $content = ob_get_clean();
        $pageTitle = 'Announcements';
        require __DIR__ . '/../../../Views/layouts/admin.php';
    }

    /**
     * Show create form
     */
    public function create()
    {
        ob_start();
        require __DIR__ . '/../../../Views/admin/storefront/announcements/create.php';
        $content = ob_get_clean();
        $pageTitle = 'Create Announcement';
        require __DIR__ . '/../../../Views/layouts/admin.php';
    }

    /**
     * Store new announcement
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/storefront/announcements');
            exit;
        }

        // Validate
        if (empty($_POST['content'])) {
            $_SESSION['error'] = 'Content is required';
            header('Location: /admin/storefront/announcements/create');
            exit;
        }

        $data = [
            'title' => $_POST['title'] ?? null,
            'content' => $_POST['content'],
            'banner_type' => $_POST['banner_type'] ?? 'info',
            'button_text' => $_POST['button_text'] ?? null,
            'button_url' => $_POST['button_url'] ?? null,
            'is_active' => isset($_POST['is_active']),
            'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            'show_on_pages' => isset($_POST['show_on_pages']) ? $_POST['show_on_pages'] : ['all'], // Default to all if not set, logic handled in view
            'display_order' => (int)($_POST['display_order'] ?? 0)
        ];

        if ($this->settingsService->createBanner($data)) {
            $_SESSION['success'] = 'Announcement created successfully';
        } else {
            $_SESSION['error'] = 'Failed to create announcement';
        }

        header('Location: /admin/storefront/announcements');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $announcement = $this->settingsService->getBanner($id);
        
        if (!$announcement) {
            header('Location: /admin/storefront/announcements');
            exit;
        }

        ob_start();
        require __DIR__ . '/../../../Views/admin/storefront/announcements/edit.php';
        $content = ob_get_clean();
        $pageTitle = 'Edit Announcement';
        require __DIR__ . '/../../../Views/layouts/admin.php';
    }

    /**
     * Update announcement
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/storefront/announcements');
            exit;
        }

        $data = [
            'title' => $_POST['title'] ?? null,
            'content' => $_POST['content'],
            'banner_type' => $_POST['banner_type'] ?? 'info',
            'button_text' => $_POST['button_text'] ?? null,
            'button_url' => $_POST['button_url'] ?? null,
            'is_active' => isset($_POST['is_active']),
            'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            'display_order' => (int)($_POST['display_order'] ?? 0)
        ];

        if ($this->settingsService->updateBanner($id, $data)) {
            $_SESSION['success'] = 'Announcement updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update announcement';
        }

        header('Location: /admin/storefront/announcements');
    }

    /**
     * Delete announcement
     */
    public function delete($id)
    {
        if ($this->settingsService->deleteBanner($id)) {
            $_SESSION['success'] = 'Announcement deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete announcement';
        }

        header('Location: /admin/storefront/announcements');
    }
}
