<?php

namespace App\Services\Marketing;

use App\Core\Database;

class CampaignService
{
    private $db;
    private $emailQueueService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        // Since we don't have a container, we'll instantiate directly for now, 
        // passing the PDO connection from Database class
        $this->emailQueueService = new \App\Services\Email\EmailQueueService($this->db->getConnection());
    }

    /**
     * Get all campaigns
     *
     * @return array
     */
    public function getAllCampaigns()
    {
        try {
            $stmt = $this->db->query("
                SELECT c.*,
                       COUNT(cr.id) as recipient_count,
                       SUM(CASE WHEN cr.status = 'sent' THEN 1 ELSE 0 END) as sent_count,
                       SUM(CASE WHEN cr.opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened_count,
                       SUM(CASE WHEN cr.clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked_count
                FROM email_campaigns c
                LEFT JOIN email_campaign_recipients cr ON c.id = cr.campaign_id
                GROUP BY c.id
                ORDER BY c.created_at DESC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Table might not exist yet
            return [];
        }
    }

    /**
     * Get campaign by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getCampaignById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM email_campaigns WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Create new campaign
     * 
     * @param array $data
     * @return int|false Campaign ID or false on failure
     */
    public function createCampaign($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO email_campaigns 
            (name, subject, template_id, content, segment, status, scheduled_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([
            $data['name'],
            $data['subject'],
            $data['template_id'],
            $data['content'],
            $data['segment'],
            $data['status'],
            $data['scheduled_at']
        ]);

        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Update campaign
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCampaign($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE email_campaigns 
            SET name = ?, subject = ?, template_id = ?, content = ?,
                segment = ?, scheduled_at = ?, updated_at = NOW()
            WHERE id = ? AND status = 'draft'
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['subject'],
            $data['template_id'],
            $data['content'],
            $data['segment'],
            $data['scheduled_at'],
            $id
        ]);
    }

    /**
     * Delete campaign
     * 
     * @param int $id
     * @return bool
     */
    public function deleteCampaign($id)
    {
        $stmt = $this->db->prepare("DELETE FROM email_campaigns WHERE id = ? AND status = 'draft'");
        return $stmt->execute([$id]);
    }

    /**
     * Send campaign
     * 
     * @param int $campaignId
     * @return bool
     */
    public function sendCampaign($campaignId)
    {
        $campaign = $this->getCampaignById($campaignId);
        if (!$campaign || $campaign['status'] !== 'draft') {
            return false;
        }

        $recipients = $this->getRecipientsBySegment($campaign['segment']);
        
        $this->db->beginTransaction();
        
        try {
            $sentCount = 0;

            foreach ($recipients as $recipient) {
                // 1. Create recipient record
                $stmt = $this->db->prepare("
                    INSERT INTO email_campaign_recipients 
                    (campaign_id, customer_id, email, status, created_at)
                    VALUES (?, ?, ?, 'pending', NOW())
                ");
                $stmt->execute([
                    $campaignId,
                    $recipient['id'] ?? null, // Can be null for newsletter subscribers
                    $recipient['email']
                ]);
                $recipientId = $this->db->lastInsertId();

                // 2. Queue the email via EmailQueueService
                $trackingId = bin2hex(random_bytes(16)); // Generate our own to link them
                
                // Get the template or use content directly? 
                // The campaign uses a template_id + overridden content usually, 
                // or just raw content. The current createCampaign implementation 
                // stores 'content' and 'template_id'.
                // Let's assume we construct the body from the campaign content.
                // We'll replace {{customer_name}} if we have a name.
                
                $bodyHtml = $campaign['content'];
                $name = $recipient['name'] ?? 'Diver';
                $bodyHtml = str_replace('{{customer_name}}', $name, $bodyHtml);
                
                // We need to pass tracking_id to queue so it matches our log if we want
                // But CampaignService handles its own tracking in `email_campaign_recipients`.
                // Actually, `EmailQueueService` generates its own tracking ID. 
                // We should update our recipient record with the queue's tracking ID or 
                // let the queue service handle the sending and we just monitor status?
                // The current design of CampaignService tracks opens/clicks via `email_campaign_recipients`.
                // EmailQueueService logs to `email_log`.
                // To keep it simple for now, we will queue it and let EmailQueueService handle delivery.
                // We will rely on EmailQueueService's "related_entity" feature to link back.
                
                $queueId = $this->emailQueueService->queueEmail([
                    'tenant_id' => $_SESSION['tenant_id'] ?? 1,
                    'to_email' => $recipient['email'],
                    'to_name' => $name,
                    'subject' => $campaign['subject'],
                    'body_html' => $bodyHtml,
                    'body_text' => strip_tags($bodyHtml),
                    'campaign_id' => $campaignId, // Internal tracking in queue
                    'related_entity_type' => 'email_campaign_recipient',
                    'related_entity_id' => $recipientId
                ]);

                // Update status to sent immediately? Or 'queued'? 
                // The original code set it to 'pending'. 
                // We'll treat 'pending' as 'queued' in this context.
                
                $sentCount++;
            }

            // Update campaign status
            $stmt = $this->db->prepare("
                UPDATE email_campaigns 
                SET status = 'sent', sent_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$campaignId]);

            $this->db->commit();

            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            // Log error?
            throw $e; // Re-throw for debugging
            return false;
        }
    }

    /**
     * Get recipients based on segment
     * 
     * @param string $segment
     * @return array
     */
    private function getRecipientsBySegment($segment)
    {
        switch ($segment) {
            case 'all':
                $stmt = $this->db->query("
                    SELECT id, email FROM customers 
                    WHERE email IS NOT NULL AND is_active = 1
                ");
                break;
                
            case 'active':
                $stmt = $this->db->query("
                    SELECT DISTINCT c.id, c.email 
                    FROM customers c
                    JOIN transactions t ON c.id = t.customer_id
                    WHERE c.email IS NOT NULL 
                    AND c.is_active = 1
                    AND t.transaction_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                ");
                break;
                
            case 'inactive':
                $stmt = $this->db->query("
                    SELECT DISTINCT c.id, c.email 
                    FROM customers c
                    LEFT JOIN transactions t ON c.id = t.customer_id 
                        AND t.transaction_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                    WHERE c.email IS NOT NULL 
                    AND c.is_active = 1
                    AND t.id IS NULL
                ");
                break;
                
            case 'newsletter':
                $stmt = $this->db->query("
                    SELECT id, email, name FROM newsletter_subscriptions 
                    WHERE is_active = 1
                ");
                // Map newsletter id to 'id' but remember it's not a customer_id
                // The loop above needs to handle this. 
                // Actually, the loop expects 'id' to be customer_id.
                // We should normalize the array returned here to be compatible.
                // For newsletter subscribers, 'id' is subscription ID, not customer ID.
                // The recipient insert above attempts to insert 'customer_id'.
                // We need to adjust `getRecipientsBySegment` to strictly return contact info
                // and maybe a type?
                
                // Let's standardise the return: [['email' => ..., 'name' => ..., 'id' => ..., 'type' => 'customer'|'subscriber']]
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $final = [];
                foreach ($results as $row) {
                    $final[] = [
                        'email' => $row['email'],
                        'name' => $row['name'],
                        'id' => null, // No customer ID
                        'subscriber_id' => $row['id']
                    ];
                }
                return $final;

            default:
                $stmt = $this->db->query("SELECT id, email FROM customers WHERE 1=0");
        }
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get campaign statistics
     * 
     * @param int $campaignId
     * @return array
     */
    public function getCampaignStats($campaignId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_recipients,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_count,
                SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened_count,
                SUM(CASE WHEN clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked_count,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count
            FROM email_campaign_recipients
            WHERE campaign_id = ?
        ");
        $stmt->execute([$campaignId]);
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($stats['sent_count'] > 0) {
            $stats['open_rate'] = round(($stats['opened_count'] / $stats['sent_count']) * 100, 2);
            $stats['click_rate'] = round(($stats['clicked_count'] / $stats['sent_count']) * 100, 2);
        } else {
            $stats['open_rate'] = 0;
            $stats['click_rate'] = 0;
        }

        return $stats;
    }

    /**
     * Get all email templates
     * 
     * @return array
     */
    public function getAllTemplates()
    {
        try {
            $stmt = $this->db->query("
                SELECT * FROM email_templates
                WHERE is_active = 1
                ORDER BY name ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Table might not exist yet
            return [];
        }
    }

    /**
     * Track email open
     * 
     * @param int $recipientId
     * @return bool
     */
    public function trackOpen($recipientId)
    {
        $stmt = $this->db->prepare("
            UPDATE email_campaign_recipients 
            SET opened_at = NOW(), open_count = open_count + 1
            WHERE id = ? AND opened_at IS NULL
        ");
        return $stmt->execute([$recipientId]);
    }

    /**
     * Track email click
     * 
     * @param int $recipientId
     * @param string $url
     * @return bool
     */
    public function trackClick($recipientId, $url)
    {
        $stmt = $this->db->prepare("
            UPDATE email_campaign_recipients 
            SET clicked_at = NOW(), click_count = click_count + 1, clicked_url = ?
            WHERE id = ?
        ");
        return $stmt->execute([$url, $recipientId]);
    }

    /**
     * Create email template
     * 
     * @param array $data
     * @return int|false Template ID or false on failure
     */
    public function createTemplate($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO email_templates 
            (name, subject, content, is_active, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([
            $data['name'],
            $data['subject'],
            $data['content'],
            $data['is_active'] ?? 1
        ]);

        return $success ? $this->db->lastInsertId() : false;
    }
}
