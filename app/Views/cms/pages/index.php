<?php
$pageTitle = 'Pages';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Pages</h2>
    <?php if (\App\Core\Auth::hasPermission('cms.create')): ?>
        <a href="<?= url('/cms/pages/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Page
        </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Homepage</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pages as $page): ?>
                        <tr>
                            <td><?= htmlspecialchars($page['title']) ?></td>
                            <td><code><?= htmlspecialchars($page['slug']) ?></code></td>
                            <td>
                                <span class="badge bg-<?= $page['status'] === 'published' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($page['status']) ?>
                                </span>
                            </td>
                            <td><?= $page['is_homepage'] ? '✓' : '' ?></td>
                            <td><?= date('M d, Y', strtotime($page['created_at'])) ?></td>
                            <td>
                                <a href="<?= url('/cms/pages/' . $page['id']) ?>" class="btn btn-sm btn-info">View</a>
                                <?php if (\App\Core\Auth::hasPermission('cms.edit')): ?>
                                    <a href="<?= url('/cms/pages/' . $page['id'] . '/edit') ?>" class="btn btn-sm btn-warning">Edit</a>
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
