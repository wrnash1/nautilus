<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\Dashboard\DashboardWidgetService;
use App\Services\Auth\PermissionService;

/**
 * Dashboard Controller
 *
 * Handles dashboard and widget management
 */
class DashboardController extends Controller
{
    private DashboardWidgetService $widgetService;
    private PermissionService $permissionService;

    public function __construct()
    {
        parent::__construct();
        $this->widgetService = new DashboardWidgetService();
        $this->permissionService = new PermissionService();
    }

    /**
     * Display main dashboard
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePermission('dashboard.view');

        $userId = $_SESSION['user_id'];
        $result = $this->widgetService->getUserDashboard($userId);

        if (!$result['success']) {
            $this->renderError($result['error']);
            return;
        }

        $this->render('dashboard/index', [
            'page_title' => 'Dashboard',
            'widgets' => $result['widgets']
        ]);
    }

    /**
     * Get dashboard data (AJAX)
     */
    public function getData(): void
    {
        $this->requireAuth();
        $this->requirePermission('dashboard.view');

        $userId = $_SESSION['user_id'];
        $result = $this->widgetService->getUserDashboard($userId);

        $this->jsonResponse($result);
    }

    /**
     * Add widget to dashboard
     */
    public function addWidget(): void
    {
        $this->requireAuth();
        $this->requirePermission('dashboard.view');

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['widget_code'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Widget code required'], 400);
            return;
        }

        $userId = $_SESSION['user_id'];
        $settings = $data['settings'] ?? [];

        $result = $this->widgetService->addWidget($userId, $data['widget_code'], $settings);

        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }

    /**
     * Update widget settings
     */
    public function updateWidget(): void
    {
        $this->requireAuth();
        $this->requirePermission('dashboard.view');

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['widget_id'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Widget ID required'], 400);
            return;
        }

        $result = $this->widgetService->updateWidget($data['widget_id'], $data['settings'] ?? []);

        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }

    /**
     * Remove widget from dashboard
     */
    public function removeWidget(): void
    {
        $this->requireAuth();
        $this->requirePermission('dashboard.view');

        $widgetId = $_GET['widget_id'] ?? null;

        if (!$widgetId) {
            $this->jsonResponse(['success' => false, 'error' => 'Widget ID required'], 400);
            return;
        }

        $userId = $_SESSION['user_id'];
        $result = $this->widgetService->removeWidget($widgetId, $userId);

        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }

    /**
     * Reorder widgets
     */
    public function reorderWidgets(): void
    {
        $this->requireAuth();
        $this->requirePermission('dashboard.view');

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['widget_order']) || !is_array($data['widget_order'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Widget order array required'], 400);
            return;
        }

        $userId = $_SESSION['user_id'];
        $result = $this->widgetService->reorderWidgets($userId, $data['widget_order']);

        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }
}
