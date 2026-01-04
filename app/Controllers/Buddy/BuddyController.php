<?php

namespace App\Controllers\Buddy;

use App\Core\Controller;

class BuddyController extends Controller
{
    /**
     * Display buddy pairs list
     */
    public function index(): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->query("
                SELECT bp.*,
                       c1.first_name as diver1_first, c1.last_name as diver1_last, c1.email as diver1_email,
                       c2.first_name as diver2_first, c2.last_name as diver2_last, c2.email as diver2_email
                FROM buddy_pairs bp
                JOIN customers c1 ON bp.diver1_id = c1.id
                JOIN customers c2 ON bp.diver2_id = c2.id
                WHERE bp.status IN ('active', 'inactive')
                ORDER BY bp.paired_at DESC
            ");
            $pairs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Stats
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM buddy_pairs WHERE status = 'active'");
            $activePairs = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            $stmt = $this->db->query("SELECT COUNT(*) as count FROM buddy_pairs WHERE relationship_type = 'permanent' AND status = 'active'");
            $permanentPairs = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

        } catch (\Exception $e) {
            $pairs = [];
            $activePairs = 0;
            $permanentPairs = 0;
        }

        $this->view('buddy/index', [
            'title' => 'Buddy Pairs',
            'pairs' => $pairs,
            'stats' => [
                'active_pairs' => $activePairs,
                'permanent_pairs' => $permanentPairs
            ]
        ]);
    }

    /**
     * Show create buddy pair form
     */
    public function create(): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->query("
                SELECT id, first_name, last_name, email
                FROM customers
                WHERE status = 'active'
                ORDER BY last_name, first_name
            ");
            $customers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $stmt = $this->db->query("
                SELECT id, trip_name, start_date
                FROM dive_trips
                WHERE start_date >= CURDATE()
                ORDER BY start_date
            ");
            $trips = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $customers = [];
            $trips = [];
        }

        $this->view('buddy/create', [
            'title' => 'Create Buddy Pair',
            'customers' => $customers,
            'trips' => $trips
        ]);
    }

    /**
     * Store new buddy pair
     */
    public function store(): void
    {
        $this->checkPermission('customers.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/buddies');
            return;
        }

        $diver1 = (int)($_POST['diver1_id'] ?? 0);
        $diver2 = (int)($_POST['diver2_id'] ?? 0);

        if (!$diver1 || !$diver2 || $diver1 === $diver2) {
            $_SESSION['flash_error'] = 'Please select two different divers';
            $this->redirect('/store/buddies/create');
            return;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO buddy_pairs (
                    tenant_id, diver1_id, diver2_id, relationship_type,
                    paired_for_trip_id, paired_for_date, notes
                ) VALUES (1, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $diver1,
                $diver2,
                $_POST['relationship_type'] ?? 'trip_specific',
                $_POST['trip_id'] ?: null,
                $_POST['paired_for_date'] ?: null,
                $_POST['notes'] ?? ''
            ]);

            $_SESSION['flash_success'] = 'Buddy pair created successfully';
            $this->redirect('/store/buddies');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to create buddy pair: ' . $e->getMessage();
            $this->redirect('/store/buddies/create');
        }
    }

    /**
     * Show buddy pair details
     */
    public function show(int $id): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->prepare("
                SELECT bp.*,
                       c1.first_name as diver1_first, c1.last_name as diver1_last, c1.email as diver1_email, c1.phone as diver1_phone,
                       c2.first_name as diver2_first, c2.last_name as diver2_last, c2.email as diver2_email, c2.phone as diver2_phone,
                       t.trip_name
                FROM buddy_pairs bp
                JOIN customers c1 ON bp.diver1_id = c1.id
                JOIN customers c2 ON bp.diver2_id = c2.id
                LEFT JOIN dive_trips t ON bp.paired_for_trip_id = t.id
                WHERE bp.id = ?
            ");
            $stmt->execute([$id]);
            $pair = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$pair) {
                $_SESSION['flash_error'] = 'Buddy pair not found';
                $this->redirect('/store/buddies');
                return;
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error loading buddy pair';
            $this->redirect('/store/buddies');
            return;
        }

        $this->view('buddy/show', [
            'title' => 'Buddy Pair Details',
            'pair' => $pair
        ]);
    }

    /**
     * Update buddy pair status
     */
    public function updateStatus(int $id): void
    {
        $this->checkPermission('customers.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/buddies/' . $id);
            return;
        }

        try {
            $status = $_POST['status'] ?? 'active';
            $stmt = $this->db->prepare("
                UPDATE buddy_pairs SET status = ?, dissolved_at = ?, dissolved_reason = ?
                WHERE id = ?
            ");

            $dissolvedAt = in_array($status, ['completed', 'dissolved']) ? date('Y-m-d H:i:s') : null;
            $dissolvedReason = in_array($status, ['completed', 'dissolved']) ? ($_POST['reason'] ?? '') : null;

            $stmt->execute([$status, $dissolvedAt, $dissolvedReason, $id]);
            $_SESSION['flash_success'] = 'Buddy pair status updated';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to update status';
        }

        $this->redirect('/store/buddies/' . $id);
    }

    /**
     * Record a dive together
     */
    public function recordDive(int $id): void
    {
        $this->checkPermission('customers.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/buddies/' . $id);
            return;
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE buddy_pairs
                SET dives_together = dives_together + 1, last_dive_date = ?
                WHERE id = ?
            ");
            $stmt->execute([$_POST['dive_date'] ?? date('Y-m-d'), $id]);
            $_SESSION['flash_success'] = 'Dive recorded for buddy pair';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to record dive';
        }

        $this->redirect('/store/buddies/' . $id);
    }

    /**
     * Find buddy match for a diver
     */
    public function findMatch(): void
    {
        $this->checkPermission('customers.view');

        try {
            $stmt = $this->db->query("
                SELECT id, first_name, last_name, email
                FROM customers
                WHERE status = 'active'
                ORDER BY last_name, first_name
            ");
            $customers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $customers = [];
        }

        $this->view('buddy/find-match', [
            'title' => 'Find Buddy Match',
            'customers' => $customers
        ]);
    }
}
