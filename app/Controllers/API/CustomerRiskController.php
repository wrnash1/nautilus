<?php

namespace App\Controllers\API;

use App\Core\Controller;
use App\Services\CustomerRiskService;

/**
 * CustomerRiskController
 * 
 * API endpoints for customer risk scoring
 */
class CustomerRiskController extends Controller
{
    private CustomerRiskService $riskService;

    public function __construct()
    {
        $this->riskService = new CustomerRiskService();
    }

    /**
     * Get customer risk scores for POS display
     */
    public function getScore(int $id): void
    {
        $this->requireAuth();

        try {
            $scores = $this->riskService->getCustomerRiskSummary($id);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $scores
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Flag a customer as risky
     */
    public function flag(int $id): void
    {
        $this->requireAuth();
        $this->requirePermission('customers.edit');

        $reason = $_POST['reason'] ?? 'Flagged by staff';
        $userId = currentUser()['id'] ?? 0;

        try {
            $this->riskService->flagCustomer($id, $reason, $userId);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Customer flagged successfully'
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Unflag a customer
     */
    public function unflag(int $id): void
    {
        $this->requireAuth();
        $this->requirePermission('customers.edit');

        try {
            $this->riskService->unflagCustomer($id);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Customer unflagged successfully'
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
