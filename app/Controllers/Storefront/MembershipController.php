<?php

namespace App\Controllers\Storefront;

use App\Core\Database;

class MembershipController extends StorefrontController
{
    /**
     * Display membership tiers
     */
    public function index(): void
    {
        $db = Database::getInstance();

        $stmt = $db->query("
            SELECT * FROM membership_tiers 
            WHERE is_active = 1 
            ORDER BY display_order ASC, price ASC
        ");
        $tiers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [
            'tiers' => $tiers,
            'pageTitle' => 'Club Memberships'
        ];

        $this->renderStorefront('storefront/memberships/index', $data);
    }

    /**
     * Show membership signup form
     */
    public function join(int $id): void
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM membership_tiers WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        $tier = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$tier) {
            $_SESSION['error'] = 'Membership tier not found';
            redirect('/memberships');
            return;
        }

        $data = [
            'tier' => $tier,
            'pageTitle' => 'Join ' . $tier['name']
        ];

        $this->renderStorefront('storefront/memberships/join', $data);
    }

    /**
     * Process membership checkout
     */
    public function checkout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/memberships');
            return;
        }

        // Check if user is logged in
        if (!isset($_SESSION['customer_id'])) {
            $_SESSION['error'] = 'Please log in to purchase a membership';
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            redirect('/account/login');
            return;
        }

        $db = Database::getInstance();
        $tierId = (int) ($_POST['tier_id'] ?? 0);
        $customerId = $_SESSION['customer_id'];

        // Get tier details
        $stmt = $db->prepare("SELECT * FROM membership_tiers WHERE id = ? AND is_active = 1");
        $stmt->execute([$tierId]);
        $tier = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$tier) {
            $_SESSION['error'] = 'Invalid membership tier';
            redirect('/memberships');
            return;
        }

        // Calculate dates
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+{$tier['duration_months']} months"));

        // Create membership
        $stmt = $db->prepare("
            INSERT INTO customer_memberships 
            (customer_id, membership_tier_id, start_date, end_date, status, payment_method, created_at)
            VALUES (?, ?, ?, ?, 'active', ?, NOW())
        ");

        $paymentMethod = $_POST['payment_method'] ?? 'credit_card';
        $stmt->execute([$customerId, $tierId, $startDate, $endDate, $paymentMethod]);

        $_SESSION['success'] = 'Congratulations! Your ' . $tier['name'] . ' membership is now active!';
        redirect('/account/membership');
    }
}
