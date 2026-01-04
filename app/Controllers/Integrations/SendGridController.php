<?php

namespace App\Controllers\Integrations;

use App\Core\Controller;
use App\Core\Database;

/**
 * SendGrid Email Integration Controller
 * Handles newsletter sending, transactional emails, and campaign management
 */
class SendGridController extends Controller
{
    private $apiKey;
    private $fromEmail;
    private $fromName;

    public function __construct()
    {
        parent::__construct();
        $this->loadConfig();
    }

    private function loadConfig()
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT setting_key, setting_value 
            FROM settings 
            WHERE tenant_id = ? AND setting_key LIKE 'sendgrid_%'
        ");
        $stmt->execute([$tenantId]);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            switch ($row['setting_key']) {
                case 'sendgrid_api_key':
                    $this->apiKey = $row['setting_value'];
                    break;
                case 'sendgrid_from_email':
                    $this->fromEmail = $row['setting_value'];
                    break;
                case 'sendgrid_from_name':
                    $this->fromName = $row['setting_value'];
                    break;
            }
        }
    }

    /**
     * SendGrid settings page
     */
    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('settings.view');

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT setting_key, setting_value 
            FROM settings 
            WHERE tenant_id = ? AND setting_key LIKE 'sendgrid_%'
        ");
        $stmt->execute([$tenantId]);
        $settings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        // Get email templates
        $stmt = $db->prepare("
            SELECT * FROM email_templates 
            WHERE tenant_id = ? 
            ORDER BY name
        ");
        $stmt->execute([$tenantId]);
        $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get recent sends
        $stmt = $db->prepare("
            SELECT * FROM email_logs 
            WHERE tenant_id = ? 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        $stmt->execute([$tenantId]);
        $recentSends = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('integrations/sendgrid', [
            'pageTitle' => 'Email Marketing (SendGrid)',
            'settings' => $settings,
            'templates' => $templates,
            'recentSends' => $recentSends
        ]);
    }

    /**
     * Save SendGrid configuration
     */
    public function saveConfig()
    {
        $this->requireAuth();
        $this->requirePermission('settings.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/integrations/sendgrid');
            return;
        }

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        $configKeys = [
            'sendgrid_api_key',
            'sendgrid_from_email',
            'sendgrid_from_name',
            'sendgrid_reply_to',
            'sendgrid_enabled'
        ];

        foreach ($configKeys as $key) {
            $value = $_POST[$key] ?? '';

            // Don't overwrite API key if empty
            if ($key === 'sendgrid_api_key' && empty($value)) {
                continue;
            }

            $stmt = $db->prepare("
                INSERT INTO settings (tenant_id, setting_key, setting_value, updated_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
            ");
            $stmt->execute([$tenantId, $key, $value]);
        }

        $_SESSION['success'] = 'SendGrid settings saved successfully.';
        redirect('/store/integrations/sendgrid');
    }

    /**
     * Test SendGrid connection
     */
    public function testConnection()
    {
        $this->requireAuth();

        if (empty($this->apiKey)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'API key not configured']);
            return;
        }

        $ch = curl_init('https://api.sendgrid.com/v3/user/profile');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        header('Content-Type: application/json');
        if ($httpCode === 200) {
            echo json_encode(['success' => true, 'message' => 'Connection successful!']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Connection failed. Check API key.']);
        }
    }

    /**
     * Send email to single recipient
     */
    public function sendEmail($to, $subject, $htmlContent, $textContent = null)
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'SendGrid not configured'];
        }

        $data = [
            'personalizations' => [
                ['to' => [['email' => $to]]]
            ],
            'from' => [
                'email' => $this->fromEmail,
                'name' => $this->fromName
            ],
            'subject' => $subject,
            'content' => [
                ['type' => 'text/html', 'value' => $htmlContent]
            ]
        ];

        if ($textContent) {
            array_unshift($data['content'], ['type' => 'text/plain', 'value' => $textContent]);
        }

        return $this->sendRequest($data, $to, $subject);
    }

    /**
     * Send bulk email (newsletter)
     */
    public function sendBulkEmail()
    {
        $this->requireAuth();
        $this->requirePermission('marketing.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'POST required']);
            return;
        }

        $subject = $_POST['subject'] ?? '';
        $htmlContent = $_POST['content'] ?? '';
        $templateId = $_POST['template_id'] ?? null;
        $segment = $_POST['segment'] ?? 'all'; // all, active, customers, divers

        if (empty($subject) || empty($htmlContent)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Subject and content required']);
            return;
        }

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        // Get recipients based on segment
        $recipients = $this->getRecipients($segment, $tenantId, $db);

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            // Personalize content
            $personalizedContent = $this->personalizeContent($htmlContent, $recipient);

            $result = $this->sendEmail($recipient['email'], $subject, $personalizedContent);

            if ($result['success']) {
                $sent++;
            } else {
                $failed++;
            }

            // Rate limiting - SendGrid allows 100 emails/second
            usleep(10000); // 10ms delay
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'sent' => $sent,
            'failed' => $failed,
            'total' => count($recipients)
        ]);
    }

    /**
     * Email templates
     */
    public function templates()
    {
        $this->requireAuth();
        $this->requirePermission('marketing.view');

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM email_templates WHERE tenant_id = ? ORDER BY name");
        $stmt->execute([$tenantId]);
        $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('integrations/sendgrid-templates', [
            'pageTitle' => 'Email Templates',
            'templates' => $templates
        ]);
    }

    /**
     * Create/edit template
     */
    public function saveTemplate()
    {
        $this->requireAuth();
        $this->requirePermission('marketing.edit');

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $htmlContent = $_POST['html_content'] ?? '';
        $category = $_POST['category'] ?? 'general';

        if ($id) {
            $stmt = $db->prepare("
                UPDATE email_templates 
                SET name = ?, subject = ?, html_content = ?, category = ?, updated_at = NOW()
                WHERE id = ? AND tenant_id = ?
            ");
            $stmt->execute([$name, $subject, $htmlContent, $category, $id, $tenantId]);
        } else {
            $stmt = $db->prepare("
                INSERT INTO email_templates (tenant_id, name, subject, html_content, category, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$tenantId, $name, $subject, $htmlContent, $category]);
        }

        $_SESSION['success'] = 'Template saved successfully.';
        redirect('/store/integrations/sendgrid/templates');
    }

    /**
     * Get recipients by segment
     */
    private function getRecipients($segment, $tenantId, $db)
    {
        switch ($segment) {
            case 'newsletter':
                $stmt = $db->prepare("
                    SELECT email, name as first_name, '' as last_name 
                    FROM newsletter_subscriptions 
                    WHERE tenant_id = ? AND is_active = 1
                ");
                break;
            case 'customers':
                $stmt = $db->prepare("
                    SELECT email, first_name, last_name 
                    FROM customers 
                    WHERE tenant_id = ? AND email IS NOT NULL AND email != ''
                ");
                break;
            case 'divers':
                $stmt = $db->prepare("
                    SELECT c.email, c.first_name, c.last_name 
                    FROM customers c
                    INNER JOIN customer_certifications cc ON c.id = cc.customer_id
                    WHERE c.tenant_id = ? AND c.email IS NOT NULL AND c.email != ''
                    GROUP BY c.id
                ");
                break;
            default: // all
                $stmt = $db->prepare("
                    SELECT email, first_name, last_name FROM customers 
                    WHERE tenant_id = ? AND email IS NOT NULL AND email != ''
                    UNION
                    SELECT email, name as first_name, '' as last_name 
                    FROM newsletter_subscriptions 
                    WHERE tenant_id = ? AND is_active = 1
                ");
                $stmt->execute([$tenantId, $tenantId]);
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Personalize email content with merge tags
     */
    private function personalizeContent($content, $recipient)
    {
        $replacements = [
            '{{first_name}}' => $recipient['first_name'] ?? 'Diver',
            '{{last_name}}' => $recipient['last_name'] ?? '',
            '{{email}}' => $recipient['email'] ?? '',
            '{{name}}' => trim(($recipient['first_name'] ?? '') . ' ' . ($recipient['last_name'] ?? '')) ?: 'Diver'
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Send request to SendGrid API
     */
    private function sendRequest($data, $toEmail, $subject)
    {
        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log the send
        $this->logEmail($toEmail, $subject, $httpCode === 202);

        return [
            'success' => $httpCode === 202,
            'httpCode' => $httpCode,
            'response' => $response
        ];
    }

    /**
     * Log email send to database
     */
    private function logEmail($to, $subject, $success)
    {
        try {
            $tenantId = $_SESSION['tenant_id'] ?? 1;
            $userId = $_SESSION['user']['id'] ?? null;
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("
                INSERT INTO email_logs (tenant_id, user_id, to_email, subject, status, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$tenantId, $userId, $to, $subject, $success ? 'sent' : 'failed']);
        } catch (\Exception $e) {
            error_log("Email log failed: " . $e->getMessage());
        }
    }
}
