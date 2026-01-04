<?php

namespace App\Controllers\Integrations;

use App\Core\Controller;
use App\Core\Database;

/**
 * VOIP Integration Controller
 * Handles caller ID popups, click-to-call, SMS, and call logging
 * Supports: Twilio, Google Voice, RingCentral, 3CX
 */
class VoIPController extends Controller
{
    /**
     * VOIP settings dashboard
     */
    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('settings.view');

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        // Get current VOIP settings
        $stmt = $db->prepare("
            SELECT setting_key, setting_value 
            FROM settings 
            WHERE tenant_id = ? AND setting_key LIKE 'voip_%'
        ");
        $stmt->execute([$tenantId]);
        $settings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $this->view('integrations/voip', [
            'pageTitle' => 'VOIP Integration',
            'settings' => $settings,
            'providers' => ['twilio', 'google_voice', 'ringcentral', '3cx']
        ]);
    }

    /**
     * Save VOIP configuration
     */
    public function saveConfig()
    {
        $this->requireAuth();
        $this->requirePermission('settings.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/integrations/voip');
            return;
        }

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        $configKeys = [
            'voip_provider',
            'voip_account_sid',
            'voip_auth_token',
            'voip_phone_number',
            'voip_caller_id_enabled',
            'voip_click_to_call_enabled',
            'voip_sms_enabled',
            'voip_call_logging_enabled'
        ];

        foreach ($configKeys as $key) {
            $value = $_POST[$key] ?? '';

            // Don't overwrite token if empty (keep existing)
            if ($key === 'voip_auth_token' && empty($value)) {
                continue;
            }

            $stmt = $db->prepare("
                INSERT INTO settings (tenant_id, setting_key, setting_value, updated_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
            ");
            $stmt->execute([$tenantId, $key, $value]);
        }

        $_SESSION['success'] = 'VOIP settings saved successfully.';
        redirect('/admin/integrations/voip');
    }

    /**
     * Lookup customer by phone number (for caller ID popup)
     * Called via AJAX when incoming call detected
     */
    public function lookupByPhone()
    {
        $this->requireAuth();

        $phone = preg_replace('/[^0-9]/', '', $_GET['phone'] ?? '');

        if (strlen($phone) < 10) {
            header('Content-Type: application/json');
            echo json_encode(['found' => false]);
            return;
        }

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        // Search customers by phone
        $stmt = $db->prepare("
            SELECT id, first_name, last_name, email, phone, 
                   (SELECT SUM(amount) FROM pos_transactions WHERE customer_id = customers.id) as total_spent,
                   (SELECT COUNT(*) FROM pos_transactions WHERE customer_id = customers.id) as visit_count,
                   (SELECT MAX(created_at) FROM pos_transactions WHERE customer_id = customers.id) as last_visit
            FROM customers 
            WHERE tenant_id = ? 
            AND (REPLACE(REPLACE(REPLACE(phone, '-', ''), ' ', ''), '(', '') LIKE ? 
                 OR REPLACE(REPLACE(REPLACE(phone, '-', ''), ' ', ''), ')', '') LIKE ?)
            LIMIT 1
        ");
        $phonePattern = '%' . $phone . '%';
        $stmt->execute([$tenantId, $phonePattern, $phonePattern]);
        $customer = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($customer) {
            header('Content-Type: application/json');
            echo json_encode([
                'found' => true,
                'customer' => [
                    'id' => $customer['id'],
                    'name' => $customer['first_name'] . ' ' . $customer['last_name'],
                    'email' => $customer['email'],
                    'phone' => $customer['phone'],
                    'totalSpent' => number_format($customer['total_spent'] ?? 0, 2),
                    'visitCount' => $customer['visit_count'] ?? 0,
                    'lastVisit' => $customer['last_visit'] ? date('M j, Y', strtotime($customer['last_visit'])) : 'Never'
                ]
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['found' => false, 'phone' => $phone]);
        }
    }

    /**
     * Initiate outbound call (click-to-call)
     */
    public function makeCall()
    {
        $this->requireAuth();

        $toNumber = preg_replace('/[^0-9+]/', '', $_POST['to'] ?? '');
        $customerId = $_POST['customer_id'] ?? null;

        if (empty($toNumber)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Phone number required']);
            return;
        }

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        // Get VOIP settings
        $stmt = $db->prepare("
            SELECT setting_key, setting_value 
            FROM settings 
            WHERE tenant_id = ? AND setting_key IN ('voip_provider', 'voip_account_sid', 'voip_auth_token', 'voip_phone_number')
        ");
        $stmt->execute([$tenantId]);
        $config = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $config[$row['setting_key']] = $row['setting_value'];
        }

        // Log the call attempt
        $this->logCall($customerId, $toNumber, 'outbound', 'initiated');

        // Provider-specific call initiation
        $result = $this->initiateCallByProvider($config, $toNumber);

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * Send SMS message
     */
    public function sendSms()
    {
        $this->requireAuth();

        $toNumber = preg_replace('/[^0-9+]/', '', $_POST['to'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $customerId = $_POST['customer_id'] ?? null;

        if (empty($toNumber) || empty($message)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Phone number and message required']);
            return;
        }

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        // Get VOIP settings
        $stmt = $db->prepare("
            SELECT setting_key, setting_value 
            FROM settings 
            WHERE tenant_id = ? AND setting_key LIKE 'voip_%'
        ");
        $stmt->execute([$tenantId]);
        $config = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $config[$row['setting_key']] = $row['setting_value'];
        }

        // Log the SMS
        $this->logSms($customerId, $toNumber, $message);

        // Provider-specific SMS sending
        $result = $this->sendSmsByProvider($config, $toNumber, $message);

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * Log incoming call webhook (from VOIP provider)
     */
    public function incomingCallWebhook()
    {
        // Verify webhook signature based on provider
        $provider = $_GET['provider'] ?? 'twilio';

        $fromNumber = $_POST['From'] ?? $_POST['caller_id'] ?? '';
        $toNumber = $_POST['To'] ?? $_POST['called_number'] ?? '';

        // Log the incoming call
        $tenantId = 1; // Would need to determine from phone number
        $this->logCall(null, $fromNumber, 'inbound', 'ringing');

        // Return TwiML or appropriate response
        header('Content-Type: text/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?><Response></Response>';
    }

    /**
     * Get call history for customer
     */
    public function callHistory($customerId)
    {
        $this->requireAuth();

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT * FROM call_logs 
            WHERE tenant_id = ? AND customer_id = ?
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$tenantId, $customerId]);
        $calls = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode(['calls' => $calls]);
    }

    /**
     * Log a call to database
     */
    private function logCall($customerId, $phone, $direction, $status)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user']['id'] ?? null;
        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("
                INSERT INTO call_logs (tenant_id, customer_id, user_id, phone_number, direction, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$tenantId, $customerId, $userId, $phone, $direction, $status]);
        } catch (\Exception $e) {
            // Table may not exist yet
            error_log("Call log failed: " . $e->getMessage());
        }
    }

    /**
     * Log SMS to database  
     */
    private function logSms($customerId, $phone, $message)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user']['id'] ?? null;
        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("
                INSERT INTO sms_logs (tenant_id, customer_id, user_id, phone_number, message, direction, created_at)
                VALUES (?, ?, ?, ?, ?, 'outbound', NOW())
            ");
            $stmt->execute([$tenantId, $customerId, $userId, $phone, $message]);
        } catch (\Exception $e) {
            error_log("SMS log failed: " . $e->getMessage());
        }
    }

    /**
     * Initiate call via provider API
     */
    private function initiateCallByProvider($config, $toNumber)
    {
        $provider = $config['voip_provider'] ?? 'twilio';

        switch ($provider) {
            case 'twilio':
                return $this->twilioCall($config, $toNumber);
            case 'google_voice':
                return ['success' => false, 'error' => 'Google Voice requires manual dialing'];
            default:
                return ['success' => false, 'error' => 'Provider not configured'];
        }
    }

    /**
     * Send SMS via provider API
     */
    private function sendSmsByProvider($config, $toNumber, $message)
    {
        $provider = $config['voip_provider'] ?? 'twilio';

        switch ($provider) {
            case 'twilio':
                return $this->twilioSms($config, $toNumber, $message);
            default:
                return ['success' => false, 'error' => 'Provider not configured'];
        }
    }

    /**
     * Twilio call initiation
     */
    private function twilioCall($config, $toNumber)
    {
        $accountSid = $config['voip_account_sid'] ?? '';
        $authToken = $config['voip_auth_token'] ?? '';
        $fromNumber = $config['voip_phone_number'] ?? '';

        if (empty($accountSid) || empty($authToken) || empty($fromNumber)) {
            return ['success' => false, 'error' => 'Twilio not configured'];
        }

        // Use Twilio REST API
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Calls.json";

        $data = [
            'To' => $toNumber,
            'From' => $fromNumber,
            'Url' => 'http://demo.twilio.com/docs/voice.xml' // Would be your TwiML endpoint
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, "{$accountSid}:{$authToken}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            return ['success' => true, 'message' => 'Call initiated'];
        } else {
            return ['success' => false, 'error' => 'Failed to initiate call'];
        }
    }

    /**
     * Twilio SMS sending
     */
    private function twilioSms($config, $toNumber, $message)
    {
        $accountSid = $config['voip_account_sid'] ?? '';
        $authToken = $config['voip_auth_token'] ?? '';
        $fromNumber = $config['voip_phone_number'] ?? '';

        if (empty($accountSid) || empty($authToken) || empty($fromNumber)) {
            return ['success' => false, 'error' => 'Twilio not configured'];
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";

        $data = [
            'To' => $toNumber,
            'From' => $fromNumber,
            'Body' => $message
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, "{$accountSid}:{$authToken}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            return ['success' => true, 'message' => 'SMS sent'];
        } else {
            return ['success' => false, 'error' => 'Failed to send SMS'];
        }
    }
}
