<?php

namespace App\Controllers;

use App\Services\Equipment\MaintenanceService;
use App\Services\Audit\AuditService;
use App\Helpers\Auth;

/**
 * Equipment Maintenance Controller
 *
 * Manages equipment maintenance tracking, scheduling, and history
 */
class MaintenanceController
{
    private MaintenanceService $maintenanceService;
    private AuditService $auditService;

    public function __construct()
    {
        $this->maintenanceService = new MaintenanceService();
        $this->auditService = new AuditService();
    }

    /**
     * Show maintenance dashboard
     */
    public function index()
    {
        if (!Auth::check() || !Auth::hasPermission('maintenance.view')) {
            redirect('/login');
            return;
        }

        $statistics = $this->maintenanceService->getStatistics();
        $equipmentNeeding = $this->maintenanceService->getEquipmentNeedingMaintenance();
        $scheduledMaintenance = $this->maintenanceService->getScheduledMaintenance('scheduled', 10);
        $upcomingInspections = $this->maintenanceService->getUpcomingInspections(14);

        require __DIR__ . '/../Views/maintenance/index.php';
    }

    /**
     * Show equipment maintenance history
     */
    public function equipmentHistory(int $equipmentId)
    {
        if (!Auth::check() || !Auth::hasPermission('maintenance.view')) {
            redirect('/login');
            return;
        }

        $history = $this->maintenanceService->getMaintenanceHistory($equipmentId);

        // Get equipment details
        $stmt = \App\Core\Database::getInstance()->getConnection()->prepare(
            "SELECT re.*, ret.name as equipment_type
             FROM rental_equipment re
             LEFT JOIN rental_equipment_types ret ON re.equipment_type_id = ret.id
             WHERE re.id = ?"
        );
        $stmt->execute([$equipmentId]);
        $equipment = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$equipment) {
            $_SESSION['error'] = 'Equipment not found';
            redirect('/maintenance');
            return;
        }

        require __DIR__ . '/../Views/maintenance/equipment_history.php';
    }

    /**
     * Show record maintenance form
     */
    public function create()
    {
        if (!Auth::check() || !Auth::hasPermission('maintenance.create')) {
            redirect('/login');
            return;
        }

        $maintenanceTypes = $this->maintenanceService->getMaintenanceTypes();

        require __DIR__ . '/../Views/maintenance/create.php';
    }

    /**
     * Store maintenance record
     */
    public function store()
    {
        if (!Auth::check() || !Auth::hasPermission('maintenance.create')) {
            $_SESSION['error'] = 'Unauthorized';
            redirect('/maintenance');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request';
            redirect('/maintenance');
            return;
        }

        $data = [
            'equipment_id' => (int)$_POST['equipment_id'],
            'maintenance_type' => $_POST['maintenance_type'],
            'maintenance_date' => $_POST['maintenance_date'],
            'performed_by' => Auth::userId(),
            'description' => $_POST['description'] ?? null,
            'parts_replaced' => $_POST['parts_replaced'] ?? null,
            'cost' => (float)($_POST['cost'] ?? 0),
            'next_service_date' => $_POST['next_service_date'] ?? null
        ];

        $maintenanceId = $this->maintenanceService->recordMaintenance($data);

        if ($maintenanceId) {
            // Log action
            $this->auditService->log(
                Auth::userId(),
                'create',
                'maintenance',
                $maintenanceId,
                $data
            );

            $_SESSION['success'] = 'Maintenance record created successfully';
        } else {
            $_SESSION['error'] = 'Failed to create maintenance record';
        }

        redirect('/maintenance');
    }

    /**
     * Show schedule maintenance form
     */
    public function schedule()
    {
        if (!Auth::check() || !Auth::hasPermission('maintenance.schedule')) {
            redirect('/login');
            return;
        }

        $maintenanceTypes = $this->maintenanceService->getMaintenanceTypes();

        require __DIR__ . '/../Views/maintenance/schedule.php';
    }

    /**
     * Store maintenance schedule
     */
    public function storeSchedule()
    {
        if (!Auth::check() || !Auth::hasPermission('maintenance.schedule')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $data = [
            'equipment_id' => (int)$_POST['equipment_id'],
            'scheduled_date' => $_POST['scheduled_date'],
            'maintenance_type' => $_POST['maintenance_type'],
            'assigned_to' => !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null,
            'notes' => $_POST['notes'] ?? null
        ];

        $scheduleId = $this->maintenanceService->scheduleMaintenance($data);

        if ($scheduleId) {
            // Log action
            $this->auditService->log(
                Auth::userId(),
                'schedule',
                'maintenance',
                $scheduleId,
                $data
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Maintenance scheduled successfully',
                'schedule_id' => $scheduleId
            ]);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to schedule maintenance'], 500);
        }
    }

    /**
     * Complete scheduled maintenance
     */
    public function completeSchedule(int $scheduleId)
    {
        if (!Auth::check() || !Auth::hasPermission('maintenance.create')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $maintenanceData = [
            'maintenance_date' => $_POST['maintenance_date'] ?? date('Y-m-d'),
            'performed_by' => Auth::userId(),
            'description' => $_POST['description'] ?? '',
            'parts_replaced' => $_POST['parts_replaced'] ?? null,
            'cost' => (float)($_POST['cost'] ?? 0),
            'next_service_date' => $_POST['next_service_date'] ?? null
        ];

        $success = $this->maintenanceService->completeScheduledMaintenance($scheduleId, $maintenanceData);

        if ($success) {
            // Log action
            $this->auditService->log(
                Auth::userId(),
                'complete',
                'maintenance_schedule',
                $scheduleId,
                $maintenanceData
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Maintenance completed and recorded successfully'
            ]);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to complete maintenance'], 500);
        }
    }

    /**
     * Cancel scheduled maintenance
     */
    public function cancelSchedule(int $scheduleId)
    {
        if (!Auth::check() || !Auth::hasPermission('maintenance.schedule')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        $reason = $_POST['reason'] ?? '';

        $success = $this->maintenanceService->cancelScheduledMaintenance($scheduleId, $reason);

        if ($success) {
            // Log action
            $this->auditService->log(
                Auth::userId(),
                'cancel',
                'maintenance_schedule',
                $scheduleId,
                ['reason' => $reason]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Maintenance schedule cancelled'
            ]);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to cancel schedule'], 500);
        }
    }

    /**
     * Show cost analysis
     */
    public function costAnalysis()
    {
        if (!Auth::check() || !Auth::hasPermission('maintenance.view')) {
            redirect('/login');
            return;
        }

        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $analysis = $this->maintenanceService->getCostAnalysis($startDate, $endDate);

        require __DIR__ . '/../Views/maintenance/cost_analysis.php';
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
