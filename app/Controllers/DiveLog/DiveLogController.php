<?php

namespace App\Controllers\DiveLog;

use App\Core\Controller;

class DiveLogController extends Controller
{
    /**
     * Display dive logs list
     */
    public function index(): void
    {
        $this->checkPermission('customers.view');

        $customerId = $_GET['customer_id'] ?? null;
        $search = $_GET['search'] ?? '';

        try {
            $sql = "
                SELECT dl.*, c.first_name, c.last_name, c.email,
                       ds.name as dive_site_name_db
                FROM dive_logs dl
                JOIN customers c ON dl.customer_id = c.id
                LEFT JOIN dive_sites ds ON dl.dive_site_id = ds.id
                WHERE 1=1";
            $params = [];

            if ($customerId) {
                $sql .= " AND dl.customer_id = ?";
                $params[] = $customerId;
            }

            if ($search) {
                $sql .= " AND (c.first_name LIKE ? OR c.last_name LIKE ? OR dl.dive_site_name LIKE ? OR dl.location LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }

            $sql .= " ORDER BY dl.dive_date DESC, dl.entry_time DESC LIMIT 100";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Stats
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM dive_logs");
            $totalLogs = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            $stmt = $this->db->query("SELECT COUNT(DISTINCT customer_id) as count FROM dive_logs");
            $uniqueDivers = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            $stmt = $this->db->query("SELECT AVG(max_depth_feet) as avg FROM dive_logs WHERE max_depth_feet IS NOT NULL");
            $avgDepth = $stmt->fetch(\PDO::FETCH_ASSOC)['avg'] ?? 0;

        } catch (\Exception $e) {
            $logs = [];
            $totalLogs = 0;
            $uniqueDivers = 0;
            $avgDepth = 0;
        }

        $this->view('divelog/index', [
            'title' => 'Dive Logs',
            'logs' => $logs,
            'search' => $search,
            'customerId' => $customerId,
            'stats' => [
                'total_logs' => $totalLogs,
                'unique_divers' => $uniqueDivers,
                'avg_depth' => round($avgDepth, 1)
            ]
        ]);
    }

    /**
     * Show create form
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

            $stmt = $this->db->query("
                SELECT id, name, location, max_depth_feet
                FROM dive_sites WHERE is_active = 1
                ORDER BY name
            ");
            $diveSites = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $customers = [];
            $diveSites = [];
        }

        $this->view('divelog/create', [
            'title' => 'Log New Dive',
            'customers' => $customers,
            'diveSites' => $diveSites
        ]);
    }

    /**
     * Store new dive log
     */
    public function store(): void
    {
        $this->checkPermission('customers.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/dive-logs');
            return;
        }

        $customerId = (int)($_POST['customer_id'] ?? 0);
        if (!$customerId) {
            $_SESSION['flash_error'] = 'Please select a diver';
            $this->redirect('/store/dive-logs/create');
            return;
        }

        try {
            // Get next dive number for customer
            $stmt = $this->db->prepare("SELECT MAX(dive_number) as max FROM dive_logs WHERE customer_id = ?");
            $stmt->execute([$customerId]);
            $maxDive = $stmt->fetch(\PDO::FETCH_ASSOC)['max'] ?? 0;
            $diveNumber = $maxDive + 1;

            $stmt = $this->db->prepare("
                INSERT INTO dive_logs (
                    tenant_id, customer_id, dive_number, dive_date, dive_site_id, dive_site_name,
                    location, country, entry_time, exit_time, max_depth_feet, max_depth_meters,
                    average_depth_feet, bottom_time_minutes, starting_pressure_psi, ending_pressure_psi,
                    gas_type, water_type, visibility_feet, water_temperature_f, air_temperature_f,
                    weather_conditions, dive_type, weight_used_lbs, wetsuit_type, notes
                ) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $maxDepthFeet = (float)($_POST['max_depth_feet'] ?? 0) ?: null;
            $maxDepthMeters = $maxDepthFeet ? round($maxDepthFeet * 0.3048, 2) : null;
            $avgDepthFeet = (float)($_POST['average_depth_feet'] ?? 0) ?: null;

            $stmt->execute([
                $customerId,
                $diveNumber,
                $_POST['dive_date'] ?? date('Y-m-d'),
                $_POST['dive_site_id'] ?: null,
                $_POST['dive_site_name'] ?? null,
                $_POST['location'] ?? null,
                $_POST['country'] ?? null,
                $_POST['entry_time'] ?: null,
                $_POST['exit_time'] ?: null,
                $maxDepthFeet,
                $maxDepthMeters,
                $avgDepthFeet,
                (int)($_POST['bottom_time_minutes'] ?? 0) ?: null,
                (int)($_POST['starting_pressure_psi'] ?? 0) ?: null,
                (int)($_POST['ending_pressure_psi'] ?? 0) ?: null,
                $_POST['gas_type'] ?? 'air',
                $_POST['water_type'] ?? 'salt',
                (int)($_POST['visibility_feet'] ?? 0) ?: null,
                (int)($_POST['water_temperature_f'] ?? 0) ?: null,
                (int)($_POST['air_temperature_f'] ?? 0) ?: null,
                $_POST['weather_conditions'] ?? null,
                $_POST['dive_type'] ?? 'recreational',
                (float)($_POST['weight_used_lbs'] ?? 0) ?: null,
                $_POST['wetsuit_type'] ?? null,
                $_POST['notes'] ?? null
            ]);

            $_SESSION['flash_success'] = "Dive #{$diveNumber} logged successfully";
            $this->redirect('/store/dive-logs/' . $this->db->lastInsertId());
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to log dive: ' . $e->getMessage();
            $this->redirect('/store/dive-logs/create');
        }
    }

    /**
     * Show dive log details
     */
    public function show(int $id): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->prepare("
                SELECT dl.*, c.first_name, c.last_name, c.email, c.phone,
                       ds.name as site_name_db, ds.location as site_location
                FROM dive_logs dl
                JOIN customers c ON dl.customer_id = c.id
                LEFT JOIN dive_sites ds ON dl.dive_site_id = ds.id
                WHERE dl.id = ?
            ");
            $stmt->execute([$id]);
            $log = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$log) {
                $_SESSION['flash_error'] = 'Dive log not found';
                $this->redirect('/store/dive-logs');
                return;
            }

            // Get media
            $stmt = $this->db->prepare("SELECT * FROM dive_log_media WHERE dive_log_id = ? ORDER BY display_order");
            $stmt->execute([$id]);
            $media = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error loading dive log';
            $this->redirect('/store/dive-logs');
            return;
        }

        $this->view('divelog/show', [
            'title' => 'Dive #' . $log['dive_number'],
            'log' => $log,
            'media' => $media
        ]);
    }

    /**
     * Customer dive log history
     */
    public function customerLogs(int $customerId): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = ?");
            $stmt->execute([$customerId]);
            $customer = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$customer) {
                $_SESSION['flash_error'] = 'Customer not found';
                $this->redirect('/store/customers');
                return;
            }

            $stmt = $this->db->prepare("
                SELECT dl.*, ds.name as site_name_db
                FROM dive_logs dl
                LEFT JOIN dive_sites ds ON dl.dive_site_id = ds.id
                WHERE dl.customer_id = ?
                ORDER BY dl.dive_date DESC, dl.dive_number DESC
            ");
            $stmt->execute([$customerId]);
            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Stats
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*) as total_dives,
                    MAX(max_depth_feet) as max_depth,
                    SUM(bottom_time_minutes) as total_time,
                    MIN(dive_date) as first_dive,
                    MAX(dive_date) as last_dive
                FROM dive_logs WHERE customer_id = ?
            ");
            $stmt->execute([$customerId]);
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $customer = null;
            $logs = [];
            $stats = [];
        }

        $this->view('divelog/customer', [
            'title' => 'Dive History - ' . ($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''),
            'customer' => $customer,
            'logs' => $logs,
            'stats' => $stats
        ]);
    }

    /**
     * Dashboard with statistics
     */
    public function dashboard(): void
    {
        $this->checkPermission('customers.view');

        try {
            // Overall stats
            $stmt = $this->db->query("
                SELECT
                    COUNT(*) as total_dives,
                    COUNT(DISTINCT customer_id) as unique_divers,
                    AVG(max_depth_feet) as avg_depth,
                    MAX(max_depth_feet) as max_depth,
                    SUM(bottom_time_minutes) as total_time
                FROM dive_logs
            ");
            $overallStats = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Top divers
            $stmt = $this->db->query("
                SELECT c.id, c.first_name, c.last_name, COUNT(*) as dive_count
                FROM dive_logs dl
                JOIN customers c ON dl.customer_id = c.id
                GROUP BY c.id
                ORDER BY dive_count DESC
                LIMIT 10
            ");
            $topDivers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Popular dive sites
            $stmt = $this->db->query("
                SELECT COALESCE(ds.name, dl.dive_site_name) as site_name, COUNT(*) as dive_count
                FROM dive_logs dl
                LEFT JOIN dive_sites ds ON dl.dive_site_id = ds.id
                WHERE dl.dive_site_id IS NOT NULL OR dl.dive_site_name IS NOT NULL
                GROUP BY COALESCE(ds.name, dl.dive_site_name)
                ORDER BY dive_count DESC
                LIMIT 10
            ");
            $popularSites = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Recent logs
            $stmt = $this->db->query("
                SELECT dl.*, c.first_name, c.last_name
                FROM dive_logs dl
                JOIN customers c ON dl.customer_id = c.id
                ORDER BY dl.created_at DESC
                LIMIT 5
            ");
            $recentLogs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $overallStats = [];
            $topDivers = [];
            $popularSites = [];
            $recentLogs = [];
        }

        $this->view('divelog/dashboard', [
            'title' => 'Dive Log Dashboard',
            'overallStats' => $overallStats,
            'topDivers' => $topDivers,
            'popularSites' => $popularSites,
            'recentLogs' => $recentLogs
        ]);
    }
}
