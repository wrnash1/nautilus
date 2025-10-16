<?php

namespace App\Controllers\Marketing;

use App\Core\Auth;
use App\Services\Marketing\LoyaltyService;

class LoyaltyController
{
    private $loyaltyService;

    public function __construct()
    {
        $this->loyaltyService = new LoyaltyService();
    }

    /**
     * Display loyalty programs list
     */
    public function index()
    {
        if (!Auth::hasPermission('marketing.view')) {
            redirect('/dashboard');
            return;
        }

        $programs = $this->loyaltyService->getAllPrograms();
        $pageTitle = 'Loyalty Programs';

        ob_start();
        require __DIR__ . '/../../Views/marketing/loyalty/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Show create loyalty program form
     */
    public function create()
    {
        if (!Auth::hasPermission('marketing.create')) {
            redirect('/dashboard');
            return;
        }

        $pageTitle = 'Create Loyalty Program';

        ob_start();
        require __DIR__ . '/../../Views/marketing/loyalty/create.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Store new loyalty program
     */
    public function store()
    {
        if (!Auth::hasPermission('marketing.create')) {
            redirect('/dashboard');
            return;
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'points_per_dollar' => $_POST['points_per_dollar'] ?? 1,
            'points_expiry_days' => $_POST['points_expiry_days'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        $programId = $this->loyaltyService->createProgram($data);

        if ($programId) {
            $_SESSION['success'] = 'Loyalty program created successfully.';
            redirect('/marketing/loyalty/' . $programId);
        } else {
            $_SESSION['error'] = 'Failed to create loyalty program.';
            redirect('/marketing/loyalty/create');
        }
    }

    /**
     * Display loyalty program details
     */
    public function show($id)
    {
        if (!Auth::hasPermission('marketing.view')) {
            redirect('/dashboard');
            return;
        }

        $program = $this->loyaltyService->getProgramById($id);
        if (!$program) {
            $_SESSION['error'] = 'Loyalty program not found.';
            redirect('/marketing/loyalty');
            return;
        }

        $tiers = $this->loyaltyService->getTiersByProgram($id);
        $pageTitle = 'Loyalty Program: ' . $program['name'];

        ob_start();
        require __DIR__ . '/../../Views/marketing/loyalty/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Show edit loyalty program form
     */
    public function edit($id)
    {
        if (!Auth::hasPermission('marketing.edit')) {
            redirect('/dashboard');
            return;
        }

        $program = $this->loyaltyService->getProgramById($id);
        if (!$program) {
            $_SESSION['error'] = 'Loyalty program not found.';
            redirect('/marketing/loyalty');
            return;
        }

        $pageTitle = 'Edit Loyalty Program';

        ob_start();
        require __DIR__ . '/../../Views/marketing/loyalty/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Update loyalty program
     */
    public function update($id)
    {
        if (!Auth::hasPermission('marketing.edit')) {
            redirect('/dashboard');
            return;
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'points_per_dollar' => $_POST['points_per_dollar'] ?? 1,
            'points_expiry_days' => $_POST['points_expiry_days'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        $success = $this->loyaltyService->updateProgram($id, $data);

        if ($success) {
            $_SESSION['success'] = 'Loyalty program updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update loyalty program.';
        }

        redirect('/marketing/loyalty/' . $id);
    }

    /**
     * Delete loyalty program
     */
    public function delete($id)
    {
        if (!Auth::hasPermission('marketing.delete')) {
            redirect('/dashboard');
            return;
        }

        $success = $this->loyaltyService->deleteProgram($id);

        if ($success) {
            $_SESSION['success'] = 'Loyalty program deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete loyalty program.';
        }

        redirect('/marketing/loyalty');
    }

    /**
     * Display customer points
     */
    public function customerPoints($customerId)
    {
        if (!Auth::hasPermission('marketing.view')) {
            redirect('/dashboard');
            return;
        }

        $points = $this->loyaltyService->getCustomerPoints($customerId);
        $history = $this->loyaltyService->getPointsHistory($customerId);
        $pageTitle = 'Customer Loyalty Points';

        ob_start();
        require __DIR__ . '/../../Views/marketing/loyalty/customer_points.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Adjust customer points manually
     */
    public function adjustPoints()
    {
        if (!Auth::hasPermission('marketing.edit')) {
            jsonResponse(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $customerId = $_POST['customer_id'] ?? 0;
        $points = $_POST['points'] ?? 0;
        $reason = $_POST['reason'] ?? '';
        $type = $_POST['type'] ?? 'manual_adjustment';

        $success = $this->loyaltyService->adjustPoints($customerId, $points, $reason, $type);

        jsonResponse([
            'success' => $success,
            'message' => $success ? 'Points adjusted successfully' : 'Failed to adjust points'
        ]);
    }
}
