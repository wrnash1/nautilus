<?php

namespace App\Controllers\Inventory;

use App\Models\Category;

class CategoryController
{
    public function index()
    {
        if (!hasPermission('categories.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $categories = Category::where('is_active', 1)->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->get();

        require __DIR__ . '/../../Views/categories/index.php';
    }

    public function create()
    {
        if (!hasPermission('categories.create')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/categories');
        }

        $categories = Category::where('is_active', 1)->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->get();

        require __DIR__ . '/../../Views/categories/create.php';
    }

    public function store()
    {
        if (!hasPermission('categories.create')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $data = [
                'parent_id' => !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null,
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'] ?? ''), '-')),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if (empty($data['name'])) {
                throw new \Exception('Category name is required');
            }

            $category = Category::create($data);
            logActivity('create', 'categories', $category->id);

            $_SESSION['flash_success'] = 'Category created successfully';
            redirect('/categories');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/categories/create');
        }
    }

    public function edit(int $id)
    {
        if (!hasPermission('categories.edit')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/categories');
        }

        $category = Category::find($id);
        $categories = Category::where('is_active', 1)->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->get();

        if (!$category) {
            $_SESSION['flash_error'] = 'Category not found';
            redirect('/categories');
        }

        require __DIR__ . '/../../Views/categories/edit.php';
    }

    public function update(int $id)
    {
        if (!hasPermission('categories.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $data = [
                'parent_id' => !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null,
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'] ?? ''), '-')),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if (empty($data['name'])) {
                throw new \Exception('Category name is required');
            }

            $category = Category::findOrFail($id);
            $category->update($data);
            logActivity('update', 'categories', $id);

            $_SESSION['flash_success'] = 'Category updated successfully';
            redirect('/categories');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/categories/{$id}/edit");
        }
    }

    public function delete(int $id)
    {
        if (!hasPermission('categories.delete')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/categories');
        }

        $category = Category::findOrFail($id);
        $category->update(['is_active' => 0]);
        logActivity('delete', 'categories', $id);

        $_SESSION['flash_success'] = 'Category deleted successfully';
        redirect('/categories');
    }
}
