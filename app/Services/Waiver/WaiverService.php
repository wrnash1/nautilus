<?php

namespace App\Services\Waiver;

use App\Core\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PDO;

class WaiverService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get waiver template by ID
     */
    public function getTemplate(int $id): ?array
    {
        $sql = "SELECT * FROM waiver_templates WHERE id = ? AND is_active = 1";
        $result = $this->db->query($sql, [$id]);
        return $result->fetch() ?: null;
    }

    /**
     * Get waiver template by type
     */
    public function getTemplateByType(string $type): ?array
    {
        $sql = "SELECT * FROM waiver_templates WHERE type = ? AND is_active = 1 ORDER BY version DESC LIMIT 1";
        $result = $this->db->query($sql, [$type]);
        return $result->fetch() ?: null;
    }

    /**
     * Get all active templates
     */
    public function getAllTemplates(): array
    {
        $sql = "SELECT * FROM waiver_templates WHERE is_active = 1 ORDER BY type, version DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Check if customer has valid waiver for service type
     */
    public function hasValidWaiver(int $customerId, string $referenceType, int $gracePeriodDays = 30): bool
    {
        $sql = "SELECT sw.*, wt.type
                FROM signed_waivers sw
                JOIN waiver_templates wt ON sw.waiver_template_id = wt.id
                WHERE sw.customer_id = ?
                AND sw.reference_type = ?
                AND sw.status = 'signed'
                AND (sw.valid_until IS NULL OR sw.valid_until >= DATE_SUB(CURDATE(), INTERVAL ? DAY))
                ORDER BY sw.signed_at DESC
                LIMIT 1";

        $result = $this->db->query($sql, [$customerId, $referenceType, $gracePeriodDays]);
        return $result->fetch() !== false;
    }

    /**
     * Get customer's most recent waiver for a service type
     */
    public function getCustomerWaiver(int $customerId, string $referenceType): ?array
    {
        $sql = "SELECT sw.*, wt.name as template_name, wt.title as template_title
                FROM signed_waivers sw
                JOIN waiver_templates wt ON sw.waiver_template_id = wt.id
                WHERE sw.customer_id = ?
                AND sw.reference_type = ?
                ORDER BY sw.signed_at DESC
                LIMIT 1";

        $result = $this->db->query($sql, [$customerId, $referenceType]);
        return $result->fetch() ?: null;
    }

    /**
     * Create a waiver signature request
     */
    public function createWaiverRequest(array $data): int
    {
        $token = bin2hex(random_bytes(32));
        $waiverUrl = $_ENV['APP_URL'] . '/waivers/sign/' . $token;

        $sql = "INSERT INTO waiver_email_queue
                (customer_id, waiver_template_id, reference_type, reference_id,
                 email_to, subject, message, unique_token, waiver_url, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))";

        $this->db->query($sql, [
            $data['customer_id'],
            $data['waiver_template_id'],
            $data['reference_type'],
            $data['reference_id'] ?? null,
            $data['email'],
            $data['subject'],
            $data['message'] ?? '',
            $token,
            $waiverUrl
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Send waiver email
     */
    public function sendWaiverEmail(int $queueId): bool
    {
        // Get queue item
        $sql = "SELECT weq.*, c.first_name, c.last_name, wt.title as waiver_title
                FROM waiver_email_queue weq
                JOIN customers c ON weq.customer_id = c.id
                JOIN waiver_templates wt ON weq.waiver_template_id = wt.id
                WHERE weq.id = ?";

        $queue = $this->db->query($sql, [$queueId])->fetch();

        if (!$queue) {
            return false;
        }

        try {
            $mail = new PHPMailer(true);

            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];

            // Recipients
            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($queue['email_to'], $queue['first_name'] . ' ' . $queue['last_name']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $queue['subject'];
            $mail->Body = $this->generateWaiverEmailBody($queue);

            $mail->send();

            // Update queue status
            $updateSql = "UPDATE waiver_email_queue
                         SET status = 'sent', sent_at = NOW(), attempts = attempts + 1
                         WHERE id = ?";
            $this->db->query($updateSql, [$queueId]);

            return true;

        } catch (\Exception $e) {
            // Log error
            $updateSql = "UPDATE waiver_email_queue
                         SET status = 'failed', error_message = ?, attempts = attempts + 1
                         WHERE id = ?";
            $this->db->query($updateSql, [$e->getMessage(), $queueId]);

            return false;
        }
    }

    /**
     * Save signed waiver
     */
    public function saveSignedWaiver(array $data): int
    {
        $sql = "INSERT INTO signed_waivers
                (waiver_template_id, customer_id, reference_type, reference_id,
                 signature_data, signature_ip, signature_user_agent, signed_at,
                 emergency_contact_name, emergency_contact_phone, emergency_contact_relationship,
                 has_medical_conditions, medical_conditions, medications, allergies,
                 customer_name, customer_email, customer_phone, customer_dob,
                 status, valid_until)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'signed', ?)";

        $this->db->query($sql, [
            $data['waiver_template_id'],
            $data['customer_id'],
            $data['reference_type'],
            $data['reference_id'] ?? null,
            $data['signature_data'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $data['emergency_contact_name'] ?? null,
            $data['emergency_contact_phone'] ?? null,
            $data['emergency_contact_relationship'] ?? null,
            $data['has_medical_conditions'] ?? false,
            $data['medical_conditions'] ?? null,
            $data['medications'] ?? null,
            $data['allergies'] ?? null,
            $data['customer_name'],
            $data['customer_email'] ?? null,
            $data['customer_phone'] ?? null,
            $data['customer_dob'] ?? null,
            $data['valid_until'] ?? null
        ]);

        $waiverId = (int)$this->db->lastInsertId();

        // Update queue if applicable
        if (isset($data['queue_token'])) {
            $this->db->query(
                "UPDATE waiver_email_queue SET status = 'signed', signed_at = NOW() WHERE unique_token = ?",
                [$data['queue_token']]
            );
        }

        // Generate PDF
        $this->generateWaiverPDF($waiverId);

        // Send confirmation email
        if ($data['customer_email']) {
            $this->sendWaiverConfirmation($waiverId);
        }

        return $waiverId;
    }

    /**
     * Automatically create and send waiver for a service
     */
    public function autoSendWaiver(int $customerId, string $serviceType, int $referenceId): bool
    {
        // Check if customer already has valid waiver
        if ($this->hasValidWaiver($customerId, $serviceType)) {
            return true; // Already has valid waiver
        }

        // Get waiver requirement
        $sql = "SELECT wr.*, wt.id as template_id, wt.title
                FROM waiver_requirements wr
                JOIN waiver_templates wt ON wr.waiver_template_id = wt.id
                WHERE wr.service_type = ? AND wr.auto_send = 1 AND wt.is_active = 1";

        $requirement = $this->db->query($sql, [$serviceType])->fetch();

        if (!$requirement) {
            return false; // No auto-send requirement
        }

        // Get customer info
        $customer = $this->db->query("SELECT * FROM customers WHERE id = ?", [$customerId])->fetch();

        if (!$customer || !$customer['email']) {
            return false;
        }

        // Create waiver request
        $queueId = $this->createWaiverRequest([
            'customer_id' => $customerId,
            'waiver_template_id' => $requirement['template_id'],
            'reference_type' => $serviceType,
            'reference_id' => $referenceId,
            'email' => $customer['email'],
            'subject' => 'Please Sign Your ' . ucfirst($serviceType) . ' Waiver - ' . $requirement['title'],
            'message' => 'Please review and sign the attached waiver before your scheduled service.'
        ]);

        // Send email
        return $this->sendWaiverEmail($queueId);
    }

    /**
     * Get waiver by token
     */
    public function getWaiverByToken(string $token): ?array
    {
        $sql = "SELECT weq.*, wt.*, c.first_name, c.last_name, c.email as customer_email
                FROM waiver_email_queue weq
                JOIN waiver_templates wt ON weq.waiver_template_id = wt.id
                JOIN customers c ON weq.customer_id = c.id
                WHERE weq.unique_token = ?
                AND weq.status IN ('pending', 'sent')
                AND (weq.expires_at IS NULL OR weq.expires_at > NOW())";

        $result = $this->db->query($sql, [$token]);
        return $result->fetch() ?: null;
    }

    /**
     * Generate waiver email body
     */
    private function generateWaiverEmailBody(array $queue): string
    {
        return "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Waiver Signature Required</h2>
            <p>Dear {$queue['first_name']} {$queue['last_name']},</p>

            <p>A waiver signature is required for your upcoming service:</p>
            <p><strong>{$queue['waiver_title']}</strong></p>

            {$queue['message']}

            <p>
                <a href='{$queue['waiver_url']}'
                   style='background-color: #0066CC; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;'>
                    Review and Sign Waiver
                </a>
            </p>

            <p>This link will expire in 7 days.</p>

            <p>If you have any questions, please contact us.</p>

            <p>
                Best regards,<br>
                Nautilus Dive Shop Team
            </p>
        </body>
        </html>
        ";
    }

    /**
     * Generate PDF of signed waiver
     */
    private function generateWaiverPDF(int $waiverId): void
    {
        // Get waiver data
        $sql = "SELECT sw.*, wt.title, wt.content, wt.legal_text, c.first_name, c.last_name
                FROM signed_waivers sw
                JOIN waiver_templates wt ON sw.waiver_template_id = wt.id
                JOIN customers c ON sw.customer_id = c.id
                WHERE sw.id = ?";

        $waiver = $this->db->query($sql, [$waiverId])->fetch();

        if (!$waiver) {
            return;
        }

        // Create PDF using TCPDF
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('Nautilus Dive Shop');
        $pdf->SetTitle($waiver['title']);
        $pdf->setPrintHeader(false);
        $pdf->AddPage();

        // Add content
        $html = "<h1>{$waiver['title']}</h1>";
        $html .= "<p><strong>Signed by:</strong> {$waiver['customer_name']}</p>";
        $html .= "<p><strong>Date:</strong> " . date('F j, Y g:i A', strtotime($waiver['signed_at'])) . "</p>";
        $html .= "<hr>";
        $html .= "<h3>Agreement</h3>";
        $html .= nl2br($waiver['content']);
        $html .= "<h3>Legal Terms</h3>";
        $html .= nl2br($waiver['legal_text']);

        if ($waiver['emergency_contact_name']) {
            $html .= "<h3>Emergency Contact</h3>";
            $html .= "<p><strong>Name:</strong> {$waiver['emergency_contact_name']}</p>";
            $html .= "<p><strong>Phone:</strong> {$waiver['emergency_contact_phone']}</p>";
            $html .= "<p><strong>Relationship:</strong> {$waiver['emergency_contact_relationship']}</p>";
        }

        $html .= "<h3>Signature</h3>";
        $html .= "<img src='{$waiver['signature_data']}' alt='Signature' style='max-width: 300px;'>";

        $pdf->writeHTML($html);

        // Save PDF
        $pdfPath = BASE_PATH . '/storage/waivers/' . $waiverId . '.pdf';
        $pdf->Output($pdfPath, 'F');

        // Update database
        $this->db->query("UPDATE signed_waivers SET pdf_path = ? WHERE id = ?", [$pdfPath, $waiverId]);
    }

    /**
     * Send confirmation email after signing
     */
    private function sendWaiverConfirmation(int $waiverId): void
    {
        $sql = "SELECT sw.*, c.first_name, c.last_name, c.email, wt.title
                FROM signed_waivers sw
                JOIN customers c ON sw.customer_id = c.id
                JOIN waiver_templates wt ON sw.waiver_template_id = wt.id
                WHERE sw.id = ?";

        $waiver = $this->db->query($sql, [$waiverId])->fetch();

        if (!$waiver || !$waiver['email']) {
            return;
        }

        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];

            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($waiver['email'], $waiver['first_name'] . ' ' . $waiver['last_name']);

            // Attach PDF
            if ($waiver['pdf_path'] && file_exists($waiver['pdf_path'])) {
                $mail->addAttachment($waiver['pdf_path']);
            }

            $mail->isHTML(true);
            $mail->Subject = 'Waiver Confirmation - ' . $waiver['title'];
            $mail->Body = "
                <h2>Waiver Signed Successfully</h2>
                <p>Dear {$waiver['first_name']},</p>
                <p>Thank you for signing the waiver: <strong>{$waiver['title']}</strong></p>
                <p>Your signed waiver is attached to this email for your records.</p>
                <p>Best regards,<br>Nautilus Dive Shop</p>
            ";

            $mail->send();

            // Update email sent flag
            $this->db->query(
                "UPDATE signed_waivers SET email_sent = 1, email_sent_at = NOW() WHERE id = ?",
                [$waiverId]
            );

        } catch (\Exception $e) {
            // Log error but don't fail
            error_log("Failed to send waiver confirmation: " . $e->getMessage());
        }
    }

    /**
     * Get all signed waivers for a customer
     */
    public function getCustomerWaivers(int $customerId): array
    {
        $sql = "SELECT sw.*, wt.title, wt.type
                FROM signed_waivers sw
                JOIN waiver_templates wt ON sw.waiver_template_id = wt.id
                WHERE sw.customer_id = ?
                ORDER BY sw.signed_at DESC";

        return $this->db->query($sql, [$customerId])->fetchAll();
    }

    /**
     * Void a waiver
     */
    public function voidWaiver(int $waiverId, int $voidedBy, string $reason): bool
    {
        $sql = "UPDATE signed_waivers
                SET status = 'voided', voided_at = NOW(), voided_by = ?, void_reason = ?
                WHERE id = ?";

        $this->db->query($sql, [$voidedBy, $reason, $waiverId]);
        return true;
    }
}
