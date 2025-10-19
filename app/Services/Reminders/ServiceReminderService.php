<?php

namespace App\Services\Reminders;

use App\Core\Database;
use PDO;

class ServiceReminderService
{
    /**
     * Schedule service reminder for customer
     */
    public function scheduleReminder(
        int $customerId,
        string $reminderType,
        \DateTime $dueDate,
        int $templateId,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): int {
        // Get template to determine when to send
        $template = $this->getTemplate($templateId);

        if (!$template) {
            throw new \Exception('Reminder template not found');
        }

        // Calculate scheduled send date (X days before due date)
        $scheduledSendDate = clone $dueDate;
        $scheduledSendDate->modify("-{$template['days_before']} days");

        // Don't schedule if send date is in the past
        $today = new \DateTime();
        if ($scheduledSendDate < $today) {
            $scheduledSendDate = $today;
        }

        $sql = "
            INSERT INTO service_reminders (
                template_id, customer_id, reminder_type, reference_type, reference_id,
                due_date, scheduled_send_date, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ";

        Database::query($sql, [
            $templateId,
            $customerId,
            $reminderType,
            $referenceType,
            $referenceId,
            $dueDate->format('Y-m-d'),
            $scheduledSendDate->format('Y-m-d')
        ]);

        return Database::lastInsertId();
    }

    /**
     * Process pending reminders that are due to be sent
     */
    public function processPendingReminders(): array
    {
        $today = date('Y-m-d');

        $sql = "
            SELECT sr.*, srt.*, c.email, c.phone, c.first_name, c.last_name
            FROM service_reminders sr
            JOIN service_reminder_templates srt ON sr.template_id = srt.id
            JOIN customers c ON sr.customer_id = c.id
            WHERE sr.status = 'pending'
            AND sr.scheduled_send_date <= ?
            AND srt.is_active = 1
            ORDER BY sr.scheduled_send_date ASC
        ";

        $reminders = Database::fetchAll($sql, [$today]);
        $results = [];

        foreach ($reminders as $reminder) {
            $result = $this->sendReminder($reminder);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Send individual reminder
     */
    private function sendReminder(array $reminder): array
    {
        $sent = false;
        $errors = [];

        // Personalize message
        $emailBody = $this->personalizeMessage($reminder['email_body'], $reminder);
        $emailSubject = $this->personalizeMessage($reminder['email_subject'], $reminder);
        $smsMessage = $this->personalizeMessage($reminder['sms_message'], $reminder);

        // Send email if enabled
        if ($reminder['send_email'] && !empty($reminder['email'])) {
            try {
                $emailSent = $this->sendEmail($reminder['email'], $emailSubject, $emailBody);
                if ($emailSent) {
                    $this->updateReminderStatus($reminder['id'], 'email_sent', true);
                    $sent = true;
                }
            } catch (\Exception $e) {
                $errors[] = "Email error: " . $e->getMessage();
            }
        }

        // Send SMS if enabled
        if ($reminder['send_sms'] && !empty($reminder['phone'])) {
            try {
                $smsSent = $this->sendSMS($reminder['phone'], $smsMessage);
                if ($smsSent) {
                    $this->updateReminderStatus($reminder['id'], 'sms_sent', true);
                    $sent = true;
                }
            } catch (\Exception $e) {
                $errors[] = "SMS error: " . $e->getMessage();
            }
        }

        // Update reminder status
        if ($sent) {
            $this->markReminderSent($reminder['id']);
        } else {
            $this->markReminderFailed($reminder['id'], implode('; ', $errors));
        }

        return [
            'reminder_id' => $reminder['id'],
            'customer_id' => $reminder['customer_id'],
            'type' => $reminder['reminder_type'],
            'sent' => $sent,
            'errors' => $errors
        ];
    }

    /**
     * Personalize message with customer data
     */
    private function personalizeMessage(string $message, array $reminder): string
    {
        $replacements = [
            '{first_name}' => $reminder['first_name'],
            '{last_name}' => $reminder['last_name'],
            '{full_name}' => $reminder['first_name'] . ' ' . $reminder['last_name'],
            '{due_date}' => date('F j, Y', strtotime($reminder['due_date'])),
            '{reminder_type}' => str_replace('_', ' ', ucwords($reminder['reminder_type'])),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    /**
     * Send email
     */
    private function sendEmail(string $to, string $subject, string $body): bool
    {
        // Use PHPMailer or configured mail service
        // For now, placeholder implementation
        // TODO: Integrate with actual email service

        $headers = "From: " . ($_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@nautilus.com') . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        return mail($to, $subject, $body, $headers);
    }

    /**
     * Send SMS
     */
    private function sendSMS(string $phone, string $message): bool
    {
        // Use Twilio or configured SMS service
        // For now, placeholder implementation
        // TODO: Integrate with Twilio service

        return true; // Placeholder
    }

    /**
     * Update reminder email/SMS sent status
     */
    private function updateReminderStatus(int $reminderId, string $field, bool $value): void
    {
        $sql = "UPDATE service_reminders SET {$field} = ? WHERE id = ?";
        Database::query($sql, [$value ? 1 : 0, $reminderId]);
    }

    /**
     * Mark reminder as sent
     */
    private function markReminderSent(int $reminderId): void
    {
        $sql = "
            UPDATE service_reminders
            SET status = 'sent', sent_at = NOW()
            WHERE id = ?
        ";
        Database::query($sql, [$reminderId]);
    }

    /**
     * Mark reminder as failed
     */
    private function markReminderFailed(int $reminderId, string $error): void
    {
        $sql = "
            UPDATE service_reminders
            SET status = 'failed', error_message = ?
            WHERE id = ?
        ";
        Database::query($sql, [$error, $reminderId]);
    }

    /**
     * Get reminder template
     */
    private function getTemplate(int $templateId): ?array
    {
        $sql = "SELECT * FROM service_reminder_templates WHERE id = ?";
        return Database::fetchOne($sql, [$templateId]);
    }

    /**
     * Auto-schedule reminders based on equipment service records
     */
    public function autoScheduleEquipmentReminders(): array
    {
        $results = [];

        // Find equipment needing service in next 90 days
        $sql = "
            SELECT DISTINCT
                esh.customer_id,
                esh.equipment_type,
                esh.next_service_due,
                esh.equipment_serial
            FROM equipment_service_history esh
            WHERE esh.next_service_due IS NOT NULL
            AND esh.next_service_due BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
            AND NOT EXISTS (
                SELECT 1 FROM service_reminders sr
                WHERE sr.customer_id = esh.customer_id
                AND sr.reference_type = 'equipment_service'
                AND sr.due_date = esh.next_service_due
                AND sr.status IN ('pending', 'sent')
            )
        ";

        $dueServices = Database::fetchAll($sql);

        foreach ($dueServices as $service) {
            // Determine reminder type and template
            $reminderType = $this->getReminderTypeForEquipment($service['equipment_type']);
            $template = $this->getTemplateByType($reminderType);

            if ($template) {
                try {
                    $dueDate = new \DateTime($service['next_service_due']);
                    $reminderId = $this->scheduleReminder(
                        $service['customer_id'],
                        $reminderType,
                        $dueDate,
                        $template['id'],
                        'equipment_service',
                        null
                    );

                    $results[] = [
                        'customer_id' => $service['customer_id'],
                        'equipment' => $service['equipment_type'],
                        'reminder_id' => $reminderId,
                        'status' => 'scheduled'
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'customer_id' => $service['customer_id'],
                        'equipment' => $service['equipment_type'],
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Auto-schedule certification expiry reminders
     */
    public function autoScheduleCertificationReminders(): array
    {
        $results = [];

        // Find certifications expiring in next 90 days
        $sql = "
            SELECT cc.*, c.first_name, c.last_name
            FROM customer_certifications cc
            JOIN customers c ON cc.customer_id = c.id
            WHERE cc.expiry_date IS NOT NULL
            AND cc.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
            AND NOT EXISTS (
                SELECT 1 FROM service_reminders sr
                WHERE sr.customer_id = cc.customer_id
                AND sr.reference_type = 'certification'
                AND sr.reference_id = cc.id
                AND sr.status IN ('pending', 'sent')
            )
        ";

        $expiringCerts = Database::fetchAll($sql);

        foreach ($expiringCerts as $cert) {
            $template = $this->getTemplateByType('certification_renewal');

            if ($template) {
                try {
                    $expiryDate = new \DateTime($cert['expiry_date']);
                    $reminderId = $this->scheduleReminder(
                        $cert['customer_id'],
                        'certification_renewal',
                        $expiryDate,
                        $template['id'],
                        'certification',
                        $cert['id']
                    );

                    $results[] = [
                        'customer_id' => $cert['customer_id'],
                        'certification_id' => $cert['id'],
                        'reminder_id' => $reminderId,
                        'status' => 'scheduled'
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'customer_id' => $cert['customer_id'],
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Schedule birthday reminders
     */
    public function scheduleBirthdayReminders(): array
    {
        $results = [];

        // Find customers with birthdays in next 7 days
        $sql = "
            SELECT id, first_name, last_name, email, birth_date
            FROM customers
            WHERE birth_date IS NOT NULL
            AND MONTH(birth_date) = MONTH(CURDATE())
            AND DAY(birth_date) BETWEEN DAY(CURDATE()) AND DAY(DATE_ADD(CURDATE(), INTERVAL 7 DAY))
            AND is_active = 1
        ";

        $customers = Database::fetchAll($sql);
        $template = $this->getTemplateByType('birthday');

        if (!$template) {
            return ['error' => 'Birthday template not found'];
        }

        foreach ($customers as $customer) {
            // Check if already scheduled for this year
            $checkSql = "
                SELECT id FROM service_reminders
                WHERE customer_id = ?
                AND reminder_type = 'birthday'
                AND YEAR(due_date) = YEAR(CURDATE())
            ";
            $existing = Database::fetchOne($checkSql, [$customer['id']]);

            if (!$existing) {
                $birthDate = new \DateTime($customer['birth_date']);
                $thisYearBirthday = new \DateTime(date('Y') . '-' . $birthDate->format('m-d'));

                try {
                    $reminderId = $this->scheduleReminder(
                        $customer['id'],
                        'birthday',
                        $thisYearBirthday,
                        $template['id']
                    );

                    $results[] = [
                        'customer_id' => $customer['id'],
                        'reminder_id' => $reminderId,
                        'status' => 'scheduled'
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'customer_id' => $customer['id'],
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Get reminder type for equipment
     */
    private function getReminderTypeForEquipment(string $equipmentType): string
    {
        $typeMap = [
            'tank' => 'tank_vip',  // or tank_hydro based on service type
            'regulator' => 'regulator_service',
            'bcd' => 'bcd_service',
        ];

        return $typeMap[$equipmentType] ?? 'custom';
    }

    /**
     * Get template by type
     */
    private function getTemplateByType(string $type): ?array
    {
        $sql = "
            SELECT * FROM service_reminder_templates
            WHERE reminder_type = ?
            AND is_active = 1
            LIMIT 1
        ";

        return Database::fetchOne($sql, [$type]);
    }

    /**
     * Get all reminder templates
     */
    public function getAllTemplates(): array
    {
        $sql = "SELECT * FROM service_reminder_templates ORDER BY reminder_type, name";
        return Database::fetchAll($sql);
    }

    /**
     * Create reminder template
     */
    public function createTemplate(array $data): int
    {
        $sql = "
            INSERT INTO service_reminder_templates (
                name, reminder_type, days_before, email_subject, email_body,
                sms_message, send_email, send_sms, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        Database::query($sql, [
            $data['name'],
            $data['reminder_type'],
            $data['days_before'] ?? 30,
            $data['email_subject'] ?? '',
            $data['email_body'] ?? '',
            $data['sms_message'] ?? '',
            $data['send_email'] ?? true,
            $data['send_sms'] ?? false,
            $data['is_active'] ?? true
        ]);

        return Database::lastInsertId();
    }
}
