<?php
$subject = 'Welcome to Nautilus Dive Shop!';
ob_start();
?>

<h2>Welcome <?= htmlspecialchars($firstName ?? 'Diver') ?>!</h2>

<p>Thank you for choosing Nautilus Dive Shop. We're excited to have you join our diving community!</p>

<h3>🤿 What You Can Do:</h3>
<ul>
    <li>Browse our extensive dive equipment catalog</li>
    <li>Book dive courses and certifications</li>
    <li>Reserve rental equipment</li>
    <li>Sign up for exciting dive trips</li>
    <li>Track your diving credentials</li>
</ul>

<a href="<?= $_ENV['APP_URL'] ?? 'https://nautilus.com' ?>/login" class="btn">Access Your Account</a>

<?php if (isset($username) && isset($tempPassword)): ?>
<div style="background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
    <p><strong>Your Login Credentials:</strong></p>
    <p>Username: <strong><?= htmlspecialchars($username) ?></strong><br>
    Temporary Password: <strong><?= htmlspecialchars($tempPassword) ?></strong></p>
    <p><small>Please change your password after your first login for security.</small></p>
</div>
<?php endif; ?>

<p>If you have any questions, don't hesitate to reach out. Happy diving!</p>

<p>Dive Safe,<br>The Nautilus Team</p>

<?php
$content = ob_get_clean();
include __DIR__ . '/base.php';
?>
