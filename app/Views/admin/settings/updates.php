<?php
$pageTitle = 'System Updates';
$activeMenu = 'settings';
$user = currentUser();

ob_start();
?>

<!-- Header -->
<div class="dashboard-header slide-up">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="bi bi-cloud-download"></i> System Updates</h1>
            <p class="text-muted">Manage system version and apply updates.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/admin/settings" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Settings
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        
        <!-- Current Version Card -->
        <div class="modern-card slide-up mb-4">
            <div class="modern-card-header">
                <h2>Current Version</h2>
            </div>
            <div class="modern-card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-light p-3 rounded-circle me-3">
                        <i class="bi bi-git text-primary fs-3"></i>
                    </div>
                    <div>
                        <h3 class="mb-1">Version: <?= htmlspecialchars($current['hash']) ?></h3>
                        <p class="text-muted mb-0">
                            <?= htmlspecialchars($current['date']) ?>
                            <br>
                            <?= htmlspecialchars($current['message']) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Status Card -->
        <div class="modern-card slide-up" style="animation-delay: 0.1s;">
            <div class="modern-card-header">
                <h2>Update Status</h2>
            </div>
            <div class="modern-card-body text-center py-5">
                <?php if ($updates['has_update']): ?>
                    <div class="mb-4">
                        <i class="bi bi-cloud-arrow-down text-info" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="mb-3">New Update Available!</h3>
                    <p class="text-muted mb-4">
                        A new version is available on the remote repository.<br>
                        You are <strong class="text-danger"><?= $updates['commits_behind'] ?> commits</strong> behind.
                    </p>
                    
                    <div class="alert alert-warning text-start mx-auto" style="max-width: 500px;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Warning:</strong> Creating a database backup before updating is highly recommended.
                    </div>

                    <form action="/admin/settings/updates/run" method="POST" class="mt-4" onsubmit="return confirm('Are you sure you want to update? This will pull the latest code and run migrations.');">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-cloud-download me-2"></i> Update Now
                        </button>
                    </form>
                <?php else: ?>
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="mb-2">System is Up to Date</h3>
                    <p class="text-muted">You are running the latest version.</p>
                    <a href="" class="btn btn-outline-primary mt-3">
                        <i class="bi bi-arrow-clockwise"></i> Check Again
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalCss = '<link rel="stylesheet" href="/assets/css/modern-theme.css">';
$additionalJs = '<script src="/assets/js/theme-manager.js"></script>';

require __DIR__ . '/../../layouts/admin.php';
?>
