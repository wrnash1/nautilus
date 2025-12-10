<?php

namespace App\Services\Marketing;

use PDO;

/**
 * SMS Marketing Service
 * Send and track SMS campaigns
 */
class SMSService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Queue SMS for sending
     */
    public function queueSMS(array $smsData): array
    {
        // Validate phone number (basic validation)
        if (!$this->validatePhoneNumber($smsData['to_phone'])) {
            return [
                'success' => false,
                'error' => 'Invalid phone number format'
            ];
        }

        // Validate message length
        if (strlen($smsData['message']) > 1600) {
            return [
                'success' => false,
                'error' => 'Message exceeds maximum length of 1600 characters'
            ];
        }

        // Calculate segment count (160 chars per SMS)
        $segmentCount = ceil(strlen($smsData['message']) / 160);

        // Get active provider
        $provider = $this->getActiveProvider($smsData['tenant_id']);
        if (!$provider) {
            return [
                'success' => false,
                'error' => 'No active SMS provider configured'
            ];
        }

        // Queue the SMS
        $stmt = $this->db->prepare("
            INSERT INTO sms_queue (
                tenant_id, provider_id, to_phone, customer_id, message, segment_count,
                campaign_id, workflow_id, sender_id, scheduled_for, priority, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");

        $stmt->execute([
            $smsData['tenant_id'],
            $provider['id'],
            $smsData['to_phone'],
            $smsData['customer_id'] ?? null,
            $smsData['message'],
            $segmentCount,
            $smsData['campaign_id'] ?? null,
            $smsData['workflow_id'] ?? null,
            $smsData['sender_id'] ?? $provider['sender_id'],
            $smsData['scheduled_for'] ?? null,
            $smsData['priority'] ?? 'normal'
        ]);

        return [
            'success' => true,
            'sms_id' => $this->db->lastInsertId(),
            'segment_count' => $segmentCount,
            'estimated_cost' => $segmentCount * $provider['cost_per_sms']
        ];
    }

    /**
     * Process SMS queue
     */
    public function processQueue(int $limit = 100): array
    {
        $stmt = $this->db->prepare("
            SELECT sq.*, sp.*
            FROM sms_queue sq
            JOIN sms_providers sp ON sq.provider_id = sp.id
            WHERE sq.status = 'pending'
              AND (sq.scheduled_for IS NULL OR sq.scheduled_for <= NOW())
              AND (sq.send_after IS NULL OR sq.send_after <= NOW())
            ORDER BY
                CASE sq.priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'normal' THEN 3
                    WHEN 'low' THEN 4
                END,
                sq.created_at ASC
            LIMIT ?
        ");

        $stmt->execute([$limit]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach ($messages as $message) {
            try {
                $result = $this->sendSMS($message);

                if ($result['success']) {
                    $sent++;
                } else {
                    $failed++;
                    $errors[] = [
                        'sms_id' => $message['id'],
                        'error' => $result['error']
                    ];
                }
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'sms_id' => $message['id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => true,
            'processed' => count($messages),
            'sent' => $sent,
            'failed' => $failed,
            'errors' => $errors
        ];
    }

    /**
     * Send individual SMS
     */
    private function sendSMS(array $message): array
    {
        // In production, integrate with actual SMS provider (Twilio, Nexmo, etc.)
        // This is a simplified version

        $providerName = $message['provider_name'];

        switch ($providerName) {
            case 'twilio':
                return $this->sendViaTwilio($message);
            case 'nexmo':
                return $this->sendViaNexmo($message);
            default:
                return $this->mockSend($message);
        }
    }

    /**
     * Mock send (for demonstration)
     */
    private function mockSend(array $message): array
    {
        // Simulate successful send
        $providerId = 'mock_' . uniqid();

        $this->db->prepare("
            UPDATE sms_queue
            SET status = 'sent',
                sent_at = NOW(),
                provider_message_id = ?
            WHERE id = ?
        ")->execute([$providerId, $message['id']]);

        // Update provider stats
        $this->db->prepare("
            UPDATE sms_providers
            SET total_sent = total_sent + ?,
                daily_sent = daily_sent + ?
            WHERE id = ?
        ")->execute([
            $message['segment_count'],
            $message['segment_count'],
            $message['provider_id']
        ]);

        return [
            'success' => true,
            'provider_id' => $providerId
        ];
    }

    /**
     * Send via Twilio (placeholder for actual implementation)
     */
    private function sendViaTwilio(array $message): array
    {
        // In production:
        // $twilio = new \Twilio\Rest\Client($message['account_sid'], $message['auth_token']);
        // $result = $twilio->messages->create($message['to_phone'], [
        //     'from' => $message['from_phone'],
        //     'body' => $message['message']
        // ]);

        return $this->mockSend($message);
    }

    /**
     * Send via Nexmo (placeholder for actual implementation)
     */
    private function sendViaNexmo(array $message): array
    {
        // In production:
        // $nexmo = new \Vonage\Client(new \Vonage\Client\Credentials\Basic($message['api_key'], $message['api_secret']));
        // $result = $nexmo->message()->send([...]);

        return $this->mockSend($message);
    }

    /**
     * Queue SMS from template
     */
    public function queueFromTemplate(int $templateId, string $toPhone, array $variables, int $tenantId): array
    {
        // Get template
        $stmt = $this->db->prepare("
            SELECT * FROM sms_templates WHERE id = ? AND tenant_id = ?
        ");
        $stmt->execute([$templateId, $tenantId]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$template) {
            return [
                'success' => false,
                'error' => 'Template not found'
            ];
        }

        // Replace variables in template
        $message = $this->replaceVariables($template['message_template'], $variables);

        // Queue the SMS
        return $this->queueSMS([
            'tenant_id' => $tenantId,
            'to_phone' => $toPhone,
            'message' => $message,
            'customer_id' => $variables['customer_id'] ?? null
        ]);
    }

    /**
     * Replace template variables
     */
    private function replaceVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace("{{" . $key . "}}", $value, $template);
        }
        return $template;
    }

    /**
     * Validate phone number
     */
    private function validatePhoneNumber(string $phone): bool
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Check if it's a valid length (10-15 digits)
        return strlen($cleaned) >= 10 && strlen($cleaned) <= 15;
    }

    /**
     * Get active SMS provider
     */
    private function getActiveProvider(int $tenantId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM sms_providers
            WHERE tenant_id = ?
              AND is_active = TRUE
            ORDER BY is_primary DESC, id ASC
            LIMIT 1
        ");

        $stmt->execute([$tenantId]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        return $provider ?: null;
    }

    /**
     * Track SMS delivery status (webhook handler)
     */
    public function updateDeliveryStatus(int $smsId, string $status, ?string $errorCode = null, ?string $errorMessage = null): bool
    {
        $updateData = [
            'delivery_status' => $status
        ];

        if ($status === 'delivered') {
            $updateData['status'] = 'delivered';
            $updateData['delivered_at'] = 'NOW()';

            $this->db->prepare("
                UPDATE sms_queue
                SET status = 'delivered',
                    delivered_at = NOW(),
                    delivery_status = ?
                WHERE id = ?
            ")->execute([$status, $smsId]);

            // Update provider stats
            $this->db->prepare("
                UPDATE sms_providers sp
                JOIN sms_queue sq ON sp.id = sq.provider_id
                SET sp.total_delivered = sp.total_delivered + 1
                WHERE sq.id = ?
            ")->execute([$smsId]);

        } elseif ($status === 'failed') {
            $this->db->prepare("
                UPDATE sms_queue
                SET status = 'failed',
                    failed_at = NOW(),
                    delivery_status = ?,
                    error_code = ?,
                    error_message = ?
                WHERE id = ?
            ")->execute([$status, $errorCode, $errorMessage, $smsId]);

            // Update provider stats
            $this->db->prepare("
                UPDATE sms_providers sp
                JOIN sms_queue sq ON sp.id = sq.provider_id
                SET sp.total_failed = sp.total_failed + 1
                WHERE sq.id = ?
            ")->execute([$smsId]);
        }

        return true;
    }

    /**
     * Track SMS link click
     */
    public function trackClick(int $smsId): bool
    {
        $this->db->prepare("
            UPDATE sms_queue
            SET clicked = TRUE,
                clicked_at = NOW()
            WHERE id = ?
        ")->execute([$smsId]);

        return true;
    }

    /**
     * Get SMS statistics
     */
    public function getStatistics(int $tenantId, int $days = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total_sent,
                COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed,
                COUNT(CASE WHEN clicked = TRUE THEN 1 END) as clicked,
                SUM(segment_count) as total_segments,
                SUM(cost) as total_cost
            FROM sms_queue
            WHERE tenant_id = ?
              AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ");

        $stmt->execute([$tenantId, $days]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $stats['delivery_rate'] = $stats['total_sent'] > 0
            ? round(($stats['delivered'] / $stats['total_sent']) * 100, 2)
            : 0;

        $stats['click_rate'] = $stats['delivered'] > 0
            ? round(($stats['clicked'] / $stats['delivered']) * 100, 2)
            : 0;

        return [
            'success' => true,
            'statistics' => $stats
        ];
    }
}
