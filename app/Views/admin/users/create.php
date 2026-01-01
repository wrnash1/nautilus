<?php $pageTitle = 'Create User'; $activeMenu = 'users'; ob_start(); ?>
<h1 class="h3 mb-4"><i class="bi bi-person-plus"></i> Create New User</h1>
<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/users">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>First Name *</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label>Email *</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Phone</label>
                <input type="tel" name="phone" class="form-control">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Role *</label>
                    <select name="role_id" class="form-select" required>
                        <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Password *</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" checked>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create User</button>
            <a href="/admin/users" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/admin.php'; ?>
