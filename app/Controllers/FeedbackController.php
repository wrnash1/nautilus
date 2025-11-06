<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

/**
 * Feedback & Support Ticket Controller
 * Handles feedback submission, ticket management, and voting
 */
class FeedbackController extends Controller
{
    /**
     * Show feedback submission form
     */
    public function create(): void
    {
        // Get current page URL if provided
        $pageUrl = $_GET['page'] ?? '';

        // Get user info if logged in
        $user = $_SESSION['user'] ?? null;

        // Auto-detect browser and OS
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $browser = $this->detectBrowser($userAgent);
        $os = $this->detectOS($userAgent);

        // Get system info
        $nautilus_version = 'Beta 1';
        $php_version = PHP_VERSION;

        $db = Database::getInstance()->getConnection();
        $mysql_version = $db->query('SELECT VERSION()')->fetchColumn();

        $this->view('feedback/create', [
            'pageUrl' => $pageUrl,
            'user' => $user,
            'browser' => $browser,
            'os' => $os,
            'nautilus_version' => $nautilus_version,
            'php_version' => $php_version,
            'mysql_version' => $mysql_version,
            'page_title' => 'Submit Feedback'
        ]);
    }

    /**
     * Submit feedback ticket
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/feedback/create');
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            // Generate unique ticket number
            $ticketNumber = $this->generateTicketNumber();

            // Get submitter info
            $userId = $_SESSION['user']['id'] ?? null;
            $submitterName = $_POST['submitter_name'] ?? '';
            $submitterEmail = $_POST['submitter_email'] ?? '';
            $submitterPhone = $_POST['submitter_phone'] ?? null;
            $diveShopName = $_POST['dive_shop_name'] ?? null;
            $diveShopLocation = $_POST['dive_shop_location'] ?? null;

            // Ticket details
            $ticketType = $_POST['ticket_type'] ?? 'bug';
            $severity = $_POST['severity'] ?? 'medium';
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';

            // Context
            $pageUrl = $_POST['page_url'] ?? null;
            $browser = $_POST['browser'] ?? null;
            $os = $_POST['operating_system'] ?? null;
            $screenResolution = $_POST['screen_resolution'] ?? null;
            $nautilusVersion = $_POST['nautilus_version'] ?? 'Beta 1';
            $phpVersion = $_POST['php_version'] ?? null;
            $mysqlVersion = $_POST['mysql_version'] ?? null;

            // Reproduction info
            $stepsToReproduce = $_POST['steps_to_reproduce'] ?? null;
            $expectedBehavior = $_POST['expected_behavior'] ?? null;
            $actualBehavior = $_POST['actual_behavior'] ?? null;
            $errorLogs = $_POST['error_logs'] ?? null;

            // Validate required fields
            if (empty($submitterName) || empty($submitterEmail) || empty($title) || empty($description)) {
                $_SESSION['error'] = 'Please fill in all required fields';
                $this->redirect('/feedback/create');
                return;
            }

            // Insert ticket
            $stmt = $db->prepare("
                INSERT INTO feedback_tickets (
                    ticket_number, submitted_by_user_id, submitted_by_name, submitted_by_email,
                    submitted_by_phone, dive_shop_name, dive_shop_location,
                    ticket_type, severity, title, description,
                    page_url, browser, operating_system, screen_resolution,
                    nautilus_version, php_version, mysql_version,
                    steps_to_reproduce, expected_behavior, actual_behavior, error_logs,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'new')
            ");

            $stmt->execute([
                $ticketNumber,
                $userId,
                $submitterName,
                $submitterEmail,
                $submitterPhone,
                $diveShopName,
                $diveShopLocation,
                $ticketType,
                $severity,
                $title,
                $description,
                $pageUrl,
                $browser,
                $os,
                $screenResolution,
                $nautilusVersion,
                $phpVersion,
                $mysqlVersion,
                $stepsToReproduce,
                $expectedBehavior,
                $actualBehavior,
                $errorLogs
            ]);

            $ticketId = $db->lastInsertId();

            // Handle categories
            if (!empty($_POST['categories'])) {
                $categories = $_POST['categories'];
                foreach ($categories as $categoryId) {
                    $stmt = $db->prepare("
                        INSERT INTO feedback_ticket_categories (ticket_id, category_id)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$ticketId, $categoryId]);
                }
            }

            // Handle screenshot uploads
            if (!empty($_FILES['screenshots']['name'][0])) {
                $screenshots = $this->handleScreenshotUploads($ticketId);
                if (!empty($screenshots)) {
                    $stmt = $db->prepare("UPDATE feedback_tickets SET screenshots = ? WHERE id = ?");
                    $stmt->execute([json_encode($screenshots), $ticketId]);
                }
            }

            // Send notification email to development team
            $this->sendNewTicketNotification($ticketId);

            $_SESSION['success'] = "Thank you! Your feedback ticket #{$ticketNumber} has been submitted. You'll receive updates at {$submitterEmail}";
            $this->redirect('/feedback/success?ticket=' . $ticketNumber);

        } catch (\Exception $e) {
            error_log('Feedback submission error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to submit feedback. Please try again.';
            $this->redirect('/feedback/create');
        }
    }

    /**
     * Show ticket details
     */
    public function show(string $ticketNumber): void
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT ft.*, u.first_name, u.last_name
            FROM feedback_tickets ft
            LEFT JOIN users u ON ft.submitted_by_user_id = u.id
            WHERE ft.ticket_number = ?
        ");
        $stmt->execute([$ticketNumber]);
        $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ticket) {
            $_SESSION['error'] = 'Ticket not found';
            $this->redirect('/feedback/list');
            return;
        }

        // Get comments
        $stmt = $db->prepare("
            SELECT * FROM feedback_ticket_comments
            WHERE ticket_id = ?
            ORDER BY created_at ASC
        ");
        $stmt->execute([$ticket['id']]);
        $comments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get categories
        $stmt = $db->prepare("
            SELECT fc.* FROM feedback_categories fc
            JOIN feedback_ticket_categories ftc ON fc.id = ftc.category_id
            WHERE ftc.ticket_id = ?
        ");
        $stmt->execute([$ticket['id']]);
        $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get votes count
        $stmt = $db->prepare("SELECT COUNT(*) FROM feedback_ticket_votes WHERE ticket_id = ?");
        $stmt->execute([$ticket['id']]);
        $votesCount = $stmt->fetchColumn();

        $this->view('feedback/show', [
            'ticket' => $ticket,
            'comments' => $comments,
            'categories' => $categories,
            'votes_count' => $votesCount,
            'page_title' => 'Ticket #' . $ticketNumber
        ]);
    }

    /**
     * List all tickets (admin view)
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole(['admin']);

        $db = Database::getInstance()->getConnection();

        $status = $_GET['status'] ?? null;
        $type = $_GET['type'] ?? null;
        $search = $_GET['search'] ?? null;

        $sql = "
            SELECT ft.*, u.first_name, u.last_name,
                   (SELECT COUNT(*) FROM feedback_ticket_comments WHERE ticket_id = ft.id) as comment_count
            FROM feedback_tickets ft
            LEFT JOIN users u ON ft.submitted_by_user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if ($status) {
            $sql .= " AND ft.status = ?";
            $params[] = $status;
        }

        if ($type) {
            $sql .= " AND ft.ticket_type = ?";
            $params[] = $type;
        }

        if ($search) {
            $sql .= " AND (ft.title LIKE ? OR ft.description LIKE ? OR ft.ticket_number LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY
            CASE
                WHEN ft.severity = 'critical' THEN 1
                WHEN ft.severity = 'high' THEN 2
                WHEN ft.severity = 'medium' THEN 3
                WHEN ft.severity = 'low' THEN 4
            END,
            ft.submitted_at DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('feedback/index', [
            'tickets' => $tickets,
            'page_title' => 'Feedback Tickets'
        ]);
    }

    /**
     * Success page after submission
     */
    public function success(): void
    {
        $ticketNumber = $_GET['ticket'] ?? '';

        $this->view('feedback/success', [
            'ticket_number' => $ticketNumber,
            'page_title' => 'Feedback Submitted'
        ]);
    }

    /**
     * Generate unique ticket number
     */
    private function generateTicketNumber(): string
    {
        $year = date('Y');
        $db = Database::getInstance()->getConnection();

        // Get count of tickets this year
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM feedback_tickets
            WHERE ticket_number LIKE ?
        ");
        $stmt->execute(["TICKET-{$year}-%"]);
        $count = $stmt->fetchColumn() + 1;

        return sprintf("TICKET-%s-%04d", $year, $count);
    }

    /**
     * Handle screenshot uploads
     */
    private function handleScreenshotUploads(int $ticketId): array
    {
        $uploadDir = "public/uploads/feedback/{$ticketId}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $screenshots = [];
        $files = $_FILES['screenshots'];

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $filename = uniqid() . '_' . basename($files['name'][$i]);
                $filepath = $uploadDir . $filename;

                if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                    $screenshots[] = $filepath;
                }
            }
        }

        return $screenshots;
    }

    /**
     * Send notification email
     */
    private function sendNewTicketNotification(int $ticketId): void
    {
        // TODO: Implement email notification
        // For now, just log it
        error_log("New ticket submitted: ID {$ticketId}");
    }

    /**
     * Detect browser from user agent
     */
    private function detectBrowser(string $userAgent): string
    {
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        if (strpos($userAgent, 'Opera') !== false) return 'Opera';
        return 'Unknown';
    }

    /**
     * Detect OS from user agent
     */
    private function detectOS(string $userAgent): string
    {
        if (strpos($userAgent, 'Windows') !== false) return 'Windows';
        if (strpos($userAgent, 'Mac') !== false) return 'macOS';
        if (strpos($userAgent, 'Linux') !== false) return 'Linux';
        if (strpos($userAgent, 'iPhone') !== false) return 'iOS';
        if (strpos($userAgent, 'iPad') !== false) return 'iPadOS';
        if (strpos($userAgent, 'Android') !== false) return 'Android';
        return 'Unknown';
    }
}
