<?php
$subject = $emailSubject ?? 'Service Reminder';
ob_start();
?>

<h2>Hello <?= htmlspecialchars($firstName ?? 'Valued Customer') ?>,</h2>

<p><?= $message ?? 'This is a reminder about an upcoming service.' ?></p>

<?php if (isset($dueDate)): ?>
<p><strong>Due Date:</strong> <?= date('F j, Y', strtotime($dueDate)) ?></p>
<?php endif; ?>

<?php if (isset($details)): ?>
<div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #0077be; margin: 20px 0;">
    <?= $details ?>
</div>
<?php endif; ?>

<p>To schedule an appointment or if you have any questions, please contact us:</p>
<ul>
    <li><strong>Phone:</strong> <?= $_ENV['BUSINESS_PHONE'] ?? '(555) 123-4567' ?></li>
    <li><strong>Email:</strong> <?= $_ENV['BUSINESS_EMAIL'] ?? 'info@nautilus.com' ?></li>
</ul>

<a href="<?= $_ENV['APP_URL'] ?? 'https://nautilus.com' ?>" class="btn">Visit Our Website</a>

<p>Thank you for your business!</p>
<p>The Nautilus Team</p>

<?php
$content = ob_get_clean();
include __DIR__ . '/base.php';
?>
