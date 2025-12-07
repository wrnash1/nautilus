<?php

namespace App\Controllers\Dashboard;

use App\Services\Dashboard\WidgetService;
use App\Helpers\Auth;

/**
 * Dashboard Widget Controller
 *
 * Handles dashboard customization and widget management
 */
class WidgetController
{
    private WidgetService $widgetService;

    public function __construct()
    {
        $this->widgetService = new WidgetService();
    }

    /**
     * Show widget customization page
     */
    public function customize()
    {
        if (!Auth::check()) {
            redirect('/login');
            return;
        }

        $userId = Auth::userId();
        $currentLayout = $this->widgetService->getUserLayout($userId);
        $availableWidgets = $this->widgetService->getAvailableWidgets();
        $categories = $this->widgetService->getCategories();

        require __DIR__ . '/../../Views/dashboard/customize.php';
    }

    /**
     * Save dashboard layout
     */
    public function saveLayout()
    {
        if (!Auth::check()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $userId = Auth::userId();
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['widgets']) || !is_array($input['widgets'])) {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid widget data'], 400);
        }

        $success = $this->widgetService->saveUserLayout($userId, $input['widgets']);

        if ($success) {
            return $this->jsonResponse(['success' => true, 'message' => 'Layout saved successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to save layout'], 500);
        }
    }

    /**
     * Reset layout to default
     */
    public function resetLayout()
    {
        if (!Auth::check()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $userId = Auth::userId();
        $success = $this->widgetService->resetToDefault($userId);

        if ($success) {
            $defaultLayout = $this->widgetService->getDefaultLayout();
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Layout reset to default',
                'layout' => $defaultLayout
            ]);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to reset layout'], 500);
        }
    }

    /**
     * Add widget to dashboard
     */
    public function addWidget()
    {
        if (!Auth::check()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $userId = Auth::userId();
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['widget_id'])) {
            return $this->jsonResponse(['success' => false, 'error' => 'Widget ID required'], 400);
        }

        $success = $this->widgetService->addWidget(
            $userId,
            $input['widget_id'],
            $input['position'] ?? null
        );

        if ($success) {
            return $this->jsonResponse(['success' => true, 'message' => 'Widget added successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to add widget'], 500);
        }
    }

    /**
     * Remove widget from dashboard
     */
    public function removeWidget()
    {
        if (!Auth::check()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $userId = Auth::userId();
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['widget_id'])) {
            return $this->jsonResponse(['success' => false, 'error' => 'Widget ID required'], 400);
        }

        $success = $this->widgetService->removeWidget($userId, $input['widget_id']);

        if ($success) {
            return $this->jsonResponse(['success' => true, 'message' => 'Widget removed successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to remove widget'], 500);
        }
    }

    /**
     * Update widget configuration
     */
    public function updateConfig()
    {
        if (!Auth::check()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $userId = Auth::userId();
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['widget_id']) || !isset($input['config'])) {
            return $this->jsonResponse(['success' => false, 'error' => 'Widget ID and config required'], 400);
        }

        $success = $this->widgetService->updateWidgetConfig(
            $userId,
            $input['widget_id'],
            $input['config']
        );

        if ($success) {
            return $this->jsonResponse(['success' => true, 'message' => 'Widget configuration updated']);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to update configuration'], 500);
        }
    }

    /**
     * Get widget data (for AJAX refresh)
     */
    public function getWidgetData(string $widgetId)
    {
        if (!Auth::check()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        // Get widget configuration for current user
        $userId = Auth::userId();
        $layout = $this->widgetService->getUserLayout($userId);

        $widgetConfig = [];
        foreach ($layout as $widget) {
            if ($widget['widget_id'] === $widgetId) {
                $widgetConfig = $widget['config'];
                break;
            }
        }

        // Fetch widget data based on widget ID
        $data = $this->fetchWidgetData($widgetId, $widgetConfig);

        return $this->jsonResponse(['success' => true, 'data' => $data]);
    }

    /**
     * Fetch widget data based on widget type
     */
    private function fetchWidgetData(string $widgetId, array $config): array
    {
        // This would integrate with existing DashboardController methods
        // For now, return placeholder
        return [
            'widget_id' => $widgetId,
            'timestamp' => time(),
            'config' => $config
        ];
    }

    /**
     * Helper to send JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
