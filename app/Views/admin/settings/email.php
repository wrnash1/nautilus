<?php $pageTitle = 'Email Settings'; $activeMenu = 'settings'; ob_start(); ?>
<div class="mb-4"><h1 class="h3"><i class="bi bi-envelope"></i> Email Settings</h1></div>
<div class="card"><div class="card-body"><p>SMTP and email configuration - PHPMailer integration ready.</p></div></div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>
