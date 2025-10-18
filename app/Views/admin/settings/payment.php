<?php $pageTitle = 'Payment Settings'; $activeMenu = 'settings'; ob_start(); ?>
<div class="mb-4"><h1 class="h3"><i class="bi bi-credit-card"></i> Payment Settings</h1></div>
<div class="card"><div class="card-body"><p>Payment gateway configuration (Stripe, Square, etc).</p></div></div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>
