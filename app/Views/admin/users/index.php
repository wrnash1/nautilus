<?php $pageTitle = 'User Management'; $activeMenu = 'users'; ob_start(); ?>
<div class="d-flex justify-content-between mb-4">
    <h1 class="h3"><i class="bi bi-people"></i> User Management</h1>
    <a href="/store/admin/users/create" class="btn btn-primary"><i class="bi bi-plus"></i> Add User</a>
</div>
<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['role_name']) ?></td>
                    <td><span class="badge bg-<?= $u['is_active'] ? 'success' : 'danger' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    <td>
                        <a href="/admin/users/<?= $u['id'] ?>" class="btn btn-sm btn-info">View</a>
                        <a href="/admin/users/<?= $u['id'] ?>/edit" class="btn btn-sm btn-primary">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/admin.php'; ?>
