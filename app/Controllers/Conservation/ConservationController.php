<?php

namespace App\Controllers\Conservation;

use App\Core\Controller;

class ConservationController extends Controller
{
    /**
     * Display conservation initiatives list
     */
    public function index(): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->query("
                SELECT ci.*,
                       (SELECT COUNT(*) FROM conservation_participants cp WHERE cp.initiative_id = ci.id AND cp.is_active = 1) as active_participants
                FROM conservation_initiatives ci
                ORDER BY ci.start_date DESC
            ");
            $initiatives = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Stats
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM conservation_initiatives WHERE is_ongoing = 1");
            $ongoingCount = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            $stmt = $this->db->query("SELECT SUM(volunteer_hours) as total FROM conservation_initiatives");
            $totalHours = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            $stmt = $this->db->query("SELECT SUM(funds_raised) as total FROM conservation_initiatives");
            $fundsRaised = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

        } catch (\Exception $e) {
            $initiatives = [];
            $ongoingCount = 0;
            $totalHours = 0;
            $fundsRaised = 0;
        }

        $this->view('conservation/index', [
            'title' => 'Conservation Initiatives',
            'initiatives' => $initiatives,
            'stats' => [
                'ongoing' => $ongoingCount,
                'total_hours' => $totalHours,
                'funds_raised' => $fundsRaised
            ]
        ]);
    }

    /**
     * Show create initiative form
     */
    public function create(): void
    {
        $this->checkPermission('customers.edit');

        $this->view('conservation/create', [
            'title' => 'Create Conservation Initiative'
        ]);
    }

    /**
     * Store new initiative
     */
    public function store(): void
    {
        $this->checkPermission('customers.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/conservation');
            return;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO conservation_initiatives (
                    tenant_id, initiative_name, initiative_type, description,
                    partner_organizations, certification_program,
                    start_date, end_date, is_ongoing, meeting_frequency
                ) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $partners = $_POST['partner_organizations'] ?? '';
            $partnersJson = $partners ? json_encode(array_map('trim', explode(',', $partners))) : null;

            $stmt->execute([
                $_POST['initiative_name'] ?? '',
                $_POST['initiative_type'] ?? 'cleanup',
                $_POST['description'] ?? '',
                $partnersJson,
                $_POST['certification_program'] ?? null,
                $_POST['start_date'] ?? date('Y-m-d'),
                $_POST['end_date'] ?: null,
                isset($_POST['is_ongoing']) ? 1 : 0,
                $_POST['meeting_frequency'] ?? null
            ]);

            $_SESSION['flash_success'] = 'Conservation initiative created successfully';
            $this->redirect('/store/conservation/' . $this->db->lastInsertId());
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to create initiative: ' . $e->getMessage();
            $this->redirect('/store/conservation/create');
        }
    }

    /**
     * Show initiative details
     */
    public function show(int $id): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->prepare("SELECT * FROM conservation_initiatives WHERE id = ?");
            $stmt->execute([$id]);
            $initiative = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$initiative) {
                $_SESSION['flash_error'] = 'Initiative not found';
                $this->redirect('/store/conservation');
                return;
            }

            // Get participants
            $stmt = $this->db->prepare("
                SELECT cp.*, c.first_name, c.last_name, c.email
                FROM conservation_participants cp
                JOIN customers c ON cp.customer_id = c.id
                WHERE cp.initiative_id = ?
                ORDER BY cp.join_date DESC
            ");
            $stmt->execute([$id]);
            $participants = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error loading initiative';
            $this->redirect('/store/conservation');
            return;
        }

        $this->view('conservation/show', [
            'title' => $initiative['initiative_name'],
            'initiative' => $initiative,
            'participants' => $participants
        ]);
    }

    /**
     * Add participant to initiative
     */
    public function addParticipant(int $id): void
    {
        $this->checkPermission('customers.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/conservation/' . $id);
            return;
        }

        $customerId = (int)($_POST['customer_id'] ?? 0);
        if (!$customerId) {
            $_SESSION['flash_error'] = 'Please select a participant';
            $this->redirect('/store/conservation/' . $id);
            return;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO conservation_participants (
                    tenant_id, initiative_id, customer_id, participation_level, join_date
                ) VALUES (1, ?, ?, ?, CURDATE())
            ");
            $stmt->execute([$id, $customerId, $_POST['participation_level'] ?? 'volunteer']);

            // Update initiative participant count
            $stmt = $this->db->prepare("
                UPDATE conservation_initiatives
                SET participants_count = (SELECT COUNT(*) FROM conservation_participants WHERE initiative_id = ?)
                WHERE id = ?
            ");
            $stmt->execute([$id, $id]);

            $_SESSION['flash_success'] = 'Participant added successfully';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to add participant';
        }

        $this->redirect('/store/conservation/' . $id);
    }

    /**
     * Log volunteer hours
     */
    public function logHours(int $id): void
    {
        $this->checkPermission('customers.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/conservation/' . $id);
            return;
        }

        $participantId = (int)($_POST['participant_id'] ?? 0);
        $hours = (int)($_POST['hours'] ?? 0);

        if (!$participantId || $hours <= 0) {
            $_SESSION['flash_error'] = 'Invalid data';
            $this->redirect('/store/conservation/' . $id);
            return;
        }

        try {
            // Update participant hours
            $stmt = $this->db->prepare("
                UPDATE conservation_participants
                SET volunteer_hours = volunteer_hours + ?, last_activity_date = CURDATE()
                WHERE id = ? AND initiative_id = ?
            ");
            $stmt->execute([$hours, $participantId, $id]);

            // Update initiative total
            $stmt = $this->db->prepare("
                UPDATE conservation_initiatives
                SET volunteer_hours = (SELECT SUM(volunteer_hours) FROM conservation_participants WHERE initiative_id = ?)
                WHERE id = ?
            ");
            $stmt->execute([$id, $id]);

            $_SESSION['flash_success'] = 'Hours logged successfully';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to log hours';
        }

        $this->redirect('/store/conservation/' . $id);
    }

    /**
     * Dashboard view
     */
    public function dashboard(): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM conservation_initiatives");
            $totalInitiatives = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            $stmt = $this->db->query("SELECT SUM(participants_count) as total FROM conservation_initiatives");
            $totalParticipants = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            $stmt = $this->db->query("SELECT SUM(volunteer_hours) as total FROM conservation_initiatives");
            $totalHours = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            $stmt = $this->db->query("SELECT SUM(funds_raised) as total FROM conservation_initiatives");
            $fundsRaised = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            // Recent initiatives
            $stmt = $this->db->query("SELECT * FROM conservation_initiatives ORDER BY created_at DESC LIMIT 5");
            $recentInitiatives = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // By type
            $stmt = $this->db->query("
                SELECT initiative_type, COUNT(*) as count
                FROM conservation_initiatives
                GROUP BY initiative_type
            ");
            $byType = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $totalInitiatives = 0;
            $totalParticipants = 0;
            $totalHours = 0;
            $fundsRaised = 0;
            $recentInitiatives = [];
            $byType = [];
        }

        $this->view('conservation/dashboard', [
            'title' => 'Conservation Dashboard',
            'stats' => [
                'total_initiatives' => $totalInitiatives,
                'total_participants' => $totalParticipants,
                'total_hours' => $totalHours,
                'funds_raised' => $fundsRaised
            ],
            'recentInitiatives' => $recentInitiatives,
            'byType' => $byType
        ]);
    }
}
