# Automated Notification System

## Overview

The Automated Notification System sends intelligent, timely email notifications for important business events. It helps keep staff informed and customers engaged automatically.

## Features

### 1. Low Stock Alerts
Automatically notifies inventory managers when products fall below restock thresholds.

**Trigger**: Daily at 8 AM
**Recipients**: Inventory manager
**Content**: List of products needing restock with urgency levels

### 2. Equipment Maintenance Alerts
Sends alerts for equipment requiring maintenance or inspection.

**Trigger**: Daily at 8 AM
**Recipients**: Maintenance manager
**Content**: Equipment list with due dates and urgency

### 3. Course Enrollment Confirmations
Automatic confirmation emails when students enroll in courses.

**Trigger**: Immediately after enrollment
**Recipients**: Student
**Content**: Course details, start date, payment information

### 4. Transaction Receipts
Email receipts for customer purchases.

**Trigger**: After transaction completion
**Recipients**: Customer
**Content**: Itemized receipt with transaction details

### 5. Rental Reminders
Reminds customers about upcoming equipment return dates.

**Trigger**: 24 hours before due date
**Recipients**: Customer
**Content**: Equipment details, due date, late fee information

### 6. Customer Milestone Celebrations
Celebrates customer milestones to build loyalty.

**Trigger**: Daily check for milestones
**Recipients**: Customer
**Milestones**:
- Purchase count (10th, 25th, 50th, 100th purchase)
- Customer birthday
- Customer anniversary

## PHP Usage

### Send Low Stock Alert
```php
use App\Services\Notifications\AutomatedNotificationService;

$notificationService = new AutomatedNotificationService();

// Manually trigger low stock alert
$itemCount = $notificationService->sendLowStockAlert();
echo "Sent low stock alert for {$itemCount} items";
```

### Send Course Enrollment Confirmation
```php
$notificationService = new AutomatedNotificationService();

// After creating enrollment
$enrollmentId = 123;
$result = $notificationService->sendCourseEnrollmentConfirmation($enrollmentId);

if ($result) {
    echo "Enrollment confirmation sent successfully";
}
```

### Send Transaction Receipt
```php
$notificationService = new AutomatedNotificationService();

// After completing transaction
$transactionId = 456;
$result = $notificationService->sendTransactionReceipt($transactionId);
```

### Send Rental Reminder
```php
$notificationService = new AutomatedNotificationService();

$rentalId = 789;
$daysBefore = 1; // Send 1 day before due
$result = $notificationService->sendRentalReminder($rentalId, $daysBefore);
```

### Send Milestone Email
```php
$notificationService = new AutomatedNotificationService();

$customerId = 123;

// Purchase milestone
$notificationService->sendMilestoneEmail(
    $customerId,
    'purchase_count',
    ['count' => 50]
);

// Birthday
$notificationService->sendMilestoneEmail(
    $customerId,
    'birthday',
    []
);

// Anniversary
$notificationService->sendMilestoneEmail(
    $customerId,
    'anniversary',
    ['years' => 5]
);
```

## Automated Execution

### Cron Job Setup

Add this to your crontab to run automated notifications every hour:

```bash
0 * * * * cd /path/to/nautilus && php app/Jobs/SendAutomatedNotificationsJob.php >> storage/logs/notifications.log 2>&1
```

Or every 30 minutes for more frequent checks:

```bash
*/30 * * * * cd /path/to/nautilus && php app/Jobs/SendAutomatedNotificationsJob.php >> storage/logs/notifications.log 2>&1
```

### Manual Execution

```bash
cd /path/to/nautilus
php app/Jobs/SendAutomatedNotificationsJob.php
```

## Configuration

### Notification Settings

Settings are stored in the `notification_settings` table:

```sql
-- Update notification settings
UPDATE notification_settings SET
    low_stock_enabled = TRUE,
    maintenance_enabled = TRUE,
    course_enabled = TRUE,
    rental_enabled = TRUE,
    admin_email = 'admin@yourdiveshop.com',
    manager_email = 'manager@yourdiveshop.com'
WHERE id = 1;
```

### Email Templates

Templates are stored in `notification_templates` table and support variable substitution:

```sql
-- Update course enrollment template
UPDATE notification_templates SET
    subject = 'Welcome to {{course_name}}!',
    body_html = '<h2>Enrollment Confirmed</h2>
                 <p>Dear {{customer_name}},</p>
                 <p>You are now enrolled in {{course_name}}.</p>
                 <p>Start Date: {{start_date}}</p>'
WHERE template_key = 'course_enrollment';
```

## Database Tables

### notification_settings
Global notification configuration and toggles.

### notification_log
Audit log of all sent notifications.

### notification_templates
Customizable email templates.

### scheduled_notifications
Queue for notifications to be sent later.

### customer_notification_preferences
Per-customer notification preferences.

### notification_statistics
Metrics on notification performance.

## Customer Preferences

Customers can opt-out of specific notification types:

```php
// Set customer preferences
Database::query(
    "INSERT INTO customer_notification_preferences
     (customer_id, receive_receipts, receive_marketing)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE receive_receipts = ?, receive_marketing = ?",
    [$customerId, true, false, true, false]
);
```

## Notification Tracking

### View Notification Log

```php
// Get recent notifications
$notifications = Database::fetchAll(
    "SELECT * FROM notification_log
     WHERE notification_type = 'course_enrollment'
     ORDER BY sent_at DESC
     LIMIT 50"
);
```

### View Statistics

```php
// Get notification stats for a date range
$stats = Database::fetchAll(
    "SELECT
        notification_type,
        SUM(sent_count) as total_sent,
        AVG(open_rate) as avg_open_rate
     FROM notification_statistics
     WHERE stat_date BETWEEN ? AND ?
     GROUP BY notification_type",
    [$startDate, $endDate]
);
```

## Email Template Variables

### Available Variables by Type

**Low Stock Alert:**
- `{{count}}` - Number of low stock items
- `{{product_table}}` - HTML table of products

**Maintenance Due:**
- `{{count}}` - Number of items needing maintenance
- `{{equipment_table}}` - HTML table of equipment

**Course Enrollment:**
- `{{customer_name}}` - Customer's full name
- `{{course_name}}` - Course name
- `{{course_code}}` - Course code
- `{{start_date}}` - Course start date
- `{{end_date}}` - Course end date
- `{{amount_paid}}` - Amount paid

**Transaction Receipt:**
- `{{transaction_number}}` - Transaction number
- `{{items_table}}` - HTML table of items
- `{{total}}` - Total amount
- `{{date}}` - Transaction date

**Rental Reminder:**
- `{{equipment_name}}` - Equipment name
- `{{serial_number}}` - Serial number
- `{{due_date}}` - Return due date
- `{{days}}` - Days until due

## Scheduled Notifications

Queue notifications to be sent later:

```php
// Schedule a notification
Database::query(
    "INSERT INTO scheduled_notifications
     (notification_type, recipient, subject, body, scheduled_for, priority)
     VALUES (?, ?, ?, ?, ?, ?)",
    [
        'custom_message',
        'customer@example.com',
        'Special Offer',
        '<p>Check out our new gear!</p>',
        '2025-01-15 10:00:00',
        'normal'
    ]
);
```

## Best Practices

1. **Test Before Production**: Send test emails to verify formatting
2. **Monitor Logs**: Check `notification_log` for delivery issues
3. **Respect Preferences**: Honor customer opt-outs
4. **Timing Matters**: Send emails at appropriate times
5. **Keep It Relevant**: Only send notifications that add value
6. **Track Performance**: Monitor open and click rates
7. **Update Templates**: Keep email content fresh and engaging

## Troubleshooting

### Notifications Not Sending

1. Check notification settings:
```sql
SELECT * FROM notification_settings WHERE id = 1;
```

2. Verify email configuration in `.env`:
```
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdiveshop.com
MAIL_FROM_NAME="Nautilus Dive Shop"
```

3. Check cron job is running:
```bash
crontab -l | grep SendAutomatedNotificationsJob
```

4. Review error logs:
```bash
tail -f storage/logs/notifications.log
```

### Duplicate Notifications

The system prevents duplicates by:
- Checking `notification_log` for recent sends
- Tracking timestamps on related records
- Using status flags (e.g., `receipt_sent_at`)

### Failed Deliveries

Check `scheduled_notifications` for failed attempts:
```sql
SELECT * FROM scheduled_notifications
WHERE status = 'failed'
ORDER BY updated_at DESC;
```

## Support

For notification system issues:
1. Check logs in `storage/logs/`
2. Verify database migrations are applied
3. Test email configuration with `test_email.php`
4. Review cron job execution times
