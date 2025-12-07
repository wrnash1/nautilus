<?php

namespace App\Services\Marketing;

use PDO;

/**
 * Campaign Builder Service
 * Create and manage multi-channel marketing campaigns
 */
class CampaignBuilderService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new marketing campaign
     */
    public function createCampaign(array $campaignData): array
    {
        $stmt = $this->db->prepare("
            INSERT INTO marketing_campaigns (
                tenant_id, name, description, campaign_type, status, objective,
                start_date, end_date, timezone, target_audience, budget
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $campaignData['tenant_id'],
            $campaignData['name'],
            $campaignData['description'] ?? null,
            $campaignData['campaign_type'] ?? 'email',
            $campaignData['status'] ?? 'draft',
            $campaignData['objective'] ?? 'conversion',
            $campaignData['start_date'] ?? null,
            $campaignData['end_date'] ?? null,
            $campaignData['timezone'] ?? 'UTC',
            json_encode($campaignData['target_audience'] ?? []),
            $campaignData['budget'] ?? 0
        ]);

        $campaignId = $this->db->lastInsertId();

        return [
            'success' => true,
            'campaign_id' => $campaignId,
            'message' => 'Campaign created successfully'
        ];
    }

    /**
     * Add email content to campaign
     */
    public function addEmailContent(int $campaignId, array $emailData): array
    {
        $stmt = $this->db->prepare("
            INSERT INTO campaign_emails (
                campaign_id, tenant_id, variant_name, subject_line, preview_text,
                from_name, from_email, reply_to_email, html_content, plain_text_content,
                personalization_tags, track_opens, track_clicks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $campaignId,
            $emailData['tenant_id'],
            $emailData['variant_name'] ?? 'default',
            $emailData['subject_line'],
            $emailData['preview_text'] ?? null,
            $emailData['from_name'],
            $emailData['from_email'],
            $emailData['reply_to_email'] ?? null,
            $emailData['html_content'],
            $emailData['plain_text_content'] ?? strip_tags($emailData['html_content']),
            json_encode($emailData['personalization_tags'] ?? []),
            $emailData['track_opens'] ?? true,
            $emailData['track_clicks'] ?? true
        ]);

        return [
            'success' => true,
            'email_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Add SMS content to campaign
     */
    public function addSMSContent(int $campaignId, array $smsData): array
    {
        // Validate message length (1600 chars = 10 SMS segments)
        if (strlen($smsData['message_content']) > 1600) {
            return [
                'success' => false,
                'error' => 'SMS message exceeds maximum length of 1600 characters'
            ];
        }

        $segmentCount = ceil(strlen($smsData['message_content']) / 160);

        $stmt = $this->db->prepare("
            INSERT INTO campaign_sms (
                campaign_id, tenant_id, variant_name, message_content,
                sender_id, personalization_tags, track_clicks, segment_count
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $campaignId,
            $smsData['tenant_id'],
            $smsData['variant_name'] ?? 'default',
            $smsData['message_content'],
            $smsData['sender_id'] ?? null,
            json_encode($smsData['personalization_tags'] ?? []),
            $smsData['track_clicks'] ?? true,
            $segmentCount
        ]);

        return [
            'success' => true,
            'sms_id' => $this->db->lastInsertId(),
            'segment_count' => $segmentCount
        ];
    }

    /**
     * Add recipients to campaign based on segment
     */
    public function addRecipientsFromSegment(int $campaignId, int $segmentId, int $tenantId): array
    {
        // Get all active members of the segment
        $stmt = $this->db->prepare("
            SELECT
                sm.customer_id,
                c.email,
                c.phone,
                sm.matched_criteria
            FROM segment_members sm
            JOIN customers c ON sm.customer_id = c.id
            WHERE sm.segment_id = ?
              AND sm.tenant_id = ?
              AND sm.is_active = TRUE
              AND c.email IS NOT NULL
        ");
        $stmt->execute([$segmentId, $tenantId]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $addedCount = 0;
        foreach ($members as $member) {
            try {
                $insertStmt = $this->db->prepare("
                    INSERT IGNORE INTO campaign_recipients (
                        campaign_id, tenant_id, customer_id, email, phone,
                        segment_id, segment_match_criteria, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
                ");

                $insertStmt->execute([
                    $campaignId,
                    $tenantId,
                    $member['customer_id'],
                    $member['email'],
                    $member['phone'],
                    $segmentId,
                    $member['matched_criteria']
                ]);

                if ($insertStmt->rowCount() > 0) {
                    $addedCount++;
                }
            } catch (\Exception $e) {
                // Skip duplicates
                continue;
            }
        }

        // Update campaign estimated reach
        $this->db->prepare("
            UPDATE marketing_campaigns
            SET estimated_reach = (
                SELECT COUNT(*) FROM campaign_recipients WHERE campaign_id = ?
            )
            WHERE id = ?
        ")->execute([$campaignId, $campaignId]);

        return [
            'success' => true,
            'recipients_added' => $addedCount,
            'total_recipients' => count($members)
        ];
    }

    /**
     * Schedule campaign for sending
     */
    public function scheduleCampaign(int $campaignId, string $startDate, ?string $endDate = null): array
    {
        // Validate campaign has content
        $hasEmail = $this->db->prepare("
            SELECT COUNT(*) FROM campaign_emails WHERE campaign_id = ?
        ");
        $hasEmail->execute([$campaignId]);

        $hasSMS = $this->db->prepare("
            SELECT COUNT(*) FROM campaign_sms WHERE campaign_id = ?
        ");
        $hasSMS->execute([$campaignId]);

        if ($hasEmail->fetchColumn() == 0 && $hasSMS->fetchColumn() == 0) {
            return [
                'success' => false,
                'error' => 'Campaign must have email or SMS content before scheduling'
            ];
        }

        // Validate has recipients
        $hasRecipients = $this->db->prepare("
            SELECT COUNT(*) FROM campaign_recipients WHERE campaign_id = ?
        ");
        $hasRecipients->execute([$campaignId]);

        if ($hasRecipients->fetchColumn() == 0) {
            return [
                'success' => false,
                'error' => 'Campaign must have recipients before scheduling'
            ];
        }

        // Schedule the campaign
        $stmt = $this->db->prepare("
            UPDATE marketing_campaigns
            SET status = 'scheduled',
                start_date = ?,
                end_date = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $startDate,
            $endDate,
            $campaignId
        ]);

        return [
            'success' => true,
            'message' => 'Campaign scheduled successfully',
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }

    /**
     * Launch campaign immediately
     */
    public function launchCampaign(int $campaignId): array
    {
        $schedule = $this->scheduleCampaign($campaignId, date('Y-m-d H:i:s'));

        if (!$schedule['success']) {
            return $schedule;
        }

        // Update to active status
        $this->db->prepare("
            UPDATE marketing_campaigns
            SET status = 'active'
            WHERE id = ?
        ")->execute([$campaignId]);

        // Queue emails for sending
        $queued = $this->queueCampaignMessages($campaignId);

        return [
            'success' => true,
            'message' => 'Campaign launched successfully',
            'queued_messages' => $queued
        ];
    }

    /**
     * Queue campaign messages for delivery
     */
    private function queueCampaignMessages(int $campaignId): int
    {
        // Get campaign details
        $campaign = $this->db->prepare("
            SELECT * FROM marketing_campaigns WHERE id = ?
        ");
        $campaign->execute([$campaignId]);
        $campaignData = $campaign->fetch(PDO::FETCH_ASSOC);

        // Get email content
        $emailContent = $this->db->prepare("
            SELECT * FROM campaign_emails WHERE campaign_id = ? LIMIT 1
        ");
        $emailContent->execute([$campaignId]);
        $email = $emailContent->fetch(PDO::FETCH_ASSOC);

        // Get recipients
        $recipients = $this->db->prepare("
            SELECT * FROM campaign_recipients
            WHERE campaign_id = ?
              AND status = 'pending'
        ");
        $recipients->execute([$campaignId]);

        $queuedCount = 0;
        while ($recipient = $recipients->fetch(PDO::FETCH_ASSOC)) {
            if ($email && $recipient['email']) {
                // Personalize content
                $personalizedContent = $this->personalizeContent(
                    $email['html_content'],
                    $recipient['customer_id']
                );

                // Queue email
                $this->db->prepare("
                    INSERT INTO email_queue (
                        tenant_id, to_email, to_name, subject, html_body,
                        from_email, from_name, campaign_id, customer_id, priority, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'normal', 'pending')
                ")->execute([
                    $campaignData['tenant_id'],
                    $recipient['email'],
                    '', // Get from customer table if needed
                    $email['subject_line'],
                    $personalizedContent,
                    $email['from_email'],
                    $email['from_name'],
                    $campaignId,
                    $recipient['customer_id']
                ]);

                $queuedCount++;
            }
        }

        return $queuedCount;
    }

    /**
     * Personalize email content with customer data
     */
    private function personalizeContent(string $content, int $customerId): string
    {
        // Get customer data
        $stmt = $this->db->prepare("
            SELECT first_name, last_name, email FROM customers WHERE id = ?
        ");
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            return $content;
        }

        // Replace merge tags
        $replacements = [
            '{{first_name}}' => $customer['first_name'] ?? '',
            '{{last_name}}' => $customer['last_name'] ?? '',
            '{{email}}' => $customer['email'] ?? '',
            '{{full_name}}' => trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''))
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Get campaign performance metrics
     */
    public function getCampaignMetrics(int $campaignId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                c.*,
                (SELECT COUNT(*) FROM campaign_recipients WHERE campaign_id = c.id) as total_recipients,
                (SELECT COUNT(*) FROM campaign_recipients WHERE campaign_id = c.id AND status = 'sent') as sent,
                (SELECT COUNT(*) FROM campaign_recipients WHERE campaign_id = c.id AND status = 'delivered') as delivered,
                (SELECT COUNT(*) FROM campaign_recipients WHERE campaign_id = c.id AND opened_at IS NOT NULL) as opened,
                (SELECT COUNT(DISTINCT recipient_id) FROM campaign_link_clicks WHERE campaign_id = c.id) as clicked,
                (SELECT COUNT(*) FROM campaign_recipients WHERE campaign_id = c.id AND converted = TRUE) as conversions,
                (SELECT SUM(conversion_value) FROM campaign_recipients WHERE campaign_id = c.id AND converted = TRUE) as total_conversion_value
            FROM marketing_campaigns c
            WHERE c.id = ?
        ");

        $stmt->execute([$campaignId]);
        $metrics = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$metrics) {
            return ['success' => false, 'error' => 'Campaign not found'];
        }

        // Calculate rates
        $metrics['delivery_rate'] = $metrics['sent'] > 0
            ? round(($metrics['delivered'] / $metrics['sent']) * 100, 2)
            : 0;

        $metrics['open_rate'] = $metrics['delivered'] > 0
            ? round(($metrics['opened'] / $metrics['delivered']) * 100, 2)
            : 0;

        $metrics['click_rate'] = $metrics['delivered'] > 0
            ? round(($metrics['clicked'] / $metrics['delivered']) * 100, 2)
            : 0;

        $metrics['conversion_rate'] = $metrics['delivered'] > 0
            ? round(($metrics['conversions'] / $metrics['delivered']) * 100, 2)
            : 0;

        $metrics['roi'] = $metrics['spent'] > 0
            ? round((($metrics['total_conversion_value'] - $metrics['spent']) / $metrics['spent']) * 100, 2)
            : 0;

        return [
            'success' => true,
            'metrics' => $metrics
        ];
    }

    /**
     * Track email open
     */
    public function trackEmailOpen(int $recipientId, string $ipAddress, string $userAgent): bool
    {
        $stmt = $this->db->prepare("
            UPDATE campaign_recipients
            SET opened_at = COALESCE(opened_at, NOW()),
                total_opens = total_opens + 1,
                last_activity_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$recipientId]);
    }

    /**
     * Track link click
     */
    public function trackLinkClick(int $campaignId, int $recipientId, string $url, array $metadata = []): bool
    {
        // Get recipient and campaign tenant_id
        $stmt = $this->db->prepare("
            SELECT tenant_id, customer_id FROM campaign_recipients WHERE id = ?
        ");
        $stmt->execute([$recipientId]);
        $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recipient) {
            return false;
        }

        // Record click
        $stmt = $this->db->prepare("
            INSERT INTO campaign_link_clicks (
                campaign_id, recipient_id, tenant_id, original_url,
                ip_address, user_agent, device_type
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $campaignId,
            $recipientId,
            $recipient['tenant_id'],
            $url,
            $metadata['ip_address'] ?? null,
            $metadata['user_agent'] ?? null,
            $metadata['device_type'] ?? 'unknown'
        ]);

        // Update recipient
        $this->db->prepare("
            UPDATE campaign_recipients
            SET first_click_at = COALESCE(first_click_at, NOW()),
                total_clicks = total_clicks + 1,
                last_activity_at = NOW()
            WHERE id = ?
        ")->execute([$recipientId]);

        return true;
    }

    /**
     * Track conversion
     */
    public function trackConversion(int $recipientId, float $value, string $conversionType): bool
    {
        $stmt = $this->db->prepare("
            UPDATE campaign_recipients
            SET converted = TRUE,
                converted_at = NOW(),
                conversion_value = ?,
                conversion_type = ?
            WHERE id = ?
        ");

        return $stmt->execute([$value, $conversionType, $recipientId]);
    }

    /**
     * Pause campaign
     */
    public function pauseCampaign(int $campaignId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE marketing_campaigns
            SET status = 'paused'
            WHERE id = ?
        ");

        return $stmt->execute([$campaignId]);
    }

    /**
     * Resume campaign
     */
    public function resumeCampaign(int $campaignId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE marketing_campaigns
            SET status = 'active'
            WHERE id = ?
        ");

        return $stmt->execute([$campaignId]);
    }

    /**
     * Get campaign performance over time
     */
    public function getCampaignTrends(int $campaignId, int $days = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT
                stat_date,
                sent,
                delivered,
                opened,
                clicked,
                conversions,
                revenue,
                cost
            FROM campaign_daily_stats
            WHERE campaign_id = ?
              AND stat_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            ORDER BY stat_date ASC
        ");

        $stmt->execute([$campaignId, $days]);
        $trends = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'trends' => $trends
        ];
    }
}
