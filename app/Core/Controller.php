<?php

namespace App\Core;

use PDO;

/**
 * Base Controller Class
 * Provides common functionality for controllers
 */
class Controller
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Check if user has permission
     */
    protected function checkPermission(string $permission): void
    {
        if (!hasPermission($permission)) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
            exit;
        }
    }

    /**
     * Render a view
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data);

        // Convert view path to file path
        $viewPath = str_replace('.', '/', $view);
        $file = BASE_PATH . '/app/Views/' . $viewPath . '.php';

        if (!file_exists($file)) {
            throw new \Exception("View not found: {$view}");
        }

        // Set page title if provided
        $pageTitle = $data['title'] ?? 'Page';

        // Start output buffering for the view
        ob_start();
        require $file;
        $content = ob_get_clean();

        // Load layout
        require BASE_PATH . '/app/Views/layouts/app.php';
    }

    /**
     * Get all vendors from database
     */
    protected function getVendors(): array
    {
        $stmt = $this->db->query("
            SELECT id, name, code
            FROM vendors
            WHERE is_active = 1
            ORDER BY name
        ");
        return $stmt->fetchAll();
    }

    /**
     * JSON response helper
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect helper
     */
    protected function redirect(string $url): void
    {
        redirect($url);
        exit;
    }
}
