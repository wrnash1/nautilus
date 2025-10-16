<?php
$pageTitle = 'Staff Management';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Staff Management</h2>
    <div>
        <a href="<?= url('/staff/schedules') ?>" class="btn btn-secondary">Schedules</a>
        <a href="<?= url('/staff/timeclock') ?>" class="btn btn-secondary">Time Clock</a>
        <a href="<?= url('/staff/commissions') ?>" class="btn btn-secondary">Commissions</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staff as $member): ?>
                        <tr>
                            <td><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></td>
                            <td><?= htmlspecialchars($member['email']) ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($member['role_name']) ?></span></td>
                            <td>
                                <span class="badge bg-<?= $member['is_active'] ? 'success' : 'danger' ?>">
                                    <?= $member['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= url('/staff/' . $member['id']) ?>" class="btn btn-sm btn-info">View</a>
                                <a href="<?= url('/staff/' . $member['id'] . '/performance') ?>" class="btn btn-sm btn-success">Performance</a>
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
require __DIR__ . '/../../layouts/app.php';
?>
