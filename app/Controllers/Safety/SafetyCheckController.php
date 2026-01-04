<?php

namespace App\Controllers\Safety;

use App\Core\Controller;

class SafetyCheckController extends Controller
{
    /**
     * Display safety checks list
     */
    public function index(): void
    {
        $this->checkPermission('customers.view');

        $status = $_GET['status'] ?? 'all';
        $date = $_GET['date'] ?? date('Y-m-d');

        try {
            $sql = "
                SELECT sc.*, c.first_name, c.last_name, ds.name as dive_site_name
                FROM pre_dive_safety_checks sc
                LEFT JOIN customers c ON sc.customer_id = c.id
                LEFT JOIN dive_sites ds ON sc.dive_site_id = ds.id
                WHERE DATE(sc.created_at) = ?";
            $params = [$date];

            if ($status !== 'all') {
                $sql .= " AND sc.check_status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY sc.created_at DESC LIMIT 100";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $checks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Stats for today
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM pre_dive_safety_checks WHERE DATE(created_at) = ? AND check_status = 'passed'");
            $stmt->execute([$date]);
            $passedCount = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM pre_dive_safety_checks WHERE DATE(created_at) = ? AND check_status = 'failed'");
            $stmt->execute([$date]);
            $failedCount = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM pre_dive_safety_checks WHERE DATE(created_at) = ? AND check_status = 'incomplete'");
            $stmt->execute([$date]);
            $incompleteCount = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

        } catch (\Exception $e) {
            $checks = [];
            $passedCount = 0;
            $failedCount = 0;
            $incompleteCount = 0;
        }

        $this->view('safety/index', [
            'title' => 'Pre-Dive Safety Checks',
            'checks' => $checks,
            'status' => $status,
            'date' => $date,
            'stats' => [
                'passed' => $passedCount,
                'failed' => $failedCount,
                'incomplete' => $incompleteCount
            ]
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->checkPermission('customers.view');

        try {
            // Get customers
            $stmt = $this->db->query("
                SELECT id, first_name, last_name, email
                FROM customers WHERE status = 'active'
                ORDER BY last_name, first_name
            ");
            $customers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get dive sites
            $stmt = $this->db->query("
                SELECT id, name FROM dive_sites WHERE is_active = 1 ORDER BY name
            ");
            $diveSites = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get active trips for today
            $stmt = $this->db->query("
                SELECT id, name, departure_date FROM trips
                WHERE departure_date >= CURDATE() AND status = 'scheduled'
                ORDER BY departure_date
            ");
            $trips = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $customers = [];
            $diveSites = [];
            $trips = [];
        }

        $this->view('safety/create', [
            'title' => 'New Safety Check',
            'customers' => $customers,
            'diveSites' => $diveSites,
            'trips' => $trips
        ]);
    }

    /**
     * Store new safety check
     */
    public function store(): void
    {
        $this->checkPermission('customers.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/safety-checks');
            return;
        }

        $customerId = (int)($_POST['customer_id'] ?? 0);
        if (!$customerId) {
            $_SESSION['flash_error'] = 'Please select a customer';
            $this->redirect('/store/safety-checks/create');
            return;
        }

        try {
            // Calculate if all checks passed
            $bwrafChecks = [
                'bcd_inflator_works', 'bcd_deflator_works', 'weights_adequate', 'weights_secure',
                'weights_releasable', 'bcd_releases_located', 'weight_releases_located',
                'tank_valve_fully_open', 'air_on_and_breathable', 'pressure_gauge_working',
                'mask_fits_properly', 'fins_secure', 'computer_functioning'
            ];

            $allPassed = true;
            foreach ($bwrafChecks as $check) {
                if (empty($_POST[$check])) {
                    $allPassed = false;
                    break;
                }
            }

            $checkStatus = $allPassed ? 'passed' : ($_POST['check_status'] ?? 'incomplete');

            $stmt = $this->db->prepare("
                INSERT INTO pre_dive_safety_checks (
                    customer_id, buddy_customer_id, dive_site_id, trip_id,
                    dive_type, planned_depth_feet, planned_duration_minutes, dive_number_today,
                    bcd_inflator_works, bcd_deflator_works, bcd_overpressure_valve_clear,
                    bcd_straps_secure, bcd_integrated_weights_secure,
                    weights_adequate, weights_secure, weights_releasable, weight_amount_kg,
                    bcd_releases_located, weight_releases_located, all_releases_functional,
                    tank_valve_fully_open, air_on_and_breathable, pressure_gauge_working,
                    starting_pressure_psi, air_quality_good, reserve_pressure_adequate,
                    mask_fits_properly, mask_defogged, fins_secure, snorkel_attached,
                    computer_functioning, compass_functioning, knife_accessible, smb_accessible,
                    dive_plan_reviewed, hand_signals_reviewed, emergency_procedures_reviewed,
                    entry_exit_points_identified, water_temp_fahrenheit, visibility_feet,
                    current_strength, diver_feels_well, diver_not_fatigued,
                    no_alcohol_24hrs, no_medications_affecting_diving, surface_interval_adequate,
                    all_checks_passed, issues_noted, check_status, checked_by_user_id, checked_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, NOW()
                )
            ");

            $stmt->execute([
                $customerId,
                $_POST['buddy_customer_id'] ?: null,
                $_POST['dive_site_id'] ?: null,
                $_POST['trip_id'] ?: null,
                $_POST['dive_type'] ?? 'recreational',
                $_POST['planned_depth_feet'] ?: null,
                $_POST['planned_duration_minutes'] ?: null,
                $_POST['dive_number_today'] ?? 1,
                !empty($_POST['bcd_inflator_works']) ? 1 : 0,
                !empty($_POST['bcd_deflator_works']) ? 1 : 0,
                !empty($_POST['bcd_overpressure_valve_clear']) ? 1 : 0,
                !empty($_POST['bcd_straps_secure']) ? 1 : 0,
                !empty($_POST['bcd_integrated_weights_secure']) ? 1 : 0,
                !empty($_POST['weights_adequate']) ? 1 : 0,
                !empty($_POST['weights_secure']) ? 1 : 0,
                !empty($_POST['weights_releasable']) ? 1 : 0,
                $_POST['weight_amount_kg'] ?: null,
                !empty($_POST['bcd_releases_located']) ? 1 : 0,
                !empty($_POST['weight_releases_located']) ? 1 : 0,
                !empty($_POST['all_releases_functional']) ? 1 : 0,
                !empty($_POST['tank_valve_fully_open']) ? 1 : 0,
                !empty($_POST['air_on_and_breathable']) ? 1 : 0,
                !empty($_POST['pressure_gauge_working']) ? 1 : 0,
                $_POST['starting_pressure_psi'] ?: null,
                !empty($_POST['air_quality_good']) ? 1 : 0,
                !empty($_POST['reserve_pressure_adequate']) ? 1 : 0,
                !empty($_POST['mask_fits_properly']) ? 1 : 0,
                !empty($_POST['mask_defogged']) ? 1 : 0,
                !empty($_POST['fins_secure']) ? 1 : 0,
                !empty($_POST['snorkel_attached']) ? 1 : 0,
                !empty($_POST['computer_functioning']) ? 1 : 0,
                !empty($_POST['compass_functioning']) ? 1 : 0,
                !empty($_POST['knife_accessible']) ? 1 : 0,
                !empty($_POST['smb_accessible']) ? 1 : 0,
                !empty($_POST['dive_plan_reviewed']) ? 1 : 0,
                !empty($_POST['hand_signals_reviewed']) ? 1 : 0,
                !empty($_POST['emergency_procedures_reviewed']) ? 1 : 0,
                !empty($_POST['entry_exit_points_identified']) ? 1 : 0,
                $_POST['water_temp_fahrenheit'] ?: null,
                $_POST['visibility_feet'] ?: null,
                $_POST['current_strength'] ?: null,
                !empty($_POST['diver_feels_well']) ? 1 : 0,
                !empty($_POST['diver_not_fatigued']) ? 1 : 0,
                !empty($_POST['no_alcohol_24hrs']) ? 1 : 0,
                !empty($_POST['no_medications_affecting_diving']) ? 1 : 0,
                !empty($_POST['surface_interval_adequate']) ? 1 : 0,
                $allPassed ? 1 : 0,
                $_POST['issues_noted'] ?? null,
                $checkStatus,
                $_SESSION['user_id'] ?? null
            ]);

            $checkId = $this->db->lastInsertId();

            $_SESSION['flash_success'] = 'Safety check recorded successfully';
            $this->redirect('/store/safety-checks/' . $checkId);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to save safety check: ' . $e->getMessage();
            $this->redirect('/store/safety-checks/create');
        }
    }

    /**
     * Show safety check details
     */
    public function show(int $id): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->prepare("
                SELECT sc.*,
                       c.first_name, c.last_name, c.email,
                       b.first_name as buddy_first_name, b.last_name as buddy_last_name,
                       ds.name as dive_site_name,
                       t.name as trip_name,
                       u.first_name as checker_first_name, u.last_name as checker_last_name
                FROM pre_dive_safety_checks sc
                LEFT JOIN customers c ON sc.customer_id = c.id
                LEFT JOIN customers b ON sc.buddy_customer_id = b.id
                LEFT JOIN dive_sites ds ON sc.dive_site_id = ds.id
                LEFT JOIN trips t ON sc.trip_id = t.id
                LEFT JOIN users u ON sc.checked_by_user_id = u.id
                WHERE sc.id = ?
            ");
            $stmt->execute([$id]);
            $check = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$check) {
                $_SESSION['flash_error'] = 'Safety check not found';
                $this->redirect('/store/safety-checks');
                return;
            }

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error loading safety check';
            $this->redirect('/store/safety-checks');
            return;
        }

        $this->view('safety/show', [
            'title' => 'Safety Check Details',
            'check' => $check
        ]);
    }

    /**
     * Dashboard view
     */
    public function dashboard(): void
    {
        $this->checkPermission('customers.view');

        try {
            // Today's stats
            $stmt = $this->db->query("
                SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN check_status = 'passed' THEN 1 ELSE 0 END) as passed,
                    SUM(CASE WHEN check_status = 'failed' THEN 1 ELSE 0 END) as failed,
                    SUM(CASE WHEN check_status = 'incomplete' THEN 1 ELSE 0 END) as incomplete
                FROM pre_dive_safety_checks
                WHERE DATE(created_at) = CURDATE()
            ");
            $todayStats = $stmt->fetch(\PDO::FETCH_ASSOC);

            // This week stats
            $stmt = $this->db->query("
                SELECT COUNT(*) as count
                FROM pre_dive_safety_checks
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ");
            $weekCount = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            // Recent checks
            $stmt = $this->db->query("
                SELECT sc.*, c.first_name, c.last_name
                FROM pre_dive_safety_checks sc
                LEFT JOIN customers c ON sc.customer_id = c.id
                ORDER BY sc.created_at DESC
                LIMIT 10
            ");
            $recentChecks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Common issues
            $stmt = $this->db->query("
                SELECT issues_noted, COUNT(*) as count
                FROM pre_dive_safety_checks
                WHERE issues_noted IS NOT NULL AND issues_noted != ''
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY issues_noted
                ORDER BY count DESC
                LIMIT 5
            ");
            $commonIssues = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $todayStats = ['total' => 0, 'passed' => 0, 'failed' => 0, 'incomplete' => 0];
            $weekCount = 0;
            $recentChecks = [];
            $commonIssues = [];
        }

        $this->view('safety/dashboard', [
            'title' => 'Safety Dashboard',
            'todayStats' => $todayStats,
            'weekCount' => $weekCount,
            'recentChecks' => $recentChecks,
            'commonIssues' => $commonIssues
        ]);
    }
}
