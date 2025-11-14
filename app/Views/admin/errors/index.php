<?php
$pageTitle = 'Error Logs';
$activeMenu = 'errors';

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
            <li class="breadcrumb-item active">Error Logs</li>
        </ol>
    </nav>

    <h1 class="h3"><i class="bi bi-exclamation-triangle"></i> Application Error Logs</h1>
    <p class="text-muted">Monitor and troubleshoot application errors</p>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<!-- Error Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h3 class="card-title"><?= $stats['total_errors'] ?></h3>
                <p class="card-text">Total Errors (7 days)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h3 class="card-title"><?= $stats['unresolved'] ?></h3>
                <p class="card-text">Unresolved</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-dark text-white">
            <div class="card-body">
                <h3 class="card-title"><?= $stats['fatal_errors'] ?></h3>
                <p class="card-text">Fatal Errors</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h3 class="card-title"><?= $stats['warnings'] ?></h3>
                <p class="card-text">Warnings</p>
            </div>
        </div>
    </div>
</div>

<!-- Error List -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-list"></i> Recent Errors</h5>
    </div>
    <div class="card-body">
        <?php if (empty($errors)): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> No errors recorded! Application is running smoothly.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Message</th>
                            <th>File</th>
                            <th>User</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($errors as $error): ?>
                            <tr>
                                <td>
                                    <?php
                                    $badgeClass = match($error['error_type']) {
                                        'fatal' => 'danger',
                                        'error' => 'warning',
                                        'warning' => 'info',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>">
                                        <?= htmlspecialchars($error['error_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= htmlspecialchars($error['error_message']) ?>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= htmlspecialchars(basename($error['error_file'] ?? '')) ?>:<?= $error['error_line'] ?>
                                    </small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($error['user_name'] ?? 'Guest') ?>
                                </td>
                                <td>
                                    <small><?= date('M d, H:i', strtotime($error['created_at'])) ?></small>
                                </td>
                                <td>
                                    <?php if ($error['is_resolved']): ?>
                                        <span class="badge bg-success">Resolved</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Open</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/store/admin/errors/<?= $error['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
