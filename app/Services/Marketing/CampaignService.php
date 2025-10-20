<?php

namespace App\Services\Marketing;

use App\Core\Database;

class CampaignService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all campaigns
     * 
     * @return array
     */
    public function getAllCampaigns()
    {
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
            foreach ($recipients as $recipient) {
                $stmt = $this->db->prepare("
                    INSERT INTO email_campaign_recipients 
                    (campaign_id, customer_id, email, status, created_at)
                    VALUES (?, ?, ?, 'pending', NOW())
                ");
                $stmt->execute([
                    $campaignId,
                    $recipient['id'],
                    $recipient['email']
                ]);
            }

            $stmt = $this->db->prepare("
                UPDATE email_campaigns 
                SET status = 'sending', sent_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$campaignId]);

            $this->db->commit();

            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
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
        $stmt = $this->db->query("
            SELECT * FROM email_templates 
            WHERE is_active = 1 
            ORDER BY name ASC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
