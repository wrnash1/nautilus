<?php

namespace App\Controllers\Marketing;

use App\Core\Auth;
use App\Services\Marketing\CampaignService;

class CampaignController
{
    private $campaignService;

    public function __construct()
    {
        $this->campaignService = new CampaignService();
    }

    /**
     * Display campaigns list
     */
    public function index()
    {
        if (!Auth::hasPermission('marketing.view')) {
            redirect('/store');
            return;
        }

        $campaigns = $this->campaignService->getAllCampaigns();
        $pageTitle = 'Email Campaigns';

        ob_start();
        require __DIR__ . '/../../Views/marketing/campaigns/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Show create campaign form
     */
    public function create()
    {
        if (!Auth::hasPermission('marketing.create')) {
            redirect('/store');
            return;
        }

        $templates = $this->campaignService->getAllTemplates();
        $pageTitle = 'Create Campaign';

        ob_start();
        require __DIR__ . '/../../Views/marketing/campaigns/create.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Store new campaign
     */
    public function store()
    {
        if (!Auth::hasPermission('marketing.create')) {
            redirect('/store');
            return;
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'subject' => $_POST['subject'] ?? '',
            'template_id' => $_POST['template_id'] ?? null,
            'content' => $_POST['content'] ?? '',
            'segment' => $_POST['segment'] ?? 'all',
            'status' => 'draft',
            'scheduled_at' => $_POST['scheduled_at'] ?? null
        ];

        $campaignId = $this->campaignService->createCampaign($data);

        if ($campaignId) {
            $_SESSION['success'] = 'Campaign created successfully.';
            redirect('/store/marketing/campaigns/' . $campaignId);
        } else {
            $_SESSION['error'] = 'Failed to create campaign.';
            redirect('/store/marketing/campaigns/create');
        }
    }

    /**
     * Display campaign details
     */
    public function show($id)
    {
        if (!Auth::hasPermission('marketing.view')) {
            redirect('/store');
            return;
        }

        $campaign = $this->campaignService->getCampaignById($id);
        if (!$campaign) {
            $_SESSION['error'] = 'Campaign not found.';
            redirect('/store/marketing/campaigns');
            return;
        }

        $stats = $this->campaignService->getCampaignStats($id);
        $pageTitle = 'Campaign: ' . $campaign['name'];

        ob_start();
        require __DIR__ . '/../../Views/marketing/campaigns/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Show edit campaign form
     */
    public function edit($id)
    {
        if (!Auth::hasPermission('marketing.edit')) {
            redirect('/store');
            return;
        }

        $campaign = $this->campaignService->getCampaignById($id);
        if (!$campaign) {
            $_SESSION['error'] = 'Campaign not found.';
            redirect('/store/marketing/campaigns');
            return;
        }

        if ($campaign['status'] !== 'draft') {
            $_SESSION['error'] = 'Only draft campaigns can be edited.';
            redirect('/store/marketing/campaigns/' . $id);
            return;
        }

        $templates = $this->campaignService->getAllTemplates();
        $pageTitle = 'Edit Campaign';

        ob_start();
        require __DIR__ . '/../../Views/marketing/campaigns/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Update campaign
     */
    public function update($id)
    {
        if (!Auth::hasPermission('marketing.edit')) {
            redirect('/store');
            return;
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'subject' => $_POST['subject'] ?? '',
            'template_id' => $_POST['template_id'] ?? null,
            'content' => $_POST['content'] ?? '',
            'segment' => $_POST['segment'] ?? 'all',
            'scheduled_at' => $_POST['scheduled_at'] ?? null
        ];

        $success = $this->campaignService->updateCampaign($id, $data);

        if ($success) {
            $_SESSION['success'] = 'Campaign updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update campaign.';
        }

        redirect('/store/marketing/campaigns/' . $id);
    }

    /**
     * Delete campaign
     */
    public function delete($id)
    {
        if (!Auth::hasPermission('marketing.delete')) {
            redirect('/store');
            return;
        }

        $success = $this->campaignService->deleteCampaign($id);

        if ($success) {
            $_SESSION['success'] = 'Campaign deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete campaign.';
        }

        redirect('/store/marketing/campaigns');
    }

    /**
     * Send campaign
     */
    public function send($id)
    {
        if (!Auth::hasPermission('marketing.edit')) {
            redirect('/store');
            return;
        }

        $success = $this->campaignService->sendCampaign($id);

        if ($success) {
            $_SESSION['success'] = 'Campaign sent successfully.';
        } else {
            $_SESSION['error'] = 'Failed to send campaign.';
        }

        redirect('/store/marketing/campaigns/' . $id);
    }

    /**
     * Display email templates
     */
    public function templates()
    {
        if (!Auth::hasPermission('marketing.view')) {
            redirect('/store');
            return;
        }

        $templates = $this->campaignService->getAllTemplates();
        $pageTitle = 'Email Templates';

        ob_start();
        require __DIR__ . '/../../Views/marketing/templates/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }
}
