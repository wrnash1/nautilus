<?php

namespace App\Core;

/**
 * View Class
 * Handles view rendering with layout support
 */
class View
{
    private string $layout = 'layouts/app';
    private array $layoutData = [];
    private string $content = '';

    /**
     * Set the layout for this view
     */
    public function layout(string $layout, array $data = []): void
    {
        $this->layout = $layout;
        $this->layoutData = $data;
    }

    /**
     * Render a view file
     */
    public function render(string $viewFile, array $data = []): string
    {
        // Extract data to local scope
        extract($data, EXTR_SKIP);

        // Start output buffering
        ob_start();

        // Include the view file - $this is available in the view
        require $viewFile;

        // Get the content
        $this->content = ob_get_clean();

        return $this->content;
    }

    /**
     * Render with layout
     */
    public function renderWithLayout(string $viewFile, array $data = []): void
    {
        // First render the view content
        $content = $this->render($viewFile, $data);

        // Merge layout data with view data
        $pageTitle = $this->layoutData['title'] ?? $data['title'] ?? 'Page';
        $activeMenu = $data['activeMenu'] ?? '';

        // Now include the layout
        $layoutFile = BASE_PATH . '/app/Views/' . $this->layout . '.php';

        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            // Fallback to default layout
            require BASE_PATH . '/app/Views/layouts/app.php';
        }
    }

    /**
     * Get the rendered content
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
