<?php $pageTitle = 'Rental Settings'; $activeMenu = 'settings'; ob_start(); ?>
<div class="mb-4"><h1 class="h3"><i class="bi bi-briefcase"></i> Rental Settings</h1></div>
<div class="card"><div class="card-body"><p>Rental policies, deposits, and late fees configuration.</p></div></div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>
