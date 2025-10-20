<?php

namespace App\Services\Communication;

use App\Core\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Email Service
 * Handles all email sending via PHPMailer
 */
class EmailService
{
    private Logger $logger;
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    /**
     * Configure PHPMailer with environment settings
     */
    private function configure(): void
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['MAIL_HOST'] ?? 'smtp.example.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['MAIL_USERNAME'] ?? '';
            $this->mailer->Password = $_ENV['MAIL_PASSWORD'] ?? '';
            $this->mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = (int)($_ENV['MAIL_PORT'] ?? 587);

            // Default sender
            $this->mailer->setFrom(
                $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@nautilus.com',
                $_ENV['MAIL_FROM_NAME'] ?? 'Nautilus Dive Shop'
            );

            // Encoding
            $this->mailer->CharSet = 'UTF-8';

        } catch (Exception $e) {
            $this->logger->error('Email configuration failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send a simple email
     */
    public function send(string $to, string $subject, string $body, bool $isHTML = true): array
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();

            $this->mailer->addAddress($to);
            $this->mailer->isHTML($isHTML);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            if ($isHTML) {
                // Create plain text version
                $this->mailer->AltBody = strip_tags($body);
            }

            $this->mailer->send();

            $this->logger->info('Email sent successfully', [
                'to' => $to,
                'subject' => $subject
            ]);

            return [
                'success' => true,
                'message' => 'Email sent successfully'
            ];

        } catch (Exception $e) {
            $this->logger->error('Email sending failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send email with template
     */
    public function sendTemplate(string $to, string $subject, string $template, array $data = []): array
    {
        $body = $this->renderTemplate($template, $data);
        return $this->send($to, $subject, $body, true);
    }

    /**
     * Send email with attachment
     */
    public function sendWithAttachment(string $to, string $subject, string $body, string $attachmentPath, string $attachmentName = ''): array
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            // Add attachment
            $this->mailer->addAttachment($attachmentPath, $attachmentName);

            $this->mailer->send();

            return [
                'success' => true,
                'message' => 'Email with attachment sent successfully'
            ];

        } catch (Exception $e) {
            $this->logger->error('Email with attachment failed', [
                'to' => $to,
                'attachment' => $attachmentPath,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send email to multiple recipients
     */
    public function sendBulk(array $recipients, string $subject, string $body): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($recipients as $recipient) {
            $result = $this->send($recipient, $subject, $body);

            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
                $results['errors'][$recipient] = $result['error'];
            }
        }

        return $results;
    }

    /**
     * Render email template
     */
    private function renderTemplate(string $template, array $data = []): string
    {
        $templatePath = BASE_PATH . '/app/Views/emails/' . $template . '.php';

        if (!file_exists($templatePath)) {
            throw new Exception("Email template not found: {$template}");
        }

        extract($data);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Test email configuration
     */
    public function testConnection(): array
    {
        try {
            $this->mailer->SMTPDebug = 0;
            $this->mailer->Timeout = 10;

            // Try to connect
            if (!$this->mailer->smtpConnect()) {
                throw new Exception('SMTP connection failed');
            }

            $this->mailer->smtpClose();

            return [
                'success' => true,
                'message' => 'SMTP connection successful'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
