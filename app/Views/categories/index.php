<?php
$pageTitle = 'Categories';
$activeMenu = 'categories';

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tags"></i> Product Categories</h2>
    <?php if (hasPermission('categories.create')): ?>
    <a href="/categories/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Category
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($categories)): ?>
        <p class="text-muted text-center py-4">No categories found.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= htmlspecialchars($category['name']) ?></td>
                        <td><code><?= htmlspecialchars($category['slug']) ?></code></td>
                        <td><?= htmlspecialchars(substr($category['description'] ?? '', 0, 100)) ?></td>
                        <td>
                            <span class="badge bg-<?= $category['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $category['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if (hasPermission('categories.edit')): ?>
                                <a href="/categories/<?= $category['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (hasPermission('categories.delete')): ?>
                                <form method="POST" action="/categories/<?= $category['id'] ?>/delete" class="d-inline" 
                                      onsubmit="return confirm('Are you sure you want to delete this category?')">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
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

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
