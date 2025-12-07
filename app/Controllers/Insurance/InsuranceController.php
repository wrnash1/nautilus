<?php

namespace App\Controllers\Insurance;

use App\Core\Controller;

class InsuranceController extends Controller
{
    /**
     * Display insurance policies list
     */
    public function index(): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->query("
                SELECT dip.*, c.first_name, c.last_name, c.email
                FROM dive_insurance_policies dip
                JOIN customers c ON dip.customer_id = c.id
                ORDER BY dip.expiration_date ASC
            ");
            $policies = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Stats
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM dive_insurance_policies WHERE expiration_date >= CURDATE()");
            $activeCount = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            $stmt = $this->db->query("SELECT COUNT(*) as count FROM dive_insurance_policies WHERE expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
            $expiringCount = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            $stmt = $this->db->query("SELECT COUNT(*) as count FROM dive_insurance_policies WHERE expiration_date < CURDATE()");
            $expiredCount = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

        } catch (\Exception $e) {
            $policies = [];
            $activeCount = 0;
            $expiringCount = 0;
            $expiredCount = 0;
        }

        $this->view('insurance/index', [
            'title' => 'Dive Insurance Policies',
            'policies' => $policies,
            'stats' => [
                'active' => $activeCount,
                'expiring' => $expiringCount,
                'expired' => $expiredCount
            ]
        ]);
    }

    /**
     * Show create policy form
     */
    public function create(): void
    {
        $this->checkPermission('customers.edit');

        try {
            $stmt = $this->db->query("
                SELECT id, first_name, last_name, email
                FROM customers WHERE status = 'active'
                ORDER BY last_name, first_name
            ");
            $customers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $customers = [];
        }

        $this->view('insurance/create', [
            'title' => 'Add Insurance Policy',
            'customers' => $customers
        ]);
    }

    /**
     * Store new policy
     */
    public function store(): void
    {
        $this->checkPermission('customers.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/insurance');
            return;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO dive_insurance_policies (
                    tenant_id, customer_id, insurance_provider, policy_number, policy_type,
                    coverage_level, coverage_amount, deductible,
                    covers_hyperbaric, covers_evacuation, covers_recompression, covers_medical, covers_equipment,
                    effective_date, expiration_date, emergency_phone, claims_phone
                ) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                (int)($_POST['customer_id'] ?? 0),
                $_POST['insurance_provider'] ?? '',
                $_POST['policy_number'] ?? '',
                $_POST['policy_type'] ?? 'individual',
                $_POST['coverage_level'] ?? null,
                (float)($_POST['coverage_amount'] ?? 0) ?: null,
                (float)($_POST['deductible'] ?? 0) ?: null,
                isset($_POST['covers_hyperbaric']) ? 1 : 0,
                isset($_POST['covers_evacuation']) ? 1 : 0,
                isset($_POST['covers_recompression']) ? 1 : 0,
                isset($_POST['covers_medical']) ? 1 : 0,
                isset($_POST['covers_equipment']) ? 1 : 0,
                $_POST['effective_date'] ?? date('Y-m-d'),
                $_POST['expiration_date'] ?? date('Y-m-d', strtotime('+1 year')),
                $_POST['emergency_phone'] ?? null,
                $_POST['claims_phone'] ?? null
            ]);

            $_SESSION['flash_success'] = 'Insurance policy added successfully';
            $this->redirect('/store/insurance/' . $this->db->lastInsertId());
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to add policy: ' . $e->getMessage();
            $this->redirect('/store/insurance/create');
        }
    }

    /**
     * Show policy details
     */
    public function show(int $id): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->prepare("
                SELECT dip.*, c.first_name, c.last_name, c.email, c.phone
                FROM dive_insurance_policies dip
                JOIN customers c ON dip.customer_id = c.id
                WHERE dip.id = ?
            ");
            $stmt->execute([$id]);
            $policy = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$policy) {
                $_SESSION['flash_error'] = 'Policy not found';
                $this->redirect('/store/insurance');
                return;
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error loading policy';
            $this->redirect('/store/insurance');
            return;
        }

        $this->view('insurance/show', [
            'title' => 'Policy Details',
            'policy' => $policy
        ]);
    }

    /**
     * Show expiring policies
     */
    public function expiring(): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->query("
                SELECT dip.*, c.first_name, c.last_name, c.email
                FROM dive_insurance_policies dip
                JOIN customers c ON dip.customer_id = c.id
                WHERE dip.expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)
                ORDER BY dip.expiration_date ASC
            ");
            $policies = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $policies = [];
        }

        $this->view('insurance/expiring', [
            'title' => 'Expiring Policies',
            'policies' => $policies
        ]);
    }

    /**
     * Edit policy
     */
    public function edit(int $id): void
    {
        $this->checkPermission('customers.edit');

        try {
            $stmt = $this->db->prepare("
                SELECT dip.*, c.first_name, c.last_name
                FROM dive_insurance_policies dip
                JOIN customers c ON dip.customer_id = c.id
                WHERE dip.id = ?
            ");
            $stmt->execute([$id]);
            $policy = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$policy) {
                $_SESSION['flash_error'] = 'Policy not found';
                $this->redirect('/store/insurance');
                return;
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error loading policy';
            $this->redirect('/store/insurance');
            return;
        }

        $this->view('insurance/edit', [
            'title' => 'Edit Policy',
            'policy' => $policy
        ]);
    }

    /**
     * Update policy
     */
    public function update(int $id): void
    {
        $this->checkPermission('customers.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/insurance/' . $id);
            return;
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE dive_insurance_policies SET
                    insurance_provider = ?, policy_number = ?, policy_type = ?,
                    coverage_level = ?, coverage_amount = ?, deductible = ?,
                    covers_hyperbaric = ?, covers_evacuation = ?, covers_recompression = ?,
                    covers_medical = ?, covers_equipment = ?,
                    effective_date = ?, expiration_date = ?,
                    emergency_phone = ?, claims_phone = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $_POST['insurance_provider'] ?? '',
                $_POST['policy_number'] ?? '',
                $_POST['policy_type'] ?? 'individual',
                $_POST['coverage_level'] ?? null,
                (float)($_POST['coverage_amount'] ?? 0) ?: null,
                (float)($_POST['deductible'] ?? 0) ?: null,
                isset($_POST['covers_hyperbaric']) ? 1 : 0,
                isset($_POST['covers_evacuation']) ? 1 : 0,
                isset($_POST['covers_recompression']) ? 1 : 0,
                isset($_POST['covers_medical']) ? 1 : 0,
                isset($_POST['covers_equipment']) ? 1 : 0,
                $_POST['effective_date'] ?? date('Y-m-d'),
                $_POST['expiration_date'] ?? date('Y-m-d', strtotime('+1 year')),
                $_POST['emergency_phone'] ?? null,
                $_POST['claims_phone'] ?? null,
                $id
            ]);

            $_SESSION['flash_success'] = 'Policy updated successfully';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to update policy';
        }

        $this->redirect('/store/insurance/' . $id);
    }

    /**
     * Dashboard
     */
    public function dashboard(): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM dive_insurance_policies");
            $totalPolicies = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            $stmt = $this->db->query("SELECT COUNT(*) as count FROM dive_insurance_policies WHERE expiration_date >= CURDATE()");
            $activePolicies = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            $stmt = $this->db->query("SELECT COUNT(*) as count FROM dive_insurance_policies WHERE expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
            $expiringPolicies = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            // By provider
            $stmt = $this->db->query("
                SELECT insurance_provider, COUNT(*) as count
                FROM dive_insurance_policies
                GROUP BY insurance_provider
                ORDER BY count DESC
            ");
            $byProvider = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Recent policies
            $stmt = $this->db->query("
                SELECT dip.*, c.first_name, c.last_name
                FROM dive_insurance_policies dip
                JOIN customers c ON dip.customer_id = c.id
                ORDER BY dip.created_at DESC LIMIT 5
            ");
            $recentPolicies = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $totalPolicies = 0;
            $activePolicies = 0;
            $expiringPolicies = 0;
            $byProvider = [];
            $recentPolicies = [];
        }

        $this->view('insurance/dashboard', [
            'title' => 'Insurance Dashboard',
            'stats' => [
                'total' => $totalPolicies,
                'active' => $activePolicies,
                'expiring' => $expiringPolicies
            ],
            'byProvider' => $byProvider,
            'recentPolicies' => $recentPolicies
        ]);
    }
}
