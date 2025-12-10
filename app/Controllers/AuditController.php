<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\Audit\AuditTrailService;

/**
 * Audit Controller
 *
 * Handles audit trail viewing and management
 */
class AuditController extends Controller
{
    private AuditTrailService $auditService;

    public function __construct()
    {
        parent::__construct();
        $this->auditService = new AuditTrailService();
    }

    /**
     * View audit trail
     */
    public function index(): void
    {
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'entity_type' => $_GET['entity_type'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'limit' => (int)($_GET['limit'] ?? 100),
            'offset' => (int)($_GET['offset'] ?? 0)
        ];

        $result = $this->auditService->getAuditTrail($filters);

        $this->json($result);
    }

    /**
     * Get audit trail for specific entity
     */
    public function entityHistory(): void
    {
        $entityType = $_GET['entity_type'] ?? '';
        $entityId = (int)($_GET['entity_id'] ?? 0);

        if (empty($entityType) || empty($entityId)) {
            $this->json(['success' => false, 'error' => 'Entity type and ID required'], 400);
            return;
        }

        $result = $this->auditService->getEntityAuditTrail($entityType, $entityId);

        $this->json($result);
    }

    /**
     * Get audit statistics
     */
    public function statistics(): void
    {
        $days = (int)($_GET['days'] ?? 30);

        $result = $this->auditService->getAuditStatistics(['days' => $days]);

        $this->json($result);
    }

    /**
     * Get security events
     */
    public function securityEvents(): void
    {
        $filters = [
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'user_id' => $_GET['user_id'] ?? null,
            'limit' => (int)($_GET['limit'] ?? 100),
            'offset' => (int)($_GET['offset'] ?? 0)
        ];

        $result = $this->auditService->getSecurityEvents($filters);

        $this->json($result);
    }

    /**
     * Get failed login attempts
     */
    public function failedLogins(): void
    {
        $hours = (int)($_GET['hours'] ?? 24);

        $result = $this->auditService->getFailedLoginAttempts($hours);

        $this->json($result);
    }

    /**
     * Get user activity summary
     */
    public function userActivity(): void
    {
        $userId = (int)($_GET['user_id'] ?? 0);
        $days = (int)($_GET['days'] ?? 30);

        if (empty($userId)) {
            $this->json(['success' => false, 'error' => 'User ID required'], 400);
            return;
        }

        $result = $this->auditService->getUserActivitySummary($userId, $days);

        $this->json($result);
    }

    /**
     * Export audit trail to CSV
     */
    public function export(): void
    {
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'entity_type' => $_GET['entity_type'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];

        $result = $this->auditService->exportAuditTrail($filters);

        if ($result['success']) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
            readfile($result['filepath']);
            unlink($result['filepath']);
            exit;
        } else {
            $this->json($result, 500);
        }
    }
}
