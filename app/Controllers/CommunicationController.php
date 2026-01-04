<?php

namespace App\Controllers;

use App\Services\Communication\CommunicationService;
use App\Services\Audit\AuditService;
use App\Helpers\Auth;

/**
 * Communication Controller
 *
 * Handles SMS, push notifications, and bulk messaging
 */
class CommunicationController
{
    private CommunicationService $communicationService;
    private AuditService $auditService;

    public function __construct()
    {
        $this->communicationService = new CommunicationService();
        $this->auditService = new AuditService();
    }

    /**
     * Show communication dashboard
     */
    public function index()
    {
        if (!Auth::check() || !Auth::hasPermission('communication.view')) {
            redirect('/login');
            return;
        }

        $campaigns = $this->communicationService->getAllCampaigns(20);

        require __DIR__ . '/../Views/communication/index.php';
    }

    /**
     * Show send message form
     */
    public function create()
    {
        if (!Auth::check() || !Auth::hasPermission('communication.send')) {
            redirect('/login');
            return;
        }

        require __DIR__ . '/../Views/communication/create.php';
    }

    /**
     * Send individual message
     */
    public function send()
    {
        if (!Auth::check() || !Auth::hasPermission('communication.send')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $customerId = (int)($_POST['customer_id'] ?? 0);
        $type = $_POST['type'] ?? 'sms';
        $message = $_POST['message'] ?? '';

        if (!$customerId || !$message) {
            return $this->jsonResponse(['success' => false, 'error' => 'Customer ID and message required'], 400);
        }

        $success = false;

        if ($type === 'sms') {
            $success = $this->communicationService->sendSMS($customerId, $message);
        } elseif ($type === 'push') {
            $title = $_POST['title'] ?? 'Notification';
            $data = isset($_POST['data']) ? json_decode($_POST['data'], true) : null;
            $success = $this->communicationService->sendPushNotification($customerId, $title, $message, $data);
        }

        if ($success) {
            // Log action
            $this->auditService->log(
                Auth::userId(),
                'send',
                'communication',
                null,
                ['type' => $type, 'customer_id' => $customerId]
            );

            return $this->jsonResponse(['success' => true, 'message' => ucfirst($type) . ' sent successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to send message'], 500);
        }
    }

    /**
     * Send bulk messages
     */
    public function sendBulk()
    {
        if (!Auth::check() || !Auth::hasPermission('communication.send_bulk')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $customerIds = $input['customer_ids'] ?? [];
        $type = $input['type'] ?? 'sms';
        $message = $input['message'] ?? '';
        $campaignName = $input['campaign_name'] ?? '';

        if (empty($customerIds) || !$message) {
            return $this->jsonResponse(['success' => false, 'error' => 'Customer IDs and message required'], 400);
        }

        $results = [];

        if ($type === 'sms') {
            $results = $this->communicationService->sendBulkSMS($customerIds, $message, $campaignName);
        } elseif ($type === 'push') {
            $title = $input['title'] ?? 'Notification';
            $data = $input['data'] ?? null;
            $results = $this->communicationService->sendBulkPush($customerIds, $title, $message, $data, $campaignName);
        }

        // Log bulk send
        $this->auditService->log(
            Auth::userId(),
            'bulk_send',
            'communication',
            null,
            [
                'type' => $type,
                'campaign_name' => $campaignName,
                'total' => $results['total'] ?? 0,
                'sent' => $results['sent'] ?? 0,
                'failed' => $results['failed'] ?? 0
            ]
        );

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Bulk messages sent',
            'results' => $results
        ]);
    }

    /**
     * Get customer message history
     */
    public function history(int $customerId)
    {
        if (!Auth::check() || !Auth::hasPermission('communication.view')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $history = $this->communicationService->getMessageHistory($customerId, 50);

        return $this->jsonResponse(['success' => true, 'history' => $history]);
    }

    /**
     * Get campaign details
     */
    public function campaign(int $campaignId)
    {
        if (!Auth::check() || !Auth::hasPermission('communication.view')) {
            redirect('/login');
            return;
        }

        $campaign = $this->communicationService->getCampaignStats($campaignId);

        if (!$campaign) {
            $_SESSION['error'] = 'Campaign not found';
            redirect('/communication');
            return;
        }

        require __DIR__ . '/../Views/communication/campaign.php';
    }

    /**
     * Update customer communication preferences
     */
    public function updatePreferences(int $customerId)
    {
        if (!Auth::check()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        // Check if user is updating their own preferences or has permission
        if (Auth::userId() !== $customerId && !Auth::hasPermission('customers.update')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $preferences = [
            'sms_opt_in' => $input['sms_opt_in'] ?? null,
            'email_opt_in' => $input['email_opt_in'] ?? null,
            'push_opt_in' => $input['push_opt_in'] ?? null
        ];

        // Remove null values
        $preferences = array_filter($preferences, fn($v) => $v !== null);

        $success = $this->communicationService->updatePreferences($customerId, $preferences);

        if ($success) {
            return $this->jsonResponse(['success' => true, 'message' => 'Preferences updated successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to update preferences'], 500);
        }
    }

    /**
     * Get customer preferences
     */
    public function getPreferences(int $customerId)
    {
        if (!Auth::check()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        // Check if user is viewing their own preferences or has permission
        if (Auth::userId() !== $customerId && !Auth::hasPermission('customers.view')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $preferences = $this->communicationService->getPreferences($customerId);

        return $this->jsonResponse(['success' => true, 'preferences' => $preferences]);
    }

    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
