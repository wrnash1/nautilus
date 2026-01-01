<?php
$pageTitle = 'System Update';
$activeMenu = 'admin';
require __DIR__ . '/../../layouts/admin.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">System Update</h1>
            <p class="text-muted">Manage application version and updates.</p>
        </div>
    </div>

    <div class="row">
        <!-- System Status -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Version</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="small font-weight-bold text-gray-500">Current Commit</label>
                        <div class="d-flex align-items-center">
                            <code class="bg-light px-2 py-1 rounded me-2"><?= htmlspecialchars($currentVersion['hash']) ?></code>
                            <span class="text-muted small"><?= htmlspecialchars($currentVersion['date']) ?></span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="small font-weight-bold text-gray-500">Latest Message</label>
                        <p class="mb-0 bg-light p-3 rounded border-left-primary"><?= htmlspecialchars($currentVersion['message']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Actions -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update Status</h6>
                </div>
                <div class="card-body">
                    <?php if ($isDirty): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Local Changes Detected</strong>
                            <p class="mb-0 mt-2">You have uncommitted local changes. The system cannot update until these are resolved.</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-<?= $updateInfo['has_updates'] ? 'warning' : 'success' ?> p-2 me-2">
                                <?= $updateInfo['has_updates'] ? 'Update Available' : 'Up to Date' ?>
                            </span>
                            <?php if ($updateInfo['has_updates']): ?>
                                <span class="text-muted"><?= $updateInfo['commits_behind'] ?> commits behind origin/main</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($updateInfo['has_updates']): ?>
                            <form method="POST" action="/admin/system/update" onsubmit="return confirm('Are you sure you want to update? This will pull the latest code and run migrations.');">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-cloud-download me-2"></i> Install Updates
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100" disabled>
                                <i class="bi bi-check-circle me-2"></i> System is Up to Date
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Logs -->
    <?php if (!empty($logs)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Update Log</h6>
        </div>
        <div class="card-body bg-dark text-white font-monospace p-3" style="max-height: 300px; overflow-y: auto;">
            <?php foreach ($logs as $log): ?>
                <div><?= htmlspecialchars($log) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
