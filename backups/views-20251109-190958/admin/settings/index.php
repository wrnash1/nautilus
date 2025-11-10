<?php
$pageTitle = 'Settings';
$activeMenu = 'settings';

ob_start();
?>

<div class="mb-4">
    <h1 class="h3">
        <i class="bi bi-gear"></i> System Settings
    </h1>
    <p class="text-muted">Configure your dive shop business settings and preferences</p>
</div>

<div class="row g-4">
    <?php foreach ($categories as $key => $category): ?>
    <div class="col-md-6 col-lg-4">
        <a href="/admin/settings/<?= $key ?>" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <div class="icon-box bg-primary bg-opacity-10 text-primary p-3 rounded">
                                <i class="<?= $category['icon'] ?>" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-2"><?= $category['name'] ?></h5>
                            <p class="card-text text-muted small mb-0">
                                <?= $category['description'] ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <small class="text-primary">
                        Configure <i class="bi bi-arrow-right"></i>
                    </small>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<style>
.hover-card {
    transition: all 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.icon-box {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
