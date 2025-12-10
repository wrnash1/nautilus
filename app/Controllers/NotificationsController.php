<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Services\Notifications\NotificationService;

class NotificationsController
{
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    /**
     * Display all notifications for the current user
     */
    public function index()
    {
        $userId = Auth::userId();

        if (!$userId) {
            header('Location: /store/login');
            exit;
        }

        $showUnreadOnly = isset($_GET['unread']) && $_GET['unread'] === '1';

        $notifications = $this->notificationService->getForUser($userId, $showUnreadOnly);
        $unreadCount = $this->notificationService->getUnreadCount($userId);

        require __DIR__ . '/../Views/notifications/index.php';
    }

    /**
     * Get notifications as JSON (for AJAX)
     */
    public function getNotifications()
    {
        $userId = Auth::userId();

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $unreadOnly = isset($_GET['unread']) && $_GET['unread'] === '1';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

        $notifications = $this->notificationService->getForUser($userId, $unreadOnly, $limit);
        $unreadCount = $this->notificationService->getUnreadCount($userId);

        header('Content-Type: application/json');
        echo json_encode([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'total_count' => count($notifications)
        ]);
    }

    /**
     * Get unread count (for badge)
     */
    public function getUnreadCount()
    {
        $userId = Auth::userId();

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $count = $this->notificationService->getUnreadCount($userId);

        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(int $id)
    {
        $userId = Auth::userId();

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $success = $this->notificationService->markAsRead($id, $userId);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
        } else {
            // If not AJAX, redirect back
            header('Location: /store/notifications');
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $userId = Auth::userId();

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $count = $this->notificationService->markAllAsRead($userId);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'count' => $count
            ]);
        } else {
            $_SESSION['flash_success'] = "$count notification(s) marked as read";
            header('Location: /store/notifications');
        }
    }

    /**
     * Delete a notification
     */
    public function delete(int $id)
    {
        $userId = Auth::userId();

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        // Verify CSRF token for non-GET requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                return;
            }
        }

        $success = $this->notificationService->delete($id, $userId);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
        } else {
            if ($success) {
                $_SESSION['flash_success'] = 'Notification deleted';
            } else {
                $_SESSION['flash_error'] = 'Failed to delete notification';
            }
            header('Location: /store/notifications');
        }
    }

    /**
     * Cleanup old notifications (admin only)
     */
    public function cleanup()
    {
        // Check admin permission
        if (!Auth::hasPermission('admin.settings')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
        $count = $this->notificationService->deleteOldRead($days);

        $_SESSION['flash_success'] = "Cleaned up $count old notification(s)";
        header('Location: /store/notifications');
    }
}
