<?php $pageTitle = 'User Details';
$activeMenu = 'users';
ob_start(); ?>
<div class="d-flex justify-content-between mb-4">
    <h1 class="h3"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
    <div>
        <a href="/store/admin/users/<?= $user['id'] ?>/edit" class="btn btn-primary">Edit</a>
        <a href="/store/admin/users" class="btn btn-secondary">Back</a>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <h5>User Information</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Name:</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></dd>
                    <dt class="col-sm-3">Email:</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($user['email']) ?></dd>
                    <dt class="col-sm-3">Phone:</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($user['phone'] ?: 'N/A') ?></dd>
                    <dt class="col-sm-3">Role:</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($user['role_name']) ?></dd>
                    <dt class="col-sm-3">Status:</dt>
                    <dd class="col-sm-9"><span
                            class="badge bg-<?= $user['is_active'] ? 'success' : 'danger' ?>"><?= $user['is_active'] ? 'Active' : 'Inactive' ?></span>
                    </dd>
                    <dt class="col-sm-3">Created:</dt>
                    <dd class="col-sm-9"><?= date('M d, Y', strtotime($user['created_at'])) ?></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6>Actions</h6>
            </div>
            <div class="card-body d-grid gap-2">
                <form method="POST" action="/store/admin/users/<?= $user['id'] ?>/reset-password">
                    <input type="hidden" name="csrf_token"
                        value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                    <button type="submit" class="btn btn-warning w-100"
                        onclick="return confirm('Reset password?')">Reset Password</button>
                </form>
                <form method="POST" action="/store/admin/users/<?= $user['id'] ?>/toggle-status">
                    <input type="hidden" name="csrf_token"
                        value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                    <button type="submit"
                        class="btn btn-info w-100"><?= $user['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php'; ?>