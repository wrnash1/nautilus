<?php

namespace App\Services\Communication;

use PDO;

/**
 * Customer Communication Service
 *
 * Manages SMS, push notifications, and bulk messaging
 */
class CommunicationService
{
    private PDO $db;
    private array $config;

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance()->getConnection();
        $this->loadConfig();
    }

    /**
     * Load communication configuration
     */
    private function loadConfig(): void
    {
        $this->config = [
            // SMS Configuration (Twilio)
            'sms_enabled' => $_ENV['SMS_ENABLED'] ?? false,
            'sms_provider' => $_ENV['SMS_PROVIDER'] ?? 'twilio',
            'twilio_sid' => $_ENV['TWILIO_ACCOUNT_SID'] ?? '',
            'twilio_token' => $_ENV['TWILIO_AUTH_TOKEN'] ?? '',
            'twilio_from' => $_ENV['TWILIO_FROM_NUMBER'] ?? '',

            // Push Notification Configuration (Firebase)
            'push_enabled' => $_ENV['PUSH_ENABLED'] ?? false,
            'firebase_key' => $_ENV['FIREBASE_SERVER_KEY'] ?? '',

            // General settings
            'default_sender' => $_ENV['BUSINESS_NAME'] ?? 'Nautilus Dive Shop',
            'opt_in_required' => true
        ];
    }

    /**
     * Send SMS to a customer
     */
    public function sendSMS(int $customerId, string $message, ?int $campaignId = null): bool
    {
        if (!$this->config['sms_enabled']) {
            error_log("SMS not enabled");
            return false;
        }

        // Get customer phone number
        $stmt = $this->db->prepare(
            "SELECT phone, sms_opt_in FROM customers WHERE id = ?"
        );
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer || empty($customer['phone'])) {
            error_log("Customer phone not found");
            return false;
        }

        // Check opt-in status
        if ($this->config['opt_in_required'] && !$customer['sms_opt_in']) {
            error_log("Customer has not opted in for SMS");
            return false;
        }

        // Send via configured provider
        $success = false;
        $messageId = null;
        $error = null;

        if ($this->config['sms_provider'] === 'twilio') {
            $result = $this->sendViaTwilio($customer['phone'], $message);
            $success = $result['success'];
            $messageId = $result['message_id'] ?? null;
            $error = $result['error'] ?? null;
        }

        // Log the message
        $this->logMessage(
            $customerId,
            'sms',
            $message,
            $success ? 'sent' : 'failed',
            $campaignId,
            $messageId,
            $error
        );

        return $success;
    }

    /**
     * Send push notification to a customer
     */
    public function sendPushNotification(
        int $customerId,
        string $title,
        string $body,
        ?array $data = null,
        ?int $campaignId = null
    ): bool {
        if (!$this->config['push_enabled']) {
            error_log("Push notifications not enabled");
            return false;
        }

        // Get customer device tokens
        $stmt = $this->db->prepare(
            "SELECT device_token, push_opt_in FROM customer_devices
             WHERE customer_id = ? AND push_opt_in = 1 AND device_token IS NOT NULL"
        );
        $stmt->execute([$customerId]);
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($devices)) {
            error_log("No device tokens found for customer");
            return false;
        }

        $allSuccess = true;

        foreach ($devices as $device) {
            $result = $this->sendViaFirebase($device['device_token'], $title, $body, $data);

            // Log each notification
            $this->logMessage(
                $customerId,
                'push',
                json_encode(['title' => $title, 'body' => $body, 'data' => $data]),
                $result['success'] ? 'sent' : 'failed',
                $campaignId,
                $result['message_id'] ?? null,
                $result['error'] ?? null
            );

            if (!$result['success']) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    /**
     * Send bulk SMS to multiple customers
     */
    public function sendBulkSMS(array $customerIds, string $message, string $campaignName = ''): array
    {
        $campaignId = null;

        if ($campaignName) {
            $campaignId = $this->createCampaign($campaignName, 'sms', count($customerIds));
        }

        $results = [
            'total' => count($customerIds),
            'sent' => 0,
            'failed' => 0,
            'skipped' => 0
        ];

        foreach ($customerIds as $customerId) {
            $success = $this->sendSMS($customerId, $message, $campaignId);

            if ($success) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }

            // Rate limiting - sleep for 100ms between messages
            usleep(100000);
        }

        // Update campaign stats
        if ($campaignId) {
            $this->updateCampaignStats($campaignId, $results['sent'], $results['failed']);
        }

        return $results;
    }

    /**
     * Send bulk push notifications
     */
    public function sendBulkPush(
        array $customerIds,
        string $title,
        string $body,
        ?array $data = null,
        string $campaignName = ''
    ): array {
        $campaignId = null;

        if ($campaignName) {
            $campaignId = $this->createCampaign($campaignName, 'push', count($customerIds));
        }

        $results = [
            'total' => count($customerIds),
            'sent' => 0,
            'failed' => 0,
            'skipped' => 0
        ];

        foreach ($customerIds as $customerId) {
            $success = $this->sendPushNotification($customerId, $title, $body, $data, $campaignId);

            if ($success) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }

            usleep(50000); // 50ms delay
        }

        // Update campaign stats
        if ($campaignId) {
            $this->updateCampaignStats($campaignId, $results['sent'], $results['failed']);
        }

        return $results;
    }

    /**
     * Get customer communication preferences
     */
    public function getPreferences(int $customerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT sms_opt_in, email_opt_in, push_opt_in FROM customers WHERE id = ?"
        );
        $stmt->execute([$customerId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'sms_opt_in' => false,
            'email_opt_in' => true,
            'push_opt_in' => false
        ];
    }

    /**
     * Update customer communication preferences
     */
    public function updatePreferences(int $customerId, array $preferences): bool
    {
        try {
            $updates = [];
            $params = [];

            if (isset($preferences['sms_opt_in'])) {
                $updates[] = 'sms_opt_in = ?';
                $params[] = $preferences['sms_opt_in'] ? 1 : 0;
            }

            if (isset($preferences['email_opt_in'])) {
                $updates[] = 'email_opt_in = ?';
                $params[] = $preferences['email_opt_in'] ? 1 : 0;
            }

            if (isset($preferences['push_opt_in'])) {
                $updates[] = 'push_opt_in = ?';
                $params[] = $preferences['push_opt_in'] ? 1 : 0;
            }

            if (empty($updates)) {
                return false;
            }

            $params[] = $customerId;

            $stmt = $this->db->prepare(
                "UPDATE customers SET " . implode(', ', $updates) . " WHERE id = ?"
            );

            return $stmt->execute($params);
        } catch (\Exception $e) {
            error_log("Failed to update preferences: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get message history for a customer
     */
    public function getMessageHistory(int $customerId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM communication_log
             WHERE customer_id = ?
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$customerId, $limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get campaign statistics
     */
    public function getCampaignStats(int $campaignId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM communication_campaigns WHERE id = ?"
        );
        $stmt->execute([$campaignId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get all campaigns
     */
    public function getAllCampaigns(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM communication_campaigns
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Send via Twilio
     */
    private function sendViaTwilio(string $to, string $message): array
    {
        try {
            // Normalize phone number
            $to = $this->normalizePhoneNumber($to);

            // In production, use Twilio SDK
            // For now, simulate the API call
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->config['twilio_sid']}/Messages.json";

            $data = [
                'From' => $this->config['twilio_from'],
                'To' => $to,
                'Body' => $message
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $this->config['twilio_sid'] . ':' . $this->config['twilio_token']);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                $result = json_decode($response, true);
                return [
                    'success' => true,
                    'message_id' => $result['sid'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'error' => "HTTP $httpCode: $response"
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send via Firebase Cloud Messaging
     */
    private function sendViaFirebase(string $deviceToken, string $title, string $body, ?array $data): array
    {
        try {
            $url = 'https://fcm.googleapis.com/fcm/send';

            $notification = [
                'title' => $title,
                'body' => $body,
                'sound' => 'default'
            ];

            $payload = [
                'to' => $deviceToken,
                'notification' => $notification
            ];

            if ($data) {
                $payload['data'] = $data;
            }

            $headers = [
                'Authorization: key=' . $this->config['firebase_key'],
                'Content-Type: application/json'
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                return [
                    'success' => $result['success'] ?? 0 > 0,
                    'message_id' => $result['multicast_id'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'error' => "HTTP $httpCode: $response"
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Log communication message
     */
    private function logMessage(
        int $customerId,
        string $type,
        string $message,
        string $status,
        ?int $campaignId = null,
        ?string $messageId = null,
        ?string $error = null
    ): void {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO communication_log
                 (customer_id, type, message, status, campaign_id, message_id, error, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))"
            );

            $stmt->execute([
                $customerId,
                $type,
                $message,
                $status,
                $campaignId,
                $messageId,
                $error
            ]);
        } catch (\Exception $e) {
            error_log("Failed to log message: " . $e->getMessage());
        }
    }

    /**
     * Create a communication campaign
     */
    private function createCampaign(string $name, string $type, int $targetCount): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO communication_campaigns
             (name, type, target_count, sent_count, failed_count, status, created_at)
             VALUES (?, ?, ?, 0, 0, 'sending', datetime('now'))"
        );

        $stmt->execute([$name, $type, $targetCount]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update campaign statistics
     */
    private function updateCampaignStats(int $campaignId, int $sentCount, int $failedCount): void
    {
        $stmt = $this->db->prepare(
            "UPDATE communication_campaigns
             SET sent_count = ?, failed_count = ?, status = 'completed', completed_at = datetime('now')
             WHERE id = ?"
        );

        $stmt->execute([$sentCount, $failedCount, $campaignId]);
    }

    /**
     * Normalize phone number to E.164 format
     */
    private function normalizePhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add country code if not present (assumes US +1)
        if (strlen($phone) === 10) {
            $phone = '1' . $phone;
        }

        return '+' . $phone;
    }
}
