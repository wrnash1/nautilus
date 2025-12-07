<?php
$pageTitle = 'Blog Posts';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Blog Posts</h2>
    <div>
        <a href="<?= url('/cms/blog/categories') ?>" class="btn btn-secondary">Categories</a>
        <a href="<?= url('/cms/blog/tags') ?>" class="btn btn-secondary">Tags</a>
        <?php if (\App\Core\Auth::hasPermission('cms.create')): ?>
            <a href="<?= url('/cms/blog/create') ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create Post
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Categories</th>
                        <th>Tags</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?= htmlspecialchars($post['title']) ?></td>
                            <td><?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?></td>
                            <td><span class="badge bg-info"><?= $post['category_count'] ?></span></td>
                            <td><span class="badge bg-secondary"><?= $post['tag_count'] ?></span></td>
                            <td>
                                <span class="badge bg-<?= $post['status'] === 'published' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($post['status']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($post['created_at'])) ?></td>
                            <td>
                                <a href="<?= url('/cms/blog/' . $post['id']) ?>" class="btn btn-sm btn-info">View</a>
                                <?php if (\App\Core\Auth::hasPermission('cms.edit')): ?>
                                    <a href="<?= url('/cms/blog/' . $post['id'] . '/edit') ?>" class="btn btn-sm btn-warning">Edit</a>
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
require __DIR__ . '/../../layouts/app.php';
?>
