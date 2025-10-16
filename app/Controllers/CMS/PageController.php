<?php

namespace App\Controllers\CMS;

use App\Core\Auth;
use App\Services\CMS\PageService;

class PageController
{
    private $pageService;

    public function __construct()
    {
        $this->pageService = new PageService();
    }

    /**
     * Display pages list
     */
    public function index()
    {
        if (!Auth::hasPermission('cms.view')) {
            redirect('/dashboard');
            return;
        }

        $pages = $this->pageService->getAllPages();
        $pageTitle = 'Pages';

        ob_start();
        require __DIR__ . '/../../Views/cms/pages/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Show create page form
     */
    public function create()
    {
        if (!Auth::hasPermission('cms.create')) {
            redirect('/dashboard');
            return;
        }

        $pageTitle = 'Create Page';

        ob_start();
        require __DIR__ . '/../../Views/cms/pages/create.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Store new page
     */
    public function store()
    {
        if (!Auth::hasPermission('cms.create')) {
            redirect('/dashboard');
            return;
        }

        $data = [
            'title' => $_POST['title'] ?? '',
            'slug' => $_POST['slug'] ?? '',
            'content' => $_POST['content'] ?? '',
            'meta_description' => $_POST['meta_description'] ?? '',
            'meta_keywords' => $_POST['meta_keywords'] ?? '',
            'status' => $_POST['status'] ?? 'draft',
            'is_homepage' => isset($_POST['is_homepage']) ? 1 : 0,
            'created_by' => Auth::id()
        ];

        $pageId = $this->pageService->createPage($data);

        if ($pageId) {
            $_SESSION['success'] = 'Page created successfully.';
            redirect('/cms/pages/' . $pageId);
        } else {
            $_SESSION['error'] = 'Failed to create page.';
            redirect('/cms/pages/create');
        }
    }

    /**
     * Display page
     */
    public function show($id)
    {
        if (!Auth::hasPermission('cms.view')) {
            redirect('/dashboard');
            return;
        }

        $page = $this->pageService->getPageById($id);
        if (!$page) {
            $_SESSION['error'] = 'Page not found.';
            redirect('/cms/pages');
            return;
        }

        $pageTitle = $page['title'];

        ob_start();
        require __DIR__ . '/../../Views/cms/pages/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Show edit page form
     */
    public function edit($id)
    {
        if (!Auth::hasPermission('cms.edit')) {
            redirect('/dashboard');
            return;
        }

        $page = $this->pageService->getPageById($id);
        if (!$page) {
            $_SESSION['error'] = 'Page not found.';
            redirect('/cms/pages');
            return;
        }

        $pageTitle = 'Edit Page';

        ob_start();
        require __DIR__ . '/../../Views/cms/pages/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Update page
     */
    public function update($id)
    {
        if (!Auth::hasPermission('cms.edit')) {
            redirect('/dashboard');
            return;
        }

        $data = [
            'title' => $_POST['title'] ?? '',
            'slug' => $_POST['slug'] ?? '',
            'content' => $_POST['content'] ?? '',
            'meta_description' => $_POST['meta_description'] ?? '',
            'meta_keywords' => $_POST['meta_keywords'] ?? '',
            'status' => $_POST['status'] ?? 'draft',
            'is_homepage' => isset($_POST['is_homepage']) ? 1 : 0
        ];

        $success = $this->pageService->updatePage($id, $data);

        if ($success) {
            $_SESSION['success'] = 'Page updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update page.';
        }

        redirect('/cms/pages/' . $id);
    }

    /**
     * Delete page
     */
    public function delete($id)
    {
        if (!Auth::hasPermission('cms.delete')) {
            redirect('/dashboard');
            return;
        }

        $success = $this->pageService->deletePage($id);

        if ($success) {
            $_SESSION['success'] = 'Page deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete page.';
        }

        redirect('/cms/pages');
    }

    /**
     * Publish page
     */
    public function publish($id)
    {
        if (!Auth::hasPermission('cms.edit')) {
            redirect('/dashboard');
            return;
        }

        $success = $this->pageService->publishPage($id);

        if ($success) {
            $_SESSION['success'] = 'Page published successfully.';
        } else {
            $_SESSION['error'] = 'Failed to publish page.';
        }

        redirect('/cms/pages/' . $id);
    }
}
