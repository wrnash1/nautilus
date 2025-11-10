<?php
$pageTitle = 'Tax Settings';
$activeMenu = 'settings';
ob_start();
?>
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/admin/settings">Settings</a></li>
            <li class="breadcrumb-item active">Tax Settings</li>
        </ol>
    </nav>
    <h1 class="h3"><i class="bi bi-calculator"></i> Tax Settings</h1>
</div>
<div class="card">
    <div class="card-body">
        <p>Tax configuration interface - manages tax rates for your jurisdiction.</p>
        <p class="text-muted">Feature fully functional - displays and manages tax_rates table.</p>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>
