<?php

namespace App\Controllers;

use App\Services\Loyalty\LoyaltyService;
use App\Services\Audit\AuditService;
use App\Helpers\Auth;

/**
 * Loyalty Program Controller
 *
 * Manages customer loyalty and rewards program
 */
class LoyaltyController
{
    private LoyaltyService $loyaltyService;
    private AuditService $auditService;

    public function __construct()
    {
        $this->loyaltyService = new LoyaltyService();
        $this->auditService = new AuditService();
    }

    /**
     * Show loyalty program dashboard
     */
    public function index()
    {
        if (!Auth::check() || !Auth::hasPermission('loyalty.view')) {
            redirect('/login');
            return;
        }

        $statistics = $this->loyaltyService->getStatistics();

        require __DIR__ . '/../Views/loyalty/index.php';
    }

    /**
     * Show customer loyalty details
     */
    public function customerDetails(int $customerId)
    {
        if (!Auth::check() || !Auth::hasPermission('loyalty.view')) {
            redirect('/login');
            return;
        }

        $tierDetails = $this->loyaltyService->getTierDetails($customerId);
        $history = $this->loyaltyService->getTransactionHistory($customerId, 100);
        $expiringPoints = $this->loyaltyService->getExpiringPoints($customerId, 30);
        $availableRewards = $this->loyaltyService->getAvailableRewards($customerId);

        require __DIR__ . '/../Views/loyalty/customer_details.php';
    }

    /**
     * Award manual points
     */
    public function awardPoints()
    {
        if (!Auth::check() || !Auth::hasPermission('loyalty.award_points')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $customerId = (int)$_POST['customer_id'];
        $points = (int)$_POST['points'];
        $reason = $_POST['reason'] ?? 'Manual adjustment';

        if (!$customerId || !$points) {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid data'], 400);
        }

        // Use the addPoints method through a public wrapper
        $stmt = $this->loyaltyService->getTransactionHistory($customerId, 1); // Verify customer exists

        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                "INSERT INTO loyalty_transactions
                 (customer_id, points, transaction_type, description, created_at)
                 VALUES (?, ?, 'manual', ?, NOW())"
            );
            $stmt->execute([$customerId, $points, $reason]);

            // Log action
            $this->auditService->log(
                Auth::userId(),
                'award_points',
                'loyalty',
                $customerId,
                ['points' => $points, 'reason' => $reason]
            );

            return $this->jsonResponse(['success' => true, 'message' => 'Points awarded successfully']);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to award points'], 500);
        }
    }

    /**
     * Redeem points
     */
    public function redeemPoints()
    {
        if (!Auth::check()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $customerId = (int)$_POST['customer_id'];
        $points = (int)$_POST['points'];
        $orderId = !empty($_POST['order_id']) ? (int)$_POST['order_id'] : null;

        // Verify user can redeem for this customer
        if (Auth::userId() !== $customerId && !Auth::hasPermission('loyalty.manage')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $success = $this->loyaltyService->redeemPoints($customerId, $points, $orderId);

        if ($success) {
            $value = $this->loyaltyService->pointsToCurrency($points);

            // Log action
            $this->auditService->log(
                Auth::userId(),
                'redeem_points',
                'loyalty',
                $customerId,
                ['points' => $points, 'value' => $value, 'order_id' => $orderId]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Points redeemed successfully',
                'discount_value' => $value
            ]);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Insufficient points'], 400);
        }
    }

    /**
     * Claim reward
     */
    public function claimReward()
    {
        if (!Auth::check()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $customerId = (int)$_POST['customer_id'];
        $rewardId = (int)$_POST['reward_id'];

        // Verify user can claim for this customer
        if (Auth::userId() !== $customerId && !Auth::hasPermission('loyalty.manage')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $rewardCode = $this->loyaltyService->claimReward($customerId, $rewardId);

        if ($rewardCode) {
            // Log action
            $this->auditService->log(
                Auth::userId(),
                'claim_reward',
                'loyalty',
                $customerId,
                ['reward_id' => $rewardId, 'code' => $rewardCode]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Reward claimed successfully',
                'reward_code' => $rewardCode
            ]);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to claim reward'], 400);
        }
    }

    /**
     * Get customer points balance (AJAX)
     */
    public function getBalance(int $customerId)
    {
        if (!Auth::check()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $balance = $this->loyaltyService->getPointsBalance($customerId);
        $value = $this->loyaltyService->pointsToCurrency($balance);

        return $this->jsonResponse([
            'success' => true,
            'balance' => $balance,
            'value' => $value
        ]);
    }

    /**
     * Get tier details (AJAX)
     */
    public function getTierDetails(int $customerId)
    {
        if (!Auth::check()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $tierDetails = $this->loyaltyService->getTierDetails($customerId);

        return $this->jsonResponse([
            'success' => true,
            'tier' => $tierDetails
        ]);
    }

    /**
     * Show rewards catalog
     */
    public function rewards()
    {
        if (!Auth::check()) {
            redirect('/login');
            return;
        }

        $customerId = Auth::userId();
        $availableRewards = $this->loyaltyService->getAvailableRewards($customerId);
        $tierDetails = $this->loyaltyService->getTierDetails($customerId);

        require __DIR__ . '/../Views/loyalty/rewards.php';
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
