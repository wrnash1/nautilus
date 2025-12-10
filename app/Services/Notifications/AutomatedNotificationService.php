<?php

namespace App\Services\Notifications;

use App\Core\Database;
use App\Services\Email\EmailService;

/**
 * Automated Notification Service
 *
 * Handles automated email notifications for various business events:
 * - Low stock alerts
 * - Equipment maintenance due
 * - Customer milestone celebrations
 * - Course enrollment confirmations
 * - Transaction receipts
 * - Equipment rental reminders
 */
class AutomatedNotificationService
{
    private EmailService $emailService;
    private array $config;

    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->loadNotificationSettings();
    }

    /**
     * Load notification settings from database
     */
    private function loadNotificationSettings(): void
    {
        $settings = Database::fetchOne(
            "SELECT * FROM notification_settings WHERE id = 1"
        );

        $this->config = [
            'low_stock_enabled' => $settings['low_stock_enabled'] ?? true,
            'maintenance_enabled' => $settings['maintenance_enabled'] ?? true,
            'course_enabled' => $settings['course_enabled'] ?? true,
            'rental_enabled' => $settings['rental_enabled'] ?? true,
            'admin_email' => $settings['admin_email'] ?? $_ENV['ADMIN_EMAIL'] ?? 'admin@nautilus.local',
            'manager_email' => $settings['manager_email'] ?? $_ENV['MANAGER_EMAIL'] ?? 'manager@nautilus.local',
        ];
    }

    /**
     * Send low stock alert to inventory manager
     */
    public function sendLowStockAlert(): int
    {
        if (!$this->config['low_stock_enabled']) {
            return 0;
        }

        $lowStockProducts = Database::fetchAll(
            "SELECT p.id, p.name, p.sku, p.stock_quantity, p.low_stock_threshold,
                    pc.name as category_name
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             WHERE p.stock_quantity <= p.low_stock_threshold
             AND p.is_active = 1
             ORDER BY p.stock_quantity ASC"
        );

        if (empty($lowStockProducts)) {
            return 0;
        }

        $body = $this->renderLowStockEmail($lowStockProducts);

        $this->emailService->send(
            $this->config['manager_email'],
            'Low Stock Alert - ' . count($lowStockProducts) . ' Items Need Restocking',
            $body,
            ['is_html' => true]
        );

        // Log notification
        $this->logNotification('low_stock_alert', $this->config['manager_email'], count($lowStockProducts));

        return count($lowStockProducts);
    }

    /**
     * Send equipment maintenance due notifications
     */
    public function sendMaintenanceDueAlerts(): int
    {
        if (!$this->config['maintenance_enabled']) {
            return 0;
        }

        $maintenanceDue = Database::fetchAll(
            "SELECT re.id, re.serial_number, ret.name as equipment_type,
                    re.next_inspection_due,
                    DATEDIFF(re.next_inspection_due, CURDATE()) as days_until_due
             FROM rental_equipment re
             INNER JOIN rental_equipment_types ret ON re.equipment_type_id = ret.id
             WHERE re.status != 'retired'
             AND re.next_inspection_due IS NOT NULL
             AND re.next_inspection_due <= DATE_ADD(CURDATE(), INTERVAL 7 DAYS)
             ORDER BY re.next_inspection_due ASC"
        );

        if (empty($maintenanceDue)) {
            return 0;
        }

        $body = $this->renderMaintenanceDueEmail($maintenanceDue);

        $this->emailService->send(
            $this->config['manager_email'],
            'Equipment Maintenance Alert - ' . count($maintenanceDue) . ' Items Due',
            $body,
            ['is_html' => true]
        );

        // Log notification
        $this->logNotification('maintenance_due', $this->config['manager_email'], count($maintenanceDue));

        return count($maintenanceDue);
    }

    /**
     * Send course enrollment confirmation to customer
     */
    public function sendCourseEnrollmentConfirmation(int $enrollmentId): bool
    {
        if (!$this->config['course_enabled']) {
            return false;
        }

        $enrollment = Database::fetchOne(
            "SELECT ce.*, c.name as course_name, c.course_code,
                    cu.first_name, cu.last_name, cu.email,
                    cs.start_date, cs.end_date
             FROM course_enrollments ce
             INNER JOIN courses c ON ce.course_id = c.id
             INNER JOIN customers cu ON ce.customer_id = cu.id
             LEFT JOIN course_schedules cs ON ce.schedule_id = cs.id
             WHERE ce.id = ?",
            [$enrollmentId]
        );

        if (!$enrollment || empty($enrollment['email'])) {
            return false;
        }

        $body = $this->renderCourseEnrollmentEmail($enrollment);

        $result = $this->emailService->send(
            $enrollment['email'],
            'Course Enrollment Confirmation - ' . $enrollment['course_name'],
            $body,
            [
                'is_html' => true,
                'to_name' => $enrollment['first_name'] . ' ' . $enrollment['last_name']
            ]
        );

        if ($result) {
            $this->logNotification('course_enrollment', $enrollment['email'], $enrollmentId);
        }

        return $result;
    }

    /**
     * Send transaction receipt to customer
     */
    public function sendTransactionReceipt(int $transactionId): bool
    {
        $transaction = Database::fetchOne(
            "SELECT t.*, c.first_name, c.last_name, c.email,
                    u.first_name as cashier_first_name, u.last_name as cashier_last_name
             FROM transactions t
             LEFT JOIN customers c ON t.customer_id = c.id
             LEFT JOIN users u ON t.cashier_id = u.id
             WHERE t.id = ?",
            [$transactionId]
        );

        if (!$transaction || empty($transaction['email'])) {
            return false;
        }

        $items = Database::fetchAll(
            "SELECT ti.*, p.name as product_name
             FROM transaction_items ti
             LEFT JOIN products p ON ti.product_id = p.id
             WHERE ti.transaction_id = ?",
            [$transactionId]
        );

        $body = $this->renderTransactionReceiptEmail($transaction, $items);

        $result = $this->emailService->send(
            $transaction['email'],
            'Receipt for Transaction #' . $transaction['transaction_number'],
            $body,
            [
                'is_html' => true,
                'to_name' => $transaction['first_name'] . ' ' . $transaction['last_name']
            ]
        );

        if ($result) {
            $this->logNotification('transaction_receipt', $transaction['email'], $transactionId);
        }

        return $result;
    }

    /**
     * Send rental reminder to customer
     */
    public function sendRentalReminder(int $rentalId, int $daysBefore = 1): bool
    {
        if (!$this->config['rental_enabled']) {
            return false;
        }

        $rental = Database::fetchOne(
            "SELECT rt.*, c.first_name, c.last_name, c.email,
                    re.serial_number, ret.name as equipment_name
             FROM rental_transactions rt
             INNER JOIN customers c ON rt.customer_id = c.id
             INNER JOIN rental_equipment re ON rt.equipment_id = re.id
             INNER JOIN rental_equipment_types ret ON re.equipment_type_id = ret.id
             WHERE rt.id = ?
             AND rt.status = 'active'",
            [$rentalId]
        );

        if (!$rental || empty($rental['email'])) {
            return false;
        }

        $body = $this->renderRentalReminderEmail($rental, $daysBefore);

        $result = $this->emailService->send(
            $rental['email'],
            'Rental Return Reminder - ' . $rental['equipment_name'],
            $body,
            [
                'is_html' => true,
                'to_name' => $rental['first_name'] . ' ' . $rental['last_name']
            ]
        );

        if ($result) {
            $this->logNotification('rental_reminder', $rental['email'], $rentalId);
        }

        return $result;
    }

    /**
     * Send customer milestone celebration (e.g., 10th purchase, birthday)
     */
    public function sendMilestoneEmail(int $customerId, string $milestoneType, array $data = []): bool
    {
        $customer = Database::fetchOne(
            "SELECT * FROM customers WHERE id = ?",
            [$customerId]
        );

        if (!$customer || empty($customer['email'])) {
            return false;
        }

        $body = $this->renderMilestoneEmail($customer, $milestoneType, $data);

        $subject = match($milestoneType) {
            'purchase_count' => '🎉 Congratulations on Your ' . $data['count'] . 'th Purchase!',
            'birthday' => '🎂 Happy Birthday from Nautilus Dive Shop!',
            'anniversary' => '🎊 Happy Customer Anniversary!',
            default => 'Special Message from Nautilus'
        };

        $result = $this->emailService->send(
            $customer['email'],
            $subject,
            $body,
            [
                'is_html' => true,
                'to_name' => $customer['first_name'] . ' ' . $customer['last_name']
            ]
        );

        if ($result) {
            $this->logNotification('milestone_' . $milestoneType, $customer['email'], $customerId);
        }

        return $result;
    }

    /**
     * Render low stock email template
     */
    private function renderLowStockEmail(array $products): string
    {
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                table { border-collapse: collapse; width: 100%; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #0066cc; color: white; }
                .urgent { color: #dc3545; font-weight: bold; }
                .warning { color: #ffc107; font-weight: bold; }
            </style>
        </head>
        <body>
            <h2>Low Stock Alert</h2>
            <p>The following products are running low on stock and need to be restocked:</p>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Threshold</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($products as $product) {
            $urgencyClass = $product['stock_quantity'] == 0 ? 'urgent' : 'warning';
            $html .= '
                    <tr>
                        <td>' . htmlspecialchars($product['name']) . '</td>
                        <td>' . htmlspecialchars($product['sku']) . '</td>
                        <td>' . htmlspecialchars($product['category_name'] ?? 'N/A') . '</td>
                        <td class="' . $urgencyClass . '">' . $product['stock_quantity'] . '</td>
                        <td>' . $product['low_stock_threshold'] . '</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>
            <p>Please take action to restock these items.</p>
        </body>
        </html>';

        return $html;
    }

    /**
     * Render maintenance due email template
     */
    private function renderMaintenanceDueEmail(array $equipment): string
    {
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                table { border-collapse: collapse; width: 100%; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #0066cc; color: white; }
                .overdue { color: #dc3545; font-weight: bold; }
                .due-soon { color: #ffc107; }
            </style>
        </head>
        <body>
            <h2>Equipment Maintenance Due</h2>
            <p>The following equipment items require maintenance or inspection:</p>
            <table>
                <thead>
                    <tr>
                        <th>Equipment Type</th>
                        <th>Serial Number</th>
                        <th>Due Date</th>
                        <th>Days Until Due</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($equipment as $item) {
            $dueClass = $item['days_until_due'] < 0 ? 'overdue' : 'due-soon';
            $daysText = $item['days_until_due'] < 0 ?
                abs($item['days_until_due']) . ' days overdue' :
                $item['days_until_due'] . ' days';

            $html .= '
                    <tr>
                        <td>' . htmlspecialchars($item['equipment_type']) . '</td>
                        <td>' . htmlspecialchars($item['serial_number']) . '</td>
                        <td>' . date('M d, Y', strtotime($item['next_inspection_due'])) . '</td>
                        <td class="' . $dueClass . '">' . $daysText . '</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>
            <p>Please schedule maintenance for these items as soon as possible.</p>
        </body>
        </html>';

        return $html;
    }

    /**
     * Render course enrollment confirmation email
     */
    private function renderCourseEnrollmentEmail(array $enrollment): string
    {
        return '
        <html>
        <body style="font-family: Arial, sans-serif;">
            <h2>Course Enrollment Confirmation</h2>
            <p>Dear ' . htmlspecialchars($enrollment['first_name']) . ',</p>
            <p>Thank you for enrolling in <strong>' . htmlspecialchars($enrollment['course_name']) . '</strong>!</p>
            <div style="background-color: #f0f0f0; padding: 15px; margin: 20px 0;">
                <h3>Course Details:</h3>
                <p><strong>Course:</strong> ' . htmlspecialchars($enrollment['course_name']) . ' (' . htmlspecialchars($enrollment['course_code']) . ')</p>
                ' . (!empty($enrollment['start_date']) ? '<p><strong>Start Date:</strong> ' . date('M d, Y', strtotime($enrollment['start_date'])) . '</p>' : '') . '
                ' . (!empty($enrollment['end_date']) ? '<p><strong>End Date:</strong> ' . date('M d, Y', strtotime($enrollment['end_date'])) . '</p>' : '') . '
                <p><strong>Amount Paid:</strong> $' . number_format($enrollment['amount_paid'], 2) . '</p>
            </div>
            <p>We look forward to seeing you in class!</p>
            <p>Best regards,<br>Nautilus Dive Shop Team</p>
        </body>
        </html>';
    }

    /**
     * Render transaction receipt email
     */
    private function renderTransactionReceiptEmail(array $transaction, array $items): string
    {
        $itemsHtml = '';
        foreach ($items as $item) {
            $itemsHtml .= '
                <tr>
                    <td>' . htmlspecialchars($item['product_name'] ?? $item['item_name']) . '</td>
                    <td>' . $item['quantity'] . '</td>
                    <td>$' . number_format($item['unit_price'], 2) . '</td>
                    <td>$' . number_format($item['total'], 2) . '</td>
                </tr>';
        }

        return '
        <html>
        <body style="font-family: Arial, sans-serif;">
            <h2>Transaction Receipt</h2>
            <p>Transaction #: <strong>' . htmlspecialchars($transaction['transaction_number']) . '</strong></p>
            <p>Date: ' . date('M d, Y H:i', strtotime($transaction['transaction_date'])) . '</p>
            <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
                <thead>
                    <tr style="background-color: #0066cc; color: white;">
                        <th style="border: 1px solid #ddd; padding: 8px;">Item</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Qty</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Price</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Total</th>
                    </tr>
                </thead>
                <tbody>' . $itemsHtml . '</tbody>
            </table>
            <div style="text-align: right; margin-top: 20px;">
                <p><strong>Subtotal:</strong> $' . number_format($transaction['subtotal'], 2) . '</p>
                <p><strong>Tax:</strong> $' . number_format($transaction['tax'], 2) . '</p>
                <h3><strong>Total:</strong> $' . number_format($transaction['total'], 2) . '</h3>
            </div>
            <p>Thank you for your business!</p>
        </body>
        </html>';
    }

    /**
     * Render rental reminder email
     */
    private function renderRentalReminderEmail(array $rental, int $daysBefore): string
    {
        return '
        <html>
        <body style="font-family: Arial, sans-serif;">
            <h2>Rental Return Reminder</h2>
            <p>Dear ' . htmlspecialchars($rental['first_name']) . ',</p>
            <p>This is a friendly reminder that your rental is due for return in ' . $daysBefore . ' day(s).</p>
            <div style="background-color: #f0f0f0; padding: 15px; margin: 20px 0;">
                <h3>Rental Details:</h3>
                <p><strong>Equipment:</strong> ' . htmlspecialchars($rental['equipment_name']) . '</p>
                <p><strong>Serial Number:</strong> ' . htmlspecialchars($rental['serial_number']) . '</p>
                <p><strong>Due Date:</strong> ' . date('M d, Y', strtotime($rental['due_date'])) . '</p>
            </div>
            <p>Please return the equipment by the due date to avoid late fees.</p>
            <p>Thank you!</p>
        </body>
        </html>';
    }

    /**
     * Render customer milestone email
     */
    private function renderMilestoneEmail(array $customer, string $milestoneType, array $data): string
    {
        $content = match($milestoneType) {
            'purchase_count' => '
                <p>We wanted to take a moment to celebrate with you - you just made your <strong>' . $data['count'] . 'th purchase</strong> with us!</p>
                <p>Thank you for being such a valued customer. As a token of our appreciation, enjoy a special 10% discount on your next purchase!</p>
                <p>Use code: <strong>MILESTONE' . $data['count'] . '</strong></p>',
            'birthday' => '
                <p>Happy Birthday! 🎂</p>
                <p>The entire team at Nautilus Dive Shop wishes you an amazing day filled with joy and adventure!</p>
                <p>Here\'s a special birthday gift: 15% off your next purchase!</p>
                <p>Use code: <strong>BIRTHDAY' . date('Y') . '</strong></p>',
            'anniversary' => '
                <p>It\'s been ' . $data['years'] . ' year(s) since you first became a Nautilus customer!</p>
                <p>Thank you for your continued trust and loyalty. We\'re honored to be part of your diving journey!</p>',
            default => '<p>Thank you for being a valued customer!</p>'
        };

        return '
        <html>
        <body style="font-family: Arial, sans-serif;">
            <h2>Special Message from Nautilus</h2>
            <p>Dear ' . htmlspecialchars($customer['first_name']) . ',</p>
            ' . $content . '
            <p>Best regards,<br>The Nautilus Dive Shop Team</p>
        </body>
        </html>';
    }

    /**
     * Log notification to database for tracking
     */
    private function logNotification(string $type, string $recipient, int $referenceId): void
    {
        Database::query(
            "INSERT INTO notification_log (notification_type, recipient, reference_id, sent_at)
             VALUES (?, ?, ?, NOW())",
            [$type, $recipient, $referenceId]
        );
    }
}
