<?php

namespace App\Services\Email;

use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Email Queue Service
 * Handles queuing, processing, and sending emails
 */
class EmailQueueService
{
    private PDO $db;
    private $smtpConfig;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->loadSMTPConfig();
    }

    /**
     * Queue an email for sending
     */
    public function queueEmail(array $params): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO email_queue (
                tenant_id, to_email, to_name, cc, bcc,
                subject, body_html, body_text,
                from_email, from_name, reply_to,
                attachments, template_name, template_variables,
                priority, scheduled_at, send_after,
                related_entity_type, related_entity_id,
                campaign_id, tracking_id
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?,
                ?, ?,
                ?, ?
            )
        ");

        $stmt->execute([
            $params['tenant_id'] ?? null,
            $params['to_email'],
            $params['to_name'] ?? null,
            isset($params['cc']) ? json_encode($params['cc']) : null,
            isset($params['bcc']) ? json_encode($params['bcc']) : null,
            $params['subject'],
            $params['body_html'] ?? null,
            $params['body_text'] ?? null,
            $params['from_email'] ?? $this->smtpConfig['from_email'],
            $params['from_name'] ?? $this->smtpConfig['from_name'],
            $params['reply_to'] ?? null,
            isset($params['attachments']) ? json_encode($params['attachments']) : null,
            $params['template_name'] ?? null,
            isset($params['template_variables']) ? json_encode($params['template_variables']) : null,
            $params['priority'] ?? 'normal',
            $params['scheduled_at'] ?? null,
            $params['send_after'] ?? null,
            $params['related_entity_type'] ?? null,
            $params['related_entity_id'] ?? null,
            $params['campaign_id'] ?? null,
            $params['tracking_id'] ?? $this->generateTrackingId()
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Queue email from template
     */
    public function queueFromTemplate(string $templateName, string $toEmail, array $variables, array $options = []): int
    {
        $template = $this->getTemplate($templateName);

        if (!$template) {
            throw new \Exception("Email template not found: $templateName");
        }

        // Replace template variables
        $subject = $this->replaceVariables($template['subject'], $variables);
        $bodyHtml = $this->replaceVariables($template['body_html'], $variables);
        $bodyText = $this->replaceVariables($template['body_text'], $variables);

        return $this->queueEmail([
            'to_email' => $toEmail,
            'to_name' => $variables['customer_name'] ?? null,
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
            'from_email' => $template['from_email'] ?? null,
            'from_name' => $template['from_name'] ?? null,
            'template_name' => $templateName,
            'template_variables' => $variables,
            'priority' => $options['priority'] ?? 'normal',
            'scheduled_at' => $options['scheduled_at'] ?? null,
            'related_entity_type' => $options['related_entity_type'] ?? null,
            'related_entity_id' => $options['related_entity_id'] ?? null,
            'campaign_id' => $options['campaign_id'] ?? null,
            'tenant_id' => $options['tenant_id'] ?? null
        ]);
    }

    /**
     * Process pending emails from queue
     */
    public function processQueue(int $limit = 50): array
    {
        // Get pending emails (prioritized, ready to send)
        $stmt = $this->db->prepare("
            SELECT * FROM email_queue
            WHERE status = 'pending'
              AND attempts < max_attempts
              AND (scheduled_at IS NULL OR scheduled_at <= NOW())
              AND (send_after IS NULL OR send_after <= NOW())
            ORDER BY
                CASE priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'normal' THEN 3
                    WHEN 'low' THEN 4
                END,
                created_at ASC
            LIMIT ?
        ");

        $stmt->execute([$limit]);
        $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [
            'processed' => 0,
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($emails as $email) {
            $results['processed']++;

            try {
                $this->markAsProcessing($email['id']);
                $this->sendEmail($email);
                $this->markAsSent($email['id']);
                $results['sent']++;
            } catch (\Exception $e) {
                $this->markAsFailed($email['id'], $e->getMessage());
                $results['failed']++;
                $results['errors'][] = [
                    'email_id' => $email['id'],
                    'to' => $email['to_email'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Send a single email
     */
    private function sendEmail(array $emailData): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->smtpConfig['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtpConfig['username'];
            $mail->Password   = $this->smtpConfig['password'];
            $mail->SMTPSecure = $this->smtpConfig['encryption'] ?? PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->smtpConfig['port'];

            // Recipients
            $mail->setFrom(
                $emailData['from_email'] ?? $this->smtpConfig['from_email'],
                $emailData['from_name'] ?? $this->smtpConfig['from_name']
            );

            $mail->addAddress($emailData['to_email'], $emailData['to_name'] ?? '');

            // CC/BCC
            if ($emailData['cc']) {
                $ccList = json_decode($emailData['cc'], true);
                foreach ($ccList as $cc) {
                    $mail->addCC($cc['email'], $cc['name'] ?? '');
                }
            }

            if ($emailData['bcc']) {
                $bccList = json_decode($emailData['bcc'], true);
                foreach ($bccList as $bcc) {
                    $mail->addBCC($bcc['email'], $bcc['name'] ?? '');
                }
            }

            // Reply-To
            if ($emailData['reply_to']) {
                $mail->addReplyTo($emailData['reply_to']);
            }

            // Attachments
            if ($emailData['attachments']) {
                $attachments = json_decode($emailData['attachments'], true);
                foreach ($attachments as $attachment) {
                    if (file_exists($attachment['path'])) {
                        $mail->addAttachment($attachment['path'], $attachment['name'] ?? '');
                    }
                }
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $emailData['subject'];
            $mail->Body    = $this->addTrackingPixel($emailData['body_html'], $emailData['tracking_id']);
            $mail->AltBody = $emailData['body_text'] ?? strip_tags($emailData['body_html']);

            $mail->send();

            // Log sent email
            $this->logEmail($emailData);

            return true;

        } catch (Exception $e) {
            throw new \Exception("Email sending failed: {$mail->ErrorInfo}");
        }
    }

    /**
     * Mark email as processing
     */
    private function markAsProcessing(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE email_queue
            SET status = 'processing',
                attempts = attempts + 1,
                last_attempt_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$id]);
    }

    /**
     * Mark email as sent
     */
    private function markAsSent(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE email_queue
            SET status = 'sent',
                sent_at = NOW(),
                error_message = NULL
            WHERE id = ?
        ");
        $stmt->execute([$id]);
    }

    /**
     * Mark email as failed
     */
    private function markAsFailed(int $id, string $errorMessage): void
    {
        $stmt = $this->db->prepare("
            UPDATE email_queue
            SET status = CASE
                    WHEN attempts >= max_attempts THEN 'failed'
                    ELSE 'pending'
                END,
                error_message = ?
            WHERE id = ?
        ");
        $stmt->execute([$errorMessage, $id]);
    }

    /**
     * Log sent email
     */
    private function logEmail(array $emailData): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO email_log (
                tenant_id, to_email, subject, body_preview,
                template_name, sent_at, tracking_id,
                related_entity_type, related_entity_id,
                customer_id, campaign_id
            ) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)
        ");

        $bodyPreview = substr(strip_tags($emailData['body_html']), 0, 500);

        $stmt->execute([
            $emailData['tenant_id'],
            $emailData['to_email'],
            $emailData['subject'],
            $bodyPreview,
            $emailData['template_name'],
            $emailData['tracking_id'],
            $emailData['related_entity_type'],
            $emailData['related_entity_id'],
            null, // customer_id - would need to lookup
            $emailData['campaign_id']
        ]);
    }

    /**
     * Get email template
     */
    private function getTemplate(string $templateName): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM email_templates
            WHERE name = ? AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$templateName]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Replace template variables
     */
    private function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }

    /**
     * Add tracking pixel to HTML email
     */
    private function addTrackingPixel(string $html, string $trackingId): string
    {
        if (empty($html) || empty($trackingId)) {
            return $html;
        }

        $trackingPixel = '<img src="' . ($_ENV['APP_URL'] ?? '') . '/email/track/' . $trackingId . '" width="1" height="1" alt="" />';

        // Try to add before </body>, otherwise append
        if (stripos($html, '</body>') !== false) {
            return str_ireplace('</body>', $trackingPixel . '</body>', $html);
        }

        return $html . $trackingPixel;
    }

    /**
     * Generate unique tracking ID
     */
    private function generateTrackingId(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Load SMTP configuration
     */
    private function loadSMTPConfig(): void
    {
        $this->smtpConfig = [
            'host' => $_ENV['SMTP_HOST'] ?? 'localhost',
            'port' => $_ENV['SMTP_PORT'] ?? 587,
            'username' => $_ENV['SMTP_USERNAME'] ?? '',
            'password' => $_ENV['SMTP_PASSWORD'] ?? '',
            'encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'tls',
            'from_email' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@diveshop.com',
            'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Dive Shop'
        ];
    }

    /**
     * Get queue statistics
     */
    public function getQueueStats(): array
    {
        $stmt = $this->db->query("
            SELECT
                status,
                COUNT(*) as count,
                MIN(created_at) as oldest,
                MAX(created_at) as newest
            FROM email_queue
            GROUP BY status
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retry failed emails
     */
    public function retryFailed(int $limit = 10): int
    {
        $stmt = $this->db->prepare("
            UPDATE email_queue
            SET status = 'pending',
                attempts = 0,
                error_message = NULL
            WHERE status = 'failed'
              AND attempts < max_attempts
            LIMIT ?
        ");

        $stmt->execute([$limit]);
        return $stmt->rowCount();
    }

    /**
     * Cancel pending email
     */
    public function cancelEmail(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE email_queue
            SET status = 'cancelled'
            WHERE id = ? AND status = 'pending'
        ");

        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
