<?php

namespace App\Services\Club;

use PDO;

/**
 * Diving Club Service
 * Manage diving clubs, memberships, events, and communications
 */
class DivingClubService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new diving club
     */
    public function createClub(array $clubData): array
    {
        // Generate unique club code if not provided
        if (empty($clubData['club_code'])) {
            $clubData['club_code'] = $this->generateClubCode($clubData['club_name']);
        }

        $stmt = $this->db->prepare("
            INSERT INTO diving_clubs (
                tenant_id, club_name, club_code, description, club_type,
                meeting_schedule, meeting_location, membership_type,
                min_certification_level, annual_dues, discount_percentage
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $clubData['tenant_id'],
            $clubData['club_name'],
            $clubData['club_code'],
            $clubData['description'] ?? null,
            $clubData['club_type'] ?? 'general',
            $clubData['meeting_schedule'] ?? null,
            $clubData['meeting_location'] ?? null,
            $clubData['membership_type'] ?? 'open',
            $clubData['min_certification_level'] ?? null,
            $clubData['annual_dues'] ?? 0.00,
            $clubData['discount_percentage'] ?? 0.00
        ]);

        $clubId = $this->db->lastInsertId();

        return [
            'success' => true,
            'club_id' => $clubId,
            'club_code' => $clubData['club_code']
        ];
    }

    /**
     * Add member to club
     */
    public function addMember(int $clubId, int $customerId, int $tenantId, array $memberData = []): array
    {
        // Get club details
        $club = $this->getClub($clubId);
        if (!$club['success']) {
            return $club;
        }

        // Check if already a member
        $stmt = $this->db->prepare("
            SELECT id FROM club_memberships
            WHERE club_id = ? AND customer_id = ?
        ");
        $stmt->execute([$clubId, $customerId]);
        if ($stmt->fetch()) {
            return [
                'success' => false,
                'error' => 'Customer is already a member of this club'
            ];
        }

        // Check capacity
        $clubDetails = $club['club'];
        if ($clubDetails['max_members'] && $clubDetails['current_member_count'] >= $clubDetails['max_members']) {
            return [
                'success' => false,
                'error' => 'Club is at maximum capacity'
            ];
        }

        // Generate member number
        $memberNumber = $this->generateMemberNumber($clubId, $tenantId);

        // Determine status based on membership type
        $status = 'active';
        if ($clubDetails['membership_type'] === 'application_required') {
            $status = 'pending';
        }

        // Calculate membership dates
        $joinDate = date('Y-m-d');
        $startDate = $memberData['membership_start_date'] ?? $joinDate;
        $endDate = date('Y-m-d', strtotime($startDate . ' +1 year'));

        $stmt = $this->db->prepare("
            INSERT INTO club_memberships (
                tenant_id, club_id, customer_id, member_number,
                membership_status, member_role, join_date,
                membership_start_date, membership_end_date,
                annual_dues, dues_paid
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $tenantId,
            $clubId,
            $customerId,
            $memberNumber,
            $status,
            $memberData['member_role'] ?? 'member',
            $joinDate,
            $startDate,
            $endDate,
            $clubDetails['annual_dues'],
            $memberData['dues_paid'] ?? false
        ]);

        // Update member count
        $this->db->prepare("
            UPDATE diving_clubs
            SET current_member_count = current_member_count + 1
            WHERE id = ?
        ")->execute([$clubId]);

        return [
            'success' => true,
            'membership_id' => $this->db->lastInsertId(),
            'member_number' => $memberNumber,
            'status' => $status
        ];
    }

    /**
     * Create club event
     */
    public function createEvent(array $eventData): array
    {
        $stmt = $this->db->prepare("
            INSERT INTO club_events (
                tenant_id, club_id, event_name, event_type,
                description, event_date, start_time, end_time,
                location, max_participants, members_only,
                member_cost, non_member_cost, registration_required,
                registration_deadline, organizer_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $eventData['tenant_id'],
            $eventData['club_id'],
            $eventData['event_name'],
            $eventData['event_type'],
            $eventData['description'] ?? null,
            $eventData['event_date'],
            $eventData['start_time'] ?? null,
            $eventData['end_time'] ?? null,
            $eventData['location'] ?? null,
            $eventData['max_participants'] ?? null,
            $eventData['members_only'] ?? false,
            $eventData['member_cost'] ?? 0.00,
            $eventData['non_member_cost'] ?? 0.00,
            $eventData['registration_required'] ?? true,
            $eventData['registration_deadline'] ?? null,
            $eventData['organizer_id'] ?? null
        ]);

        $eventId = $this->db->lastInsertId();

        // Update status to open registration if appropriate
        if ($eventData['registration_required'] ?? true) {
            $this->db->prepare("
                UPDATE club_events
                SET status = 'open_registration'
                WHERE id = ?
            ")->execute([$eventId]);
        }

        return [
            'success' => true,
            'event_id' => $eventId
        ];
    }

    /**
     * Register for event
     */
    public function registerForEvent(int $eventId, int $customerId, int $tenantId, array $registrationData = []): array
    {
        // Get event details
        $stmt = $this->db->prepare("
            SELECT ce.*, dc.discount_percentage
            FROM club_events ce
            JOIN diving_clubs dc ON ce.club_id = dc.id
            WHERE ce.id = ?
        ");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            return [
                'success' => false,
                'error' => 'Event not found'
            ];
        }

        // Check if registration is open
        if (!in_array($event['status'], ['open_registration', 'scheduled'])) {
            return [
                'success' => false,
                'error' => 'Registration is not open for this event'
            ];
        }

        // Check capacity
        if ($event['max_participants'] && $event['current_participants'] >= $event['max_participants']) {
            $registrationStatus = 'waitlist';
        } else {
            $registrationStatus = 'confirmed';
        }

        // Determine if member
        $stmt = $this->db->prepare("
            SELECT id FROM club_memberships
            WHERE club_id = ? AND customer_id = ? AND membership_status = 'active'
        ");
        $stmt->execute([$event['club_id'], $customerId]);
        $isMember = $stmt->fetch() !== false;

        // Calculate cost
        $registrationType = $isMember ? 'member' : 'non_member';
        $amountDue = $isMember ? $event['member_cost'] : $event['non_member_cost'];

        $stmt = $this->db->prepare("
            INSERT INTO club_event_registrations (
                tenant_id, event_id, customer_id, registration_type,
                guests_count, amount_due, registration_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $tenantId,
            $eventId,
            $customerId,
            $registrationType,
            $registrationData['guests_count'] ?? 0,
            $amountDue,
            $registrationStatus
        ]);

        // Update participant count
        if ($registrationStatus === 'confirmed') {
            $this->db->prepare("
                UPDATE club_events
                SET current_participants = current_participants + 1
                WHERE id = ?
            ")->execute([$eventId]);
        }

        return [
            'success' => true,
            'registration_id' => $this->db->lastInsertId(),
            'registration_status' => $registrationStatus,
            'amount_due' => $amountDue
        ];
    }

    /**
     * Get club details
     */
    public function getClub(int $clubId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM diving_clubs WHERE id = ?
        ");
        $stmt->execute([$clubId]);
        $club = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$club) {
            return [
                'success' => false,
                'error' => 'Club not found'
            ];
        }

        return [
            'success' => true,
            'club' => $club
        ];
    }

    /**
     * Get club members
     */
    public function getClubMembers(int $clubId, string $status = 'active'): array
    {
        $stmt = $this->db->prepare("
            SELECT
                cm.*,
                c.first_name,
                c.last_name,
                c.email,
                c.phone
            FROM club_memberships cm
            JOIN customers c ON cm.customer_id = c.id
            WHERE cm.club_id = ?
              AND cm.membership_status = ?
            ORDER BY cm.join_date DESC
        ");
        $stmt->execute([$clubId, $status]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'members' => $members,
            'count' => count($members)
        ];
    }

    /**
     * Get upcoming club events
     */
    public function getUpcomingEvents(int $clubId, int $daysAhead = 90): array
    {
        $stmt = $this->db->prepare("
            SELECT
                ce.*,
                (ce.max_participants - ce.current_participants) as spots_available
            FROM club_events ce
            WHERE ce.club_id = ?
              AND ce.event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
              AND ce.status IN ('scheduled', 'open_registration')
            ORDER BY ce.event_date ASC
        ");
        $stmt->execute([$clubId, $daysAhead]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'events' => $events,
            'count' => count($events)
        ];
    }

    /**
     * Send club communication
     */
    public function sendCommunication(int $clubId, int $tenantId, array $communicationData): array
    {
        // Get target recipients
        $recipients = $this->getRecipients($clubId, $communicationData['target_audience']);

        $stmt = $this->db->prepare("
            INSERT INTO club_communications (
                tenant_id, club_id, communication_type, subject, content,
                target_audience, send_via, total_recipients, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'sent')
        ");

        $stmt->execute([
            $tenantId,
            $clubId,
            $communicationData['communication_type'],
            $communicationData['subject'],
            $communicationData['content'],
            $communicationData['target_audience'],
            json_encode($communicationData['send_via'] ?? ['email']),
            count($recipients)
        ]);

        // In production, would queue actual email/SMS sending

        return [
            'success' => true,
            'communication_id' => $this->db->lastInsertId(),
            'recipients_count' => count($recipients)
        ];
    }

    /**
     * Get recipients based on target audience
     */
    private function getRecipients(int $clubId, string $targetAudience): array
    {
        $sql = "
            SELECT c.email, c.first_name, c.last_name
            FROM club_memberships cm
            JOIN customers c ON cm.customer_id = c.id
            WHERE cm.club_id = ?
        ";

        switch ($targetAudience) {
            case 'active_only':
                $sql .= " AND cm.membership_status = 'active'";
                break;
            case 'officers_only':
                $sql .= " AND cm.member_role IN ('officer', 'board_member', 'president', 'vice_president', 'treasurer', 'secretary')";
                break;
            case 'all_members':
            default:
                $sql .= " AND cm.membership_status IN ('active', 'pending')";
                break;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clubId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate unique club code
     */
    private function generateClubCode(string $clubName): string
    {
        // Take first letters of each word
        $words = explode(' ', $clubName);
        $code = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $code .= strtoupper($word[0]);
            }
        }

        // Add random digits if needed
        $code .= rand(10, 99);

        return substr($code, 0, 10);
    }

    /**
     * Generate member number
     */
    private function generateMemberNumber(int $clubId, int $tenantId): string
    {
        $count = $this->db->query("
            SELECT COUNT(*) FROM club_memberships WHERE club_id = {$clubId}
        ")->fetchColumn();

        return "M{$clubId}-" . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    }
}
