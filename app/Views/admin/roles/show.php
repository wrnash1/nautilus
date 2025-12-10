<?php
/**
 * Role Management - Show/Detail View
 * Displays role details, permissions, and assigned users
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Role Details') ?> - Nautilus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <?php include dirname(__DIR__) . '/../layouts/admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid py-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-shield-lock me-2"></i>
                    <?= htmlspecialchars($role['name']) ?>
                </h1>
                <div>
                    <a href="/store/admin/roles/<?= $role['id'] ?>/edit" class="btn btn-primary me-2">
                        <i class="bi bi-pencil me-1"></i> Edit Role
                    </a>
                    <a href="/store/admin/roles" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Roles
                    </a>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Role Info -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Role Information</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-3">Role Name:</dt>
                                <dd class="col-sm-9">
                                    <strong><?= htmlspecialchars($role['name']) ?></strong>
                                </dd>

                                <dt class="col-sm-3">Description:</dt>
                                <dd class="col-sm-9">
                                    <?= htmlspecialchars($role['description'] ?? 'No description provided') ?>
                                </dd>

                                <dt class="col-sm-3">Created:</dt>
                                <dd class="col-sm-9">
                                    <?= date('F j, Y \a\t g:i A', strtotime($role['created_at'])) ?>
                                </dd>

                                <dt class="col-sm-3">Last Updated:</dt>
                                <dd class="col-sm-9 mb-0">
                                    <?= date('F j, Y \a\t g:i A', strtotime($role['updated_at'])) ?>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-key-fill me-2"></i>
                                Assigned Permissions
                                <span class="badge bg-info ms-2"><?= count(array_merge(...array_values($permissions))) ?> total</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($permissions)): ?>
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    This role has no permissions assigned. Users with this role will have limited access.
                                </div>
                            <?php else: ?>
                                <?php foreach ($permissions as $category => $categoryPermissions): ?>
                                    <div class="permission-group mb-4">
                                        <h6 class="text-primary border-bottom pb-2">
                                            <i class="bi bi-folder2-open me-2"></i>
                                            <?= htmlspecialchars($category) ?>
                                            <span class="badge bg-secondary ms-2"><?= count($categoryPermissions) ?></span>
                                        </h6>
                                        <div class="row">
                                            <?php foreach ($categoryPermissions as $permission): ?>
                                                <div class="col-md-6 mb-2">
                                                    <div class="permission-badge">
                                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                        <strong><?= htmlspecialchars($permission['name']) ?></strong>
                                                        <?php if (!empty($permission['description'])): ?>
                                                            <br>
                                                            <small class="text-muted ms-4"><?= htmlspecialchars($permission['description']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Assigned Users -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-people-fill me-2"></i>
                                Users with this Role
                                <span class="badge bg-secondary ms-2"><?= count($users) ?></span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($users)): ?>
                                <div class="p-3">
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        No users are currently assigned to this role.
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td>
                                                        <i class="bi bi-person-circle me-2 text-primary"></i>
                                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                                    <td class="text-center">
                                                        <a href="/store/admin/users/<?= $user['id'] ?>"
                                                           class="btn btn-sm btn-outline-primary"
                                                           title="View User">
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
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Quick Stats -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-bar-chart-fill me-2"></i>
                                Quick Stats
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <small class="text-muted">Total Permissions</small>
                                    <h4 class="mb-0"><?= count(array_merge(...array_values($permissions))) ?></h4>
                                </div>
                                <i class="bi bi-key-fill display-6 text-info"></i>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <small class="text-muted">Users Assigned</small>
                                    <h4 class="mb-0"><?= count($users) ?></h4>
                                </div>
                                <i class="bi bi-people-fill display-6 text-success"></i>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-0">
                                <div>
                                    <small class="text-muted">Permission Categories</small>
                                    <h4 class="mb-0"><?= count($permissions) ?></h4>
                                </div>
                                <i class="bi bi-folder2-open display-6 text-warning"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-gear-fill me-2"></i>
                                Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <a href="/store/admin/roles/<?= $role['id'] ?>/edit"
                               class="btn btn-outline-primary w-100 mb-2">
                                <i class="bi bi-pencil me-2"></i>
                                Edit Role
                            </a>
                            <button type="button"
                                    class="btn btn-outline-danger w-100"
                                    onclick="confirmDelete()">
                                <i class="bi bi-trash me-2"></i>
                                Delete Role
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the role "<strong><?= htmlspecialchars($role['name']) ?></strong>"?</p>
                    <?php if (count($users) > 0): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This role is assigned to <?= count($users) ?> user(s).
                            You must reassign these users before deleting this role.
                        </div>
                    <?php else: ?>
                        <p class="text-danger mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            This action cannot be undone.
                        </p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <?php if (count($users) === 0): ?>
                        <form method="POST" action="/store/admin/roles/<?= $role['id'] ?>" class="d-inline">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="btn btn-danger">Delete Role</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete() {
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>

    <style>
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
        .permission-group {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .permission-badge {
            background: white;
            padding: 10px;
            border-radius: 4px;
            border-left: 3px solid #28a745;
        }
    </style>
</body>
</html>
