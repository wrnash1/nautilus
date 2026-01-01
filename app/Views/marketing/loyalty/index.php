<?php
$pageTitle = 'Loyalty Programs';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Loyalty Programs</h2>
    <?php if (\App\Core\Auth::hasPermission('marketing.create')): ?>
        <a href="<?= url('/marketing/loyalty/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Program
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Program Name</th>
                        <th>Points Per Dollar</th>
                        <th>Expiry Days</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($programs as $program): ?>
                        <tr>
                            <td><?= htmlspecialchars($program['name']) ?></td>
                            <td><?= htmlspecialchars($program['points_per_dollar']) ?></td>
                            <td><?= $program['points_expiry_days'] ? $program['points_expiry_days'] . ' days' : 'Never' ?></td>
                            <td>
                                <span class="badge bg-<?= $program['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $program['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= url('/marketing/loyalty/' . $program['id']) ?>" class="btn btn-sm btn-info">View</a>
                                <?php if (\App\Core\Auth::hasPermission('marketing.edit')): ?>
                                    <a href="<?= url('/marketing/loyalty/' . $program['id'] . '/edit') ?>" class="btn btn-sm btn-warning">Edit</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
?>
