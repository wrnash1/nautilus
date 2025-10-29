<?php

namespace App\Services\Email;

use App\Core\Logger;

/**
 * Email Service
 * Handles sending emails using PHPMailer or native PHP mail
 */
class EmailService
{
    private Logger $logger;
    private array $config;
    private bool $usePHPMailer;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->loadConfig();
        $this->usePHPMailer = class_exists('\PHPMailer\PHPMailer\PHPMailer');
    }

    /**
     * Load email configuration from environment or database
     */
    private function loadConfig(): void
    {
        $this->config = [
            'from_email' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@nautilus.local',
            'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Nautilus Dive Shop',
            'smtp_host' => $_ENV['MAIL_HOST'] ?? 'localhost',
            'smtp_port' => $_ENV['MAIL_PORT'] ?? 587,
            'smtp_username' => $_ENV['MAIL_USERNAME'] ?? '',
            'smtp_password' => $_ENV['MAIL_PASSWORD'] ?? '',
            'smtp_encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
            'use_smtp' => filter_var($_ENV['MAIL_USE_SMTP'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * Send an email
     */
    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        try {
            if ($this->usePHPMailer && $this->config['use_smtp']) {
                return $this->sendWithPHPMailer($to, $subject, $body, $options);
            } else {
                return $this->sendWithNativeMail($to, $subject, $body, $options);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send email using PHPMailer
     */
    private function sendWithPHPMailer(string $to, string $subject, string $body, array $options): bool
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = !empty($this->config['smtp_username']);
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->SMTPSecure = $this->config['smtp_encryption'];
            $mail->Port = $this->config['smtp_port'];

            // Recipients
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($to, $options['to_name'] ?? '');

            // CC and BCC
            if (!empty($options['cc'])) {
                if (is_array($options['cc'])) {
                    foreach ($options['cc'] as $ccEmail) {
                        $mail->addCC($ccEmail);
                    }
                } else {
                    $mail->addCC($options['cc']);
                }
            }

            if (!empty($options['bcc'])) {
                if (is_array($options['bcc'])) {
                    foreach ($options['bcc'] as $bccEmail) {
                        $mail->addBCC($bccEmail);
                    }
                } else {
                    $mail->addBCC($options['bcc']);
                }
            }

            // Reply-To
            if (!empty($options['reply_to'])) {
                $mail->addReplyTo($options['reply_to'], $options['reply_to_name'] ?? '');
            }

            // Attachments
            if (!empty($options['attachments'])) {
                foreach ($options['attachments'] as $attachment) {
                    if (is_array($attachment)) {
                        $mail->addAttachment($attachment['path'], $attachment['name'] ?? '');
                    } else {
                        $mail->addAttachment($attachment);
                    }
                }
            }

            // Content
            $mail->isHTML(!empty($options['is_html']) ? $options['is_html'] : true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            // Plain text alternative
            if (!empty($options['text_body'])) {
                $mail->AltBody = $options['text_body'];
            }

            $mail->send();

            $this->logger->info('Email sent via PHPMailer', [
                'to' => $to,
                'subject' => $subject
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('PHPMailer error', [
                'error' => $mail->ErrorInfo
            ]);
            throw $e;
        }
    }

    /**
     * Send email using native PHP mail()
     */
    private function sendWithNativeMail(string $to, string $subject, string $body, array $options): bool
    {
        $headers = [];
        $headers[] = 'From: ' . $this->config['from_name'] . ' <' . $this->config['from_email'] . '>';
        $headers[] = 'Reply-To: ' . ($options['reply_to'] ?? $this->config['from_email']);
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        if (!empty($options['is_html']) && $options['is_html']) {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        }

        $headerString = implode("\r\n", $headers);

        $success = mail($to, $subject, $body, $headerString);

        if ($success) {
            $this->logger->info('Email sent via native mail', [
                'to' => $to,
                'subject' => $subject
            ]);
        }

        return $success;
    }

    /**
     * Send template-based email
     */
    public function sendTemplate(string $to, string $template, array $data = [], array $options = []): bool
    {
        $templatePath = __DIR__ . '/../../Views/emails/' . $template . '.php';

        if (!file_exists($templatePath)) {
            $this->logger->error('Email template not found', ['template' => $template]);
            return false;
        }

        // Extract data for template
        extract($data);

        // Capture template output
        ob_start();
        include $templatePath;
        $body = ob_get_clean();

        $subject = $data['subject'] ?? 'Message from Nautilus Dive Shop';

        return $this->send($to, $subject, $body, array_merge($options, ['is_html' => true]));
    }

    /**
     * Send appointment reminder email
     */
    public function sendAppointmentReminder(array $appointment): bool
    {
        $data = [
            'subject' => 'Appointment Reminder - ' . ucfirst($appointment['appointment_type']),
            'customer_name' => $appointment['customer_name'],
            'appointment_type' => ucfirst($appointment['appointment_type']),
            'start_time' => $appointment['start_time'],
            'end_time' => $appointment['end_time'],
            'location' => $appointment['location'] ?? 'Main Location',
            'assigned_to_name' => $appointment['assigned_to_name'] ?? 'Our Team',
            'notes' => $appointment['notes'] ?? ''
        ];

        return $this->sendTemplate($appointment['customer_email'], 'appointment_reminder', $data);
    }

    /**
     * Send appointment confirmation email
     */
    public function sendAppointmentConfirmation(array $appointment): bool
    {
        $data = [
            'subject' => 'Appointment Confirmed - ' . ucfirst($appointment['appointment_type']),
            'customer_name' => $appointment['customer_name'],
            'appointment_type' => ucfirst($appointment['appointment_type']),
            'start_time' => $appointment['start_time'],
            'end_time' => $appointment['end_time'],
            'location' => $appointment['location'] ?? 'Main Location',
            'assigned_to_name' => $appointment['assigned_to_name'] ?? 'Our Team'
        ];

        return $this->sendTemplate($appointment['customer_email'], 'appointment_confirmation', $data);
    }

    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmation(array $order, array $customer): bool
    {
        $data = [
            'subject' => 'Order Confirmation #' . $order['id'],
            'customer_name' => $customer['first_name'] . ' ' . $customer['last_name'],
            'order_id' => $order['id'],
            'order_total' => $order['total'],
            'order_items' => $order['items'] ?? [],
            'order_date' => $order['created_at']
        ];

        return $this->sendTemplate($customer['email'], 'order_confirmation', $data);
    }

    /**
     * Send welcome email to new customer
     */
    public function sendWelcomeEmail(array $customer, string $password = null): bool
    {
        $data = [
            'subject' => 'Welcome to Nautilus Dive Shop',
            'customer_name' => $customer['first_name'],
            'email' => $customer['email'],
            'password' => $password,
            'login_url' => $_ENV['APP_URL'] ?? 'https://nautilus.local'
        ];

        return $this->sendTemplate($customer['email'], 'welcome', $data);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset(string $email, string $token): bool
    {
        $resetUrl = ($_ENV['APP_URL'] ?? 'https://nautilus.local') . '/reset-password?token=' . $token;

        $data = [
            'subject' => 'Password Reset Request',
            'reset_url' => $resetUrl,
            'token' => $token
        ];

        return $this->sendTemplate($email, 'password_reset', $data);
    }

    /**
     * Send certification completion email
     */
    public function sendCertificationEmail(array $enrollment, array $customer): bool
    {
        $data = [
            'subject' => 'Congratulations on Your Certification!',
            'customer_name' => $customer['first_name'] . ' ' . $customer['last_name'],
            'course_name' => $enrollment['course_name'],
            'certification_number' => $enrollment['certification_number'],
            'completion_date' => $enrollment['completion_date']
        ];

        return $this->sendTemplate($customer['email'], 'certification', $data);
    }

    /**
     * Send trip booking confirmation
     */
    public function sendTripBookingConfirmation(array $booking, array $customer): bool
    {
        $data = [
            'subject' => 'Trip Booking Confirmed - ' . $booking['trip_name'],
            'customer_name' => $customer['first_name'] . ' ' . $customer['last_name'],
            'trip_name' => $booking['trip_name'],
            'departure_date' => $booking['departure_date'],
            'return_date' => $booking['return_date'] ?? null,
            'booking_id' => $booking['id']
        ];

        return $this->sendTemplate($customer['email'], 'trip_booking', $data);
    }

    /**
     * Send low stock alert to staff
     */
    public function sendLowStockAlert(array $product, array $staffEmails): bool
    {
        $data = [
            'subject' => 'Low Stock Alert - ' . $product['name'],
            'product_name' => $product['name'],
            'sku' => $product['sku'],
            'current_stock' => $product['stock_quantity'],
            'threshold' => $product['low_stock_threshold']
        ];

        $success = true;
        foreach ($staffEmails as $email) {
            $success = $this->sendTemplate($email, 'low_stock_alert', $data) && $success;
        }

        return $success;
    }

    /**
     * Test email configuration
     */
    public function testConnection(): array
    {
        try {
            $testEmail = $this->config['from_email'];
            $subject = 'Email Test - Nautilus';
            $body = 'This is a test email from Nautilus Dive Shop Management System.';

            $success = $this->send($testEmail, $subject, $body, ['is_html' => false]);

            return [
                'success' => $success,
                'message' => $success ? 'Email sent successfully' : 'Failed to send email',
                'using_phpmailer' => $this->usePHPMailer,
                'using_smtp' => $this->config['use_smtp']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'using_phpmailer' => $this->usePHPMailer,
                'using_smtp' => $this->config['use_smtp']
            ];
        }
    }

    /**
     * Queue email for later sending (basic implementation)
     */
    public function queue(string $to, string $subject, string $body, array $options = []): int
    {
        // TODO: Implement proper email queue with database table
        // For now, just send immediately
        $this->send($to, $subject, $body, $options);
        return 0;
    }
}
