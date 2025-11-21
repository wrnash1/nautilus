<?php

namespace App\Controllers\CMS;

use App\Core\Auth;
use App\Services\CMS\BlogService;

class BlogController
{
    private $blogService;

    public function __construct()
    {
        $this->blogService = new BlogService();
    }

    /**
     * Display blog posts list
     */
    public function index()
    {
        if (!Auth::hasPermission('cms.view')) {
            redirect('/store');
            return;
        }

        $posts = $this->blogService->getAllPosts();
        $pageTitle = 'Blog Posts';

        ob_start();
        require __DIR__ . '/../../Views/cms/blog/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Show create post form
     */
    public function create()
    {
        if (!Auth::hasPermission('cms.create')) {
            redirect('/store');
            return;
        }

        $categories = $this->blogService->getAllCategories();
        $tags = $this->blogService->getAllTags();
        $pageTitle = 'Create Blog Post';

        ob_start();
        require __DIR__ . '/../../Views/cms/blog/create.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Store new post
     */
    public function store()
    {
        if (!Auth::hasPermission('cms.create')) {
            redirect('/store');
            return;
        }

        $data = [
            'title' => $_POST['title'] ?? '',
            'slug' => $_POST['slug'] ?? '',
            'excerpt' => $_POST['excerpt'] ?? '',
            'content' => $_POST['content'] ?? '',
            'featured_image' => $_POST['featured_image'] ?? null,
            'meta_description' => $_POST['meta_description'] ?? '',
            'meta_keywords' => $_POST['meta_keywords'] ?? '',
            'status' => $_POST['status'] ?? 'draft',
            'author_id' => Auth::id()
        ];

        $categories = $_POST['categories'] ?? [];
        $tags = $_POST['tags'] ?? [];

        $postId = $this->blogService->createPost($data, $categories, $tags);

        if ($postId) {
            $_SESSION['success'] = 'Blog post created successfully.';
            redirect('/store/cms/blog/' . $postId);
        } else {
            $_SESSION['error'] = 'Failed to create blog post.';
            redirect('/store/cms/blog/create');
        }
    }

    /**
     * Display post
     */
    public function show($id)
    {
        if (!Auth::hasPermission('cms.view')) {
            redirect('/store');
            return;
        }

        $post = $this->blogService->getPostById($id);
        if (!$post) {
            $_SESSION['error'] = 'Blog post not found.';
            redirect('/store/cms/blog');
            return;
        }

        $categories = $this->blogService->getPostCategories($id);
        $tags = $this->blogService->getPostTags($id);
        $pageTitle = $post['title'];

        ob_start();
        require __DIR__ . '/../../Views/cms/blog/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Show edit post form
     */
    public function edit($id)
    {
        if (!Auth::hasPermission('cms.edit')) {
            redirect('/store');
            return;
        }

        $post = $this->blogService->getPostById($id);
        if (!$post) {
            $_SESSION['error'] = 'Blog post not found.';
            redirect('/store/cms/blog');
            return;
        }

        $allCategories = $this->blogService->getAllCategories();
        $allTags = $this->blogService->getAllTags();
        $postCategories = array_column($this->blogService->getPostCategories($id), 'id');
        $postTags = array_column($this->blogService->getPostTags($id), 'id');
        $pageTitle = 'Edit Blog Post';

        ob_start();
        require __DIR__ . '/../../Views/cms/blog/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Update post
     */
    public function update($id)
    {
        if (!Auth::hasPermission('cms.edit')) {
            redirect('/store');
            return;
        }

        $data = [
            'title' => $_POST['title'] ?? '',
            'slug' => $_POST['slug'] ?? '',
            'excerpt' => $_POST['excerpt'] ?? '',
            'content' => $_POST['content'] ?? '',
            'featured_image' => $_POST['featured_image'] ?? null,
            'meta_description' => $_POST['meta_description'] ?? '',
            'meta_keywords' => $_POST['meta_keywords'] ?? '',
            'status' => $_POST['status'] ?? 'draft'
        ];

        $categories = $_POST['categories'] ?? [];
        $tags = $_POST['tags'] ?? [];

        $success = $this->blogService->updatePost($id, $data, $categories, $tags);

        if ($success) {
            $_SESSION['success'] = 'Blog post updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update blog post.';
        }

        redirect('/store/cms/blog/' . $id);
    }

    /**
     * Delete post
     */
    public function delete($id)
    {
        if (!Auth::hasPermission('cms.delete')) {
            redirect('/store');
            return;
        }

        $success = $this->blogService->deletePost($id);

        if ($success) {
            $_SESSION['success'] = 'Blog post deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete blog post.';
        }

        redirect('/store/cms/blog');
    }

    /**
     * Publish post
     */
    public function publish($id)
    {
        if (!Auth::hasPermission('cms.edit')) {
            redirect('/store');
            return;
        }

        $success = $this->blogService->publishPost($id);

        if ($success) {
            $_SESSION['success'] = 'Blog post published successfully.';
        } else {
            $_SESSION['error'] = 'Failed to publish blog post.';
        }

        redirect('/store/cms/blog/' . $id);
    }

    /**
     * Manage categories
     */
    public function categories()
    {
        if (!Auth::hasPermission('cms.view')) {
            redirect('/store');
            return;
        }

        $categories = $this->blogService->getAllCategories();
        $pageTitle = 'Blog Categories';

        ob_start();
        require __DIR__ . '/../../Views/cms/blog/categories.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Manage tags
     */
    public function tags()
    {
        if (!Auth::hasPermission('cms.view')) {
            redirect('/store');
            return;
        }

        $tags = $this->blogService->getAllTags();
        $pageTitle = 'Blog Tags';

        ob_start();
        require __DIR__ . '/../../Views/cms/blog/tags.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }
}
