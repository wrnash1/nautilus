<?php

namespace App\Controllers\Club;

use App\Core\Controller;
use App\Services\Club\DivingClubService;

class ClubController extends Controller
{
    private DivingClubService $clubService;

    public function __construct()
    {
        parent::__construct();
        $this->clubService = new DivingClubService($this->db);
    }

    /**
     * Display clubs list
     */
    public function index(): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->query("
                SELECT dc.*,
                       (SELECT COUNT(*) FROM club_memberships cm WHERE cm.club_id = dc.id AND cm.membership_status = 'active') as active_members
                FROM diving_clubs dc
                WHERE dc.is_active = 1
                ORDER BY dc.club_name
            ");
            $clubs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $clubs = [];
        }

        $this->view('club/index', [
            'title' => 'Diving Clubs',
            'clubs' => $clubs
        ]);
    }

    /**
     * Show create club form
     */
    public function create(): void
    {
        $this->checkPermission('customers.create');

        $this->view('club/create', [
            'title' => 'Create Diving Club'
        ]);
    }

    /**
     * Store new club
     */
    public function store(): void
    {
        $this->checkPermission('customers.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/clubs');
            return;
        }

        $result = $this->clubService->createClub([
            'tenant_id' => 1,
            'club_name' => $_POST['club_name'] ?? '',
            'club_code' => $_POST['club_code'] ?? '',
            'description' => $_POST['description'] ?? '',
            'club_type' => $_POST['club_type'] ?? 'general',
            'meeting_schedule' => $_POST['meeting_schedule'] ?? '',
            'meeting_location' => $_POST['meeting_location'] ?? '',
            'membership_type' => $_POST['membership_type'] ?? 'open',
            'min_certification_level' => $_POST['min_certification_level'] ?? null,
            'annual_dues' => (float)($_POST['annual_dues'] ?? 0),
            'discount_percentage' => (float)($_POST['discount_percentage'] ?? 0)
        ]);

        if ($result['success']) {
            $_SESSION['flash_success'] = 'Club created successfully';
            $this->redirect('/store/clubs/' . $result['club_id']);
        } else {
            $_SESSION['flash_error'] = $result['error'] ?? 'Failed to create club';
            $this->redirect('/store/clubs/create');
        }
    }

    /**
     * Show club details
     */
    public function show(int $id): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->prepare("SELECT * FROM diving_clubs WHERE id = ?");
            $stmt->execute([$id]);
            $club = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$club) {
                $_SESSION['flash_error'] = 'Club not found';
                $this->redirect('/store/clubs');
                return;
            }

            // Get members
            $stmt = $this->db->prepare("
                SELECT cm.*, c.first_name, c.last_name, c.email, c.phone
                FROM club_memberships cm
                JOIN customers c ON cm.customer_id = c.id
                WHERE cm.club_id = ?
                ORDER BY cm.join_date DESC
            ");
            $stmt->execute([$id]);
            $members = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get upcoming events
            $stmt = $this->db->prepare("
                SELECT * FROM club_events
                WHERE club_id = ? AND event_date >= CURDATE()
                ORDER BY event_date
                LIMIT 5
            ");
            $stmt->execute([$id]);
            $events = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $club = null;
            $members = [];
            $events = [];
        }

        $this->view('club/show', [
            'title' => $club['club_name'] ?? 'Club',
            'club' => $club,
            'members' => $members,
            'events' => $events
        ]);
    }

    /**
     * Add member to club
     */
    public function addMember(int $id): void
    {
        $this->checkPermission('customers.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/clubs/' . $id);
            return;
        }

        $customerId = (int)($_POST['customer_id'] ?? 0);
        if (!$customerId) {
            $_SESSION['flash_error'] = 'Please select a customer';
            $this->redirect('/store/clubs/' . $id);
            return;
        }

        $result = $this->clubService->addMember($id, $customerId, 1, [
            'member_role' => $_POST['member_role'] ?? 'member',
            'dues_paid' => isset($_POST['dues_paid'])
        ]);

        if ($result['success']) {
            $_SESSION['flash_success'] = 'Member added successfully';
        } else {
            $_SESSION['flash_error'] = $result['error'] ?? 'Failed to add member';
        }

        $this->redirect('/store/clubs/' . $id);
    }

    /**
     * Show club events
     */
    public function events(int $id): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->prepare("SELECT * FROM diving_clubs WHERE id = ?");
            $stmt->execute([$id]);
            $club = $stmt->fetch(\PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare("
                SELECT ce.*,
                       (SELECT COUNT(*) FROM club_event_registrations WHERE event_id = ce.id) as registered_count
                FROM club_events ce
                WHERE ce.club_id = ?
                ORDER BY ce.event_date DESC
            ");
            $stmt->execute([$id]);
            $events = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $club = null;
            $events = [];
        }

        $this->view('club/events', [
            'title' => ($club['club_name'] ?? 'Club') . ' - Events',
            'club' => $club,
            'events' => $events
        ]);
    }

    /**
     * Create club event
     */
    public function createEvent(int $id): void
    {
        $this->checkPermission('customers.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/clubs/' . $id . '/events');
            return;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO club_events (
                    tenant_id, club_id, event_name, description, event_type,
                    event_date, start_time, end_time, location, max_participants,
                    registration_deadline, member_price, non_member_price
                ) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                $_POST['event_name'] ?? '',
                $_POST['description'] ?? '',
                $_POST['event_type'] ?? 'dive',
                $_POST['event_date'] ?? date('Y-m-d'),
                $_POST['start_time'] ?? null,
                $_POST['end_time'] ?? null,
                $_POST['location'] ?? '',
                (int)($_POST['max_participants'] ?? 0) ?: null,
                $_POST['registration_deadline'] ?? null,
                (float)($_POST['member_price'] ?? 0),
                (float)($_POST['non_member_price'] ?? 0)
            ]);
            $_SESSION['flash_success'] = 'Event created successfully';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to create event: ' . $e->getMessage();
        }

        $this->redirect('/store/clubs/' . $id . '/events');
    }

    /**
     * All clubs dashboard
     */
    public function dashboard(): void
    {
        $this->checkPermission('customers.view');

        try {
            // Total clubs
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM diving_clubs WHERE is_active = 1");
            $totalClubs = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            // Total members
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM club_memberships WHERE membership_status = 'active'");
            $totalMembers = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            // Upcoming events
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM club_events WHERE event_date >= CURDATE()");
            $upcomingEvents = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            // Recent clubs
            $stmt = $this->db->query("SELECT * FROM diving_clubs WHERE is_active = 1 ORDER BY created_at DESC LIMIT 5");
            $recentClubs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $totalClubs = 0;
            $totalMembers = 0;
            $upcomingEvents = 0;
            $recentClubs = [];
        }

        $this->view('club/dashboard', [
            'title' => 'Club Dashboard',
            'stats' => [
                'total_clubs' => $totalClubs,
                'total_members' => $totalMembers,
                'upcoming_events' => $upcomingEvents
            ],
            'recentClubs' => $recentClubs
        ]);
    }
}
