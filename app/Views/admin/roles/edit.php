<?php
/**
 * Role Management - Edit View
 * Form to edit existing role and update permissions
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Edit Role') ?> - Nautilus</title>
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
                    <i class="bi bi-pencil me-2"></i>
                    <?= htmlspecialchars($page_title ?? 'Edit Role') ?>
                </h1>
                <div>
                    <a href="/store/admin/roles/<?= $role['id'] ?>" class="btn btn-secondary me-2">
                        <i class="bi bi-eye me-1"></i> View
                    </a>
                    <a href="/store/admin/roles" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Roles
                    </a>
                </div>
            </div>

            <!-- Error Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Edit Form -->
            <div class="row">
                <div class="col-lg-8">
                    <form method="POST" action="/store/admin/roles/<?= $role['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="_method" value="PUT">

                        <!-- Basic Information -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Basic Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        Role Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control"
                                           id="name"
                                           name="name"
                                           required
                                           value="<?= htmlspecialchars($role['name']) ?>">
                                    <small class="text-muted">A descriptive name for this role</small>
                                </div>

                                <div class="mb-0">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control"
                                              id="description"
                                              name="description"
                                              rows="3"><?= htmlspecialchars($role['description'] ?? '') ?></textarea>
                                    <small class="text-muted">Optional description to help identify this role's purpose</small>
                                </div>
                            </div>
                        </div>

                        <!-- Permissions -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Assign Permissions</h5>
                                <div>
                                    <button type="button" class="btn btn-sm btn-light" onclick="selectAll()">Select All</button>
                                    <button type="button" class="btn btn-sm btn-light" onclick="selectNone()">Select None</button>
                                    <span class="badge bg-light text-dark ms-2" id="permCount"></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (empty($permissions)): ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        No permissions found. Please contact system administrator.
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-3">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Select the permissions this role should have. Users assigned to this role will inherit these permissions.
                                    </p>

                                    <?php foreach ($permissions as $category => $categoryPermissions): ?>
                                        <div class="permission-category mb-4">
                                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                                <i class="bi bi-folder2-open me-2"></i>
                                                <?= htmlspecialchars($category) ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary float-end"
                                                        onclick="toggleCategory('<?= htmlspecialchars($category) ?>')">
                                                    Toggle All
                                                </button>
                                            </h6>
                                            <div class="row">
                                                <?php foreach ($categoryPermissions as $permission): ?>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input permission-checkbox"
                                                                   type="checkbox"
                                                                   name="permissions[]"
                                                                   value="<?= $permission['id'] ?>"
                                                                   id="perm_<?= $permission['id'] ?>"
                                                                   data-category="<?= htmlspecialchars($category) ?>"
                                                                   <?= $permission['is_assigned'] ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="perm_<?= $permission['id'] ?>">
                                                                <strong><?= htmlspecialchars($permission['name']) ?></strong>
                                                                <?php if (!empty($permission['description'])): ?>
                                                                    <br>
                                                                    <small class="text-muted"><?= htmlspecialchars($permission['description']) ?></small>
                                                                <?php endif; ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="/store/admin/roles" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Update Role
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Sidebar Info -->
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Role Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Created:</dt>
                                <dd class="col-sm-7">
                                    <?= date('M j, Y', strtotime($role['created_at'])) ?>
                                </dd>

                                <dt class="col-sm-5">Last Updated:</dt>
                                <dd class="col-sm-7 mb-0">
                                    <?= date('M j, Y', strtotime($role['updated_at'])) ?>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-lightbulb me-2"></i>
                                Tips
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="small mb-0">
                                <li>Changes take effect immediately for all users with this role</li>
                                <li>Review permissions carefully before saving</li>
                                <li>Use "Toggle All" to quickly manage category permissions</li>
                                <li>Document significant changes in the description</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectAll() {
            document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
            updateCount();
        }

        function selectNone() {
            document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
            updateCount();
        }

        function toggleCategory(category) {
            const checkboxes = document.querySelectorAll(`[data-category="${category}"]`);
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
            updateCount();
        }

        function updateCount() {
            const count = document.querySelectorAll('.permission-checkbox:checked').length;
            document.getElementById('permCount').textContent = `${count} selected`;
        }

        // Initialize count on load
        document.addEventListener('DOMContentLoaded', function() {
            updateCount();
            document.querySelectorAll('.permission-checkbox').forEach(cb => {
                cb.addEventListener('change', updateCount);
            });
        });
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
        .permission-category {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .form-check {
            padding: 10px;
            background: white;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .form-check:hover {
            background-color: #f8f9fa;
        }
        .form-check-input:checked + .form-check-label {
            color: #0d6efd;
        }
    </style>
</body>
</html>
