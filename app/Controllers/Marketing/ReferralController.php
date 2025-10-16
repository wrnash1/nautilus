<?php

namespace App\Controllers\Marketing;

use App\Core\Auth;
use App\Services\Marketing\ReferralService;

class ReferralController
{
    private $referralService;

    public function __construct()
    {
        $this->referralService = new ReferralService();
    }

    /**
     * Display referral program dashboard
     */
    public function index()
    {
        if (!Auth::hasPermission('marketing.view')) {
            redirect('/dashboard');
            return;
        }

        $stats = $this->referralService->getReferralStats();
        $topReferrers = $this->referralService->getTopReferrers(10);
        $pageTitle = 'Referral Program';

        ob_start();
        require __DIR__ . '/../../Views/marketing/referrals/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Display referral history
     */
    public function history()
    {
        if (!Auth::hasPermission('marketing.view')) {
            redirect('/dashboard');
            return;
        }

        $referrals = $this->referralService->getAllReferrals();
        $pageTitle = 'Referral History';

        ob_start();
        require __DIR__ . '/../../Views/marketing/referrals/history.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Display customer referral details
     */
    public function customerReferrals($customerId)
    {
        if (!Auth::hasPermission('marketing.view')) {
            redirect('/dashboard');
            return;
        }

        $referrals = $this->referralService->getCustomerReferrals($customerId);
        $stats = $this->referralService->getCustomerReferralStats($customerId);
        $pageTitle = 'Customer Referrals';

        ob_start();
        require __DIR__ . '/../../Views/marketing/referrals/customer.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Process referral (manually record a referral)
     */
    public function process()
    {
        if (!Auth::hasPermission('marketing.create')) {
            jsonResponse(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $referrerId = $_POST['referrer_id'] ?? 0;
        $referredId = $_POST['referred_id'] ?? 0;
        $referralCode = $_POST['referral_code'] ?? '';

        $success = $this->referralService->processReferral($referrerId, $referredId, $referralCode);

        jsonResponse([
            'success' => $success,
            'message' => $success ? 'Referral processed successfully' : 'Failed to process referral'
        ]);
    }

    /**
     * Configure referral program settings
     */
    public function settings()
    {
        if (!Auth::hasPermission('marketing.edit')) {
            redirect('/dashboard');
            return;
        }

        $settings = $this->referralService->getSettings();
        $pageTitle = 'Referral Program Settings';

        ob_start();
        require __DIR__ . '/../../Views/marketing/referrals/settings.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Update referral program settings
     */
    public function updateSettings()
    {
        if (!Auth::hasPermission('marketing.edit')) {
            redirect('/dashboard');
            return;
        }

        $settings = [
            'referrer_reward_type' => $_POST['referrer_reward_type'] ?? 'points',
            'referrer_reward_value' => $_POST['referrer_reward_value'] ?? 0,
            'referee_reward_type' => $_POST['referee_reward_type'] ?? 'discount',
            'referee_reward_value' => $_POST['referee_reward_value'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        $success = $this->referralService->updateSettings($settings);

        if ($success) {
            $_SESSION['success'] = 'Referral program settings updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update settings.';
        }

        redirect('/marketing/referrals/settings');
    }
}
