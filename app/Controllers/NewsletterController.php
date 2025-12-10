<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class NewsletterController extends Controller
{
    /**
     * Show newsletter subscription form (public)
     */
    public function showSubscribe()
    {
        $this->view('newsletter/subscribe', [
            'pageTitle' => 'Subscribe to Newsletter'
        ]);
    }

    /**
     * Process newsletter subscription
     */
    public function subscribe()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/newsletter/subscribe');
            return;
        }

        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $name = trim($_POST['name'] ?? '');

        if (!$email) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Please provide a valid email address'
            ]);
            return;
        }

        $db = Database::getInstance()->getConnection();
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        try {
            // Check if already subscribed
            $stmt = $db->prepare("
                SELECT id, is_active FROM newsletter_subscriptions
                WHERE tenant_id = ? AND email = ?
                LIMIT 1
            ");
            $stmt->execute([$tenantId, $email]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                if ($existing['is_active']) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'This email is already subscribed'
                    ]);
                    return;
                } else {
                    // Reactivate subscription
                    $stmt = $db->prepare("
                        UPDATE newsletter_subscriptions
                        SET is_active = 1, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$existing['id']]);
                }
            } else {
                // Create new subscription
                $confirmToken = bin2hex(random_bytes(32));

                $stmt = $db->prepare("
                    INSERT INTO newsletter_subscriptions
                    (tenant_id, email, name, confirm_token, is_active, subscribed_at, created_at)
                    VALUES (?, ?, ?, ?, 1, NOW(), NOW())
                ");
                $stmt->execute([$tenantId, $email, $name, $confirmToken]);
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Successfully subscribed to newsletter!'
            ]);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Subscription failed. Please try again later.'
            ]);
        }
    }

    /**
     * Confirm newsletter subscription
     */
    public function confirm($token)
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT id FROM newsletter_subscriptions
            WHERE confirm_token = ? AND is_active = 0
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $subscription = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($subscription) {
            $stmt = $db->prepare("
                UPDATE newsletter_subscriptions
                SET is_active = 1, confirmed_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$subscription['id']]);

            $_SESSION['success'] = 'Email confirmed! You are now subscribed to our newsletter.';
        } else {
            $_SESSION['error'] = 'Invalid or expired confirmation link.';
        }

        redirect('/');
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribe($token)
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT id FROM newsletter_subscriptions
            WHERE confirm_token = ?
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $subscription = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($subscription) {
            $stmt = $db->prepare("
                UPDATE newsletter_subscriptions
                SET is_active = 0, unsubscribed_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$subscription['id']]);

            $_SESSION['success'] = 'You have been unsubscribed from our newsletter.';
        } else {
            $_SESSION['error'] = 'Invalid unsubscribe link.';
        }

        redirect('/');
    }

    /**
     * Admin: View all subscriptions
     */
    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('marketing.view');

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT * FROM newsletter_subscriptions
            WHERE tenant_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$tenantId]);
        $subscriptions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get statistics
        $stmt = $db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as unsubscribed
            FROM newsletter_subscriptions
            WHERE tenant_id = ?
        ");
        $stmt->execute([$tenantId]);
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->view('admin/newsletter/index', [
            'subscriptions' => $subscriptions,
            'stats' => $stats,
            'pageTitle' => 'Newsletter Subscriptions'
        ]);
    }

    /**
     * Admin: Export subscriptions to CSV
     */
    public function export()
    {
        $this->requireAuth();
        $this->requirePermission('marketing.view');

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT email, name, subscribed_at, is_active
            FROM newsletter_subscriptions
            WHERE tenant_id = ? AND is_active = 1
            ORDER BY subscribed_at DESC
        ");
        $stmt->execute([$tenantId]);
        $subscriptions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="newsletter_subscriptions_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Email', 'Name', 'Subscribed Date', 'Status']);

        foreach ($subscriptions as $sub) {
            fputcsv($output, [
                $sub['email'],
                $sub['name'],
                $sub['subscribed_at'],
                $sub['is_active'] ? 'Active' : 'Unsubscribed'
            ]);
        }

        fclose($output);
        exit;
    }
}
