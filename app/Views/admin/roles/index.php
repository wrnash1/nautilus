<?php
/**
 * Role Management - Index View
 * Lists all roles with permissions and user counts
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Role Management') ?> - Nautilus</title>
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
                    <?= htmlspecialchars($page_title ?? 'Role Management') ?>
                </h1>
                <a href="/store/admin/roles/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Create New Role
                </a>
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

            <!-- Roles Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">System Roles</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($roles)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <p class="text-muted mt-3">No roles found. Create your first role to get started.</p>
                            <a href="/store/admin/roles/create" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> Create Role
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Role Name</th>
                                        <th>Description</th>
                                        <th class="text-center">Permissions</th>
                                        <th class="text-center">Users</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roles as $role): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($role['name']) ?></strong>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($role['description'] ?? 'No description') ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">
                                                    <?= (int)$role['permission_count'] ?> permissions
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary">
                                                    <?= (int)$role['user_count'] ?> users
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="/store/admin/roles/<?= $role['id'] ?>"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="/store/admin/roles/<?= $role['id'] ?>/edit"
                                                       class="btn btn-sm btn-outline-secondary"
                                                       title="Edit Role">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="confirmDelete(<?= $role['id'] ?>, '<?= htmlspecialchars($role['name'], ENT_QUOTES) ?>')"
                                                            title="Delete Role">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Roles</h6>
                                    <h3 class="mb-0"><?= count($roles) ?></h3>
                                </div>
                                <div class="text-primary">
                                    <i class="bi bi-shield-lock display-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Users</h6>
                                    <h3 class="mb-0"><?= array_sum(array_column($roles, 'user_count')) ?></h3>
                                </div>
                                <div class="text-success">
                                    <i class="bi bi-people-fill display-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Avg Permissions/Role</h6>
                                    <h3 class="mb-0">
                                        <?= count($roles) > 0 ? round(array_sum(array_column($roles, 'permission_count')) / count($roles)) : 0 ?>
                                    </h3>
                                </div>
                                <div class="text-info">
                                    <i class="bi bi-key-fill display-4"></i>
                                </div>
                            </div>
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
                    <p>Are you sure you want to delete the role "<strong id="roleName"></strong>"?</p>
                    <p class="text-danger mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger">Delete Role</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(roleId, roleName) {
            document.getElementById('roleName').textContent = roleName;
            document.getElementById('deleteForm').action = '/store/admin/roles/' + roleId;
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
    </style>
</body>
</html>
