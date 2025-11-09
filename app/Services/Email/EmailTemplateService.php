<?php

namespace App\Services\Email;

use App\Middleware\TenantMiddleware;
use App\Services\Tenant\TenantService;

/**
 * Email Template Service
 *
 * Manages email templates with tenant-specific customization
 */
class EmailTemplateService
{
    private TenantService $tenantService;

    public function __construct()
    {
        $this->tenantService = new TenantService();
    }

    /**
     * Get email template with tenant branding
     */
    public function getTemplate(string $templateName, array $variables = []): string
    {
        $tenant = TenantMiddleware::getCurrentTenant();

        // Load base template
        $template = $this->loadTemplate($templateName);

        // Apply tenant branding
        $template = $this->applyBranding($template, $tenant);

        // Replace variables
        $template = $this->replaceVariables($template, $variables);

        return $template;
    }

    /**
     * Send welcome email to new tenant
     */
    public function sendTenantWelcome(array $tenant, array $adminUser): string
    {
        $variables = [
            'company_name' => $tenant['company_name'],
            'admin_name' => $adminUser['first_name'] . ' ' . $adminUser['last_name'],
            'subdomain' => $tenant['subdomain'],
            'login_url' => 'https://' . $tenant['subdomain'] . '.' . ($_ENV['BASE_DOMAIN'] ?? 'nautilus.local') . '/login',
            'trial_days' => 14,
            'trial_ends' => date('F j, Y', strtotime($tenant['trial_ends_at'])),
            'support_email' => $_ENV['SUPPORT_EMAIL'] ?? 'support@nautilus.com'
        ];

        return $this->loadTemplate('tenant_welcome', $variables);
    }

    /**
     * Send trial expiration reminder
     */
    public function sendTrialExpiring(array $tenant, int $daysRemaining): string
    {
        $variables = [
            'company_name' => $tenant['company_name'],
            'days_remaining' => $daysRemaining,
            'upgrade_url' => 'https://' . $tenant['subdomain'] . '.' . ($_ENV['BASE_DOMAIN'] ?? 'nautilus.local') . '/settings/subscription',
            'support_email' => $_ENV['SUPPORT_EMAIL'] ?? 'support@nautilus.com'
        ];

        return $this->loadTemplate('trial_expiring', $variables);
    }

    /**
     * Send invoice email
     */
    public function sendInvoice(array $tenant, array $invoice): string
    {
        $variables = [
            'company_name' => $tenant['company_name'],
            'invoice_number' => $invoice['invoice_number'],
            'invoice_date' => date('F j, Y', strtotime($invoice['created_at'])),
            'amount_due' => number_format($invoice['total'], 2),
            'currency' => $invoice['currency'],
            'due_date' => date('F j, Y', strtotime($invoice['billing_period_end'])),
            'invoice_url' => 'https://' . $tenant['subdomain'] . '.' . ($_ENV['BASE_DOMAIN'] ?? 'nautilus.local') . '/billing/invoice/' . $invoice['id'],
            'line_items' => $this->formatLineItems(json_decode($invoice['line_items'], true))
        ];

        return $this->loadTemplate('invoice', $variables);
    }

    /**
     * Send user invitation email
     */
    public function sendUserInvitation(array $invitation, array $tenant): string
    {
        $acceptUrl = 'https://' . $tenant['subdomain'] . '.' .
                     ($_ENV['BASE_DOMAIN'] ?? 'nautilus.local') .
                     '/accept-invitation?token=' . $invitation['token'];

        $variables = [
            'company_name' => $tenant['company_name'],
            'role' => ucfirst($invitation['role']),
            'accept_url' => $acceptUrl,
            'expires_at' => date('F j, Y', strtotime($invitation['expires_at']))
        ];

        return $this->loadTemplate('user_invitation', $variables);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset(array $user, string $resetToken): string
    {
        $tenant = TenantMiddleware::getCurrentTenant();

        $resetUrl = 'https://' . $tenant['subdomain'] . '.' .
                    ($_ENV['BASE_DOMAIN'] ?? 'nautilus.local') .
                    '/reset-password?token=' . $resetToken;

        $variables = [
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'reset_url' => $resetUrl,
            'expires_in' => '1 hour',
            'support_email' => $_ENV['SUPPORT_EMAIL'] ?? 'support@nautilus.com'
        ];

        return $this->loadTemplate('password_reset', $variables);
    }

    /**
     * Send transaction receipt
     */
    public function sendTransactionReceipt(array $transaction, array $customer): string
    {
        $tenant = TenantMiddleware::getCurrentTenant();

        $variables = [
            'company_name' => $tenant['company_name'],
            'customer_name' => $customer['first_name'] . ' ' . $customer['last_name'],
            'transaction_number' => $transaction['transaction_number'],
            'transaction_date' => date('F j, Y g:i A', strtotime($transaction['created_at'])),
            'items' => $this->formatTransactionItems($transaction['id']),
            'subtotal' => number_format($transaction['subtotal'] ?? 0, 2),
            'tax' => number_format($transaction['tax_amount'] ?? 0, 2),
            'total' => number_format($transaction['total_amount'], 2),
            'payment_method' => ucfirst($transaction['payment_method'] ?? 'cash')
        ];

        return $this->loadTemplate('transaction_receipt', $variables);
    }

    /**
     * Send course enrollment confirmation
     */
    public function sendCourseEnrollment(array $enrollment, array $course, array $customer): string
    {
        $tenant = TenantMiddleware::getCurrentTenant();

        $variables = [
            'company_name' => $tenant['company_name'],
            'customer_name' => $customer['first_name'] . ' ' . $customer['last_name'],
            'course_name' => $course['name'],
            'course_date' => date('F j, Y', strtotime($enrollment['start_date'])),
            'course_time' => date('g:i A', strtotime($enrollment['start_date'])),
            'instructor' => $enrollment['instructor_name'] ?? 'TBD',
            'location' => $course['location'] ?? 'Our facility',
            'price' => number_format($enrollment['price'], 2)
        ];

        return $this->loadTemplate('course_enrollment', $variables);
    }

    /**
     * Load template file
     */
    private function loadTemplate(string $templateName, array $variables = []): string
    {
        $templatePath = __DIR__ . '/../../templates/email/' . $templateName . '.html';

        if (file_exists($templatePath)) {
            $template = file_get_contents($templatePath);
            return $this->replaceVariables($template, $variables);
        }

        // Return default template if custom not found
        return $this->getDefaultTemplate($templateName, $variables);
    }

    /**
     * Apply tenant branding to template
     */
    private function applyBranding(string $template, ?array $tenant): string
    {
        if (!$tenant) {
            return $template;
        }

        $replacements = [
            '{{COMPANY_NAME}}' => $tenant['company_name'] ?? 'Nautilus',
            '{{COMPANY_LOGO}}' => $tenant['logo_url'] ?? '',
            '{{PRIMARY_COLOR}}' => $tenant['primary_color'] ?? '#0066cc',
            '{{SECONDARY_COLOR}}' => $tenant['secondary_color'] ?? '#004999',
            '{{COMPANY_EMAIL}}' => $tenant['contact_email'] ?? '',
            '{{COMPANY_PHONE}}' => $tenant['contact_phone'] ?? ''
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Replace variables in template
     */
    private function replaceVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . strtoupper($key) . '}}', $value, $template);
        }

        return $template;
    }

    /**
     * Format invoice line items
     */
    private function formatLineItems(?array $lineItems): string
    {
        if (empty($lineItems)) {
            return '';
        }

        $html = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
        $html .= '<thead><tr style="background: #f5f5f5;">';
        $html .= '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Description</th>';
        $html .= '<th style="padding: 10px; text-align: right; border: 1px solid #ddd;">Amount</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($lineItems as $item) {
            $html .= '<tr>';
            $html .= '<td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($item['description']) . '</td>';
            $html .= '<td style="padding: 10px; text-align: right; border: 1px solid #ddd;">$' . number_format($item['amount'], 2) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Format transaction items
     */
    private function formatTransactionItems(int $transactionId): string
    {
        $items = \App\Core\TenantDatabase::fetchAllTenant(
            "SELECT * FROM pos_transaction_items WHERE transaction_id = ?",
            [$transactionId]
        );

        if (empty($items)) {
            return '';
        }

        $html = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
        $html .= '<thead><tr style="background: #f5f5f5;">';
        $html .= '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Item</th>';
        $html .= '<th style="padding: 10px; text-align: center; border: 1px solid #ddd;">Qty</th>';
        $html .= '<th style="padding: 10px; text-align: right; border: 1px solid #ddd;">Price</th>';
        $html .= '<th style="padding: 10px; text-align: right; border: 1px solid #ddd;">Total</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($items as $item) {
            $html .= '<tr>';
            $html .= '<td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($item['product_name']) . '</td>';
            $html .= '<td style="padding: 10px; text-align: center; border: 1px solid #ddd;">' . $item['quantity'] . '</td>';
            $html .= '<td style="padding: 10px; text-align: right; border: 1px solid #ddd;">$' . number_format($item['unit_price'], 2) . '</td>';
            $html .= '<td style="padding: 10px; text-align: right; border: 1px solid #ddd;">$' . number_format($item['line_total'], 2) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Get default template HTML
     */
    private function getDefaultTemplate(string $templateName, array $variables): string
    {
        // Base HTML structure with tenant branding
        $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{COMPANY_NAME}}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: {{PRIMARY_COLOR}}; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        ' . (isset($variables['company_logo']) && $variables['company_logo'] ?
            '<img src="{{COMPANY_LOGO}}" alt="{{COMPANY_NAME}}" style="max-height: 60px;">' :
            '<h1 style="margin: 0;">{{COMPANY_NAME}}</h1>') . '
    </div>
    <div style="background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none;">
        ' . $this->getTemplateContent($templateName, $variables) . '
    </div>
    <div style="background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 5px 5px;">
        <p style="margin: 0;">© ' . date('Y') . ' {{COMPANY_NAME}}. All rights reserved.</p>
        <p style="margin: 5px 0 0 0;">
            <a href="mailto:{{COMPANY_EMAIL}}" style="color: {{PRIMARY_COLOR}};">{{COMPANY_EMAIL}}</a>
            ' . (isset($variables['company_phone']) ? '| {{COMPANY_PHONE}}' : '') . '
        </p>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Get template-specific content
     */
    private function getTemplateContent(string $templateName, array $variables): string
    {
        switch ($templateName) {
            case 'tenant_welcome':
                return '
                    <h2 style="color: {{PRIMARY_COLOR}};">Welcome to Nautilus!</h2>
                    <p>Hi {{ADMIN_NAME}},</p>
                    <p>Thank you for choosing Nautilus for {{COMPANY_NAME}}. Your account has been successfully created!</p>
                    <p><strong>Your Details:</strong></p>
                    <ul>
                        <li>Subdomain: <strong>{{SUBDOMAIN}}</strong></li>
                        <li>Trial Period: {{TRIAL_DAYS}} days (expires {{TRIAL_ENDS}})</li>
                    </ul>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{{LOGIN_URL}}" style="background: {{PRIMARY_COLOR}}; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Access Your Dashboard</a>
                    </p>
                    <p>If you have any questions, please contact us at <a href="mailto:{{SUPPORT_EMAIL}}">{{SUPPORT_EMAIL}}</a></p>
                ';

            case 'trial_expiring':
                return '
                    <h2 style="color: #ffc107;">Your Trial is Expiring Soon</h2>
                    <p>Hi,</p>
                    <p>Your {{TRIAL_DAYS}}-day trial for {{COMPANY_NAME}} will expire in <strong>{{DAYS_REMAINING}} days</strong>.</p>
                    <p>To continue using Nautilus without interruption, please upgrade to a paid plan.</p>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{{UPGRADE_URL}}" style="background: {{PRIMARY_COLOR}}; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Upgrade Now</a>
                    </p>
                ';

            case 'invoice':
                return '
                    <h2 style="color: {{PRIMARY_COLOR}};">Invoice #{{INVOICE_NUMBER}}</h2>
                    <p>Date: {{INVOICE_DATE}}</p>
                    <p>Dear {{COMPANY_NAME}},</p>
                    <p>Thank you for your continued business. Here is your invoice:</p>
                    {{LINE_ITEMS}}
                    <div style="text-align: right; font-size: 18px; margin: 20px 0;">
                        <strong>Total: {{CURRENCY}} {{AMOUNT_DUE}}</strong>
                    </div>
                    <p>Due Date: {{DUE_DATE}}</p>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{{INVOICE_URL}}" style="background: {{PRIMARY_COLOR}}; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">View Invoice</a>
                    </p>
                ';

            case 'user_invitation':
                return '
                    <h2 style="color: {{PRIMARY_COLOR}};">You\'ve Been Invited!</h2>
                    <p>You have been invited to join <strong>{{COMPANY_NAME}}</strong> on Nautilus as a <strong>{{ROLE}}</strong>.</p>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{{ACCEPT_URL}}" style="background: {{PRIMARY_COLOR}}; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Accept Invitation</a>
                    </p>
                    <p style="font-size: 12px; color: #666;">This invitation expires on {{EXPIRES_AT}}</p>
                ';

            case 'password_reset':
                return '
                    <h2 style="color: {{PRIMARY_COLOR}};">Password Reset Request</h2>
                    <p>Hi {{USER_NAME}},</p>
                    <p>We received a request to reset your password. Click the button below to create a new password:</p>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{{RESET_URL}}" style="background: {{PRIMARY_COLOR}}; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Password</a>
                    </p>
                    <p style="font-size: 12px; color: #666;">This link will expire in {{EXPIRES_IN}}. If you didn\'t request this, please ignore this email.</p>
                ';

            case 'transaction_receipt':
                return '
                    <h2 style="color: {{PRIMARY_COLOR}};">Transaction Receipt</h2>
                    <p>Hi {{CUSTOMER_NAME}},</p>
                    <p>Thank you for your purchase!</p>
                    <p><strong>Transaction #:</strong> {{TRANSACTION_NUMBER}}<br>
                    <strong>Date:</strong> {{TRANSACTION_DATE}}</p>
                    {{ITEMS}}
                    <div style="text-align: right; margin: 20px 0;">
                        <p style="margin: 5px 0;">Subtotal: ${{SUBTOTAL}}</p>
                        <p style="margin: 5px 0;">Tax: ${{TAX}}</p>
                        <p style="font-size: 18px; margin: 5px 0;"><strong>Total: ${{TOTAL}}</strong></p>
                    </div>
                    <p><strong>Payment Method:</strong> {{PAYMENT_METHOD}}</p>
                ';

            case 'course_enrollment':
                return '
                    <h2 style="color: {{PRIMARY_COLOR}};">Course Enrollment Confirmation</h2>
                    <p>Hi {{CUSTOMER_NAME}},</p>
                    <p>You have been successfully enrolled in <strong>{{COURSE_NAME}}</strong>!</p>
                    <p><strong>Course Details:</strong></p>
                    <ul>
                        <li>Date: {{COURSE_DATE}}</li>
                        <li>Time: {{COURSE_TIME}}</li>
                        <li>Instructor: {{INSTRUCTOR}}</li>
                        <li>Location: {{LOCATION}}</li>
                        <li>Price: ${{PRICE}}</li>
                    </ul>
                    <p>We look forward to seeing you!</p>
                ';

            default:
                return '<p>Email content</p>';
        }
    }
}
