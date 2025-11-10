<?php
// This view content will be rendered inside layouts/app.php
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-tags-fill"></i> Customer Tags</h1>
        <a href="/store/customers/tags/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Tag
        </a>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill"></i>
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <?= htmlspecialchars($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list"></i> All Tags</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($tags)): ?>
            <div class="text-center py-5">
                <i class="bi bi-tags text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3">No tags created yet.</p>
                <a href="/store/customers/tags/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Your First Tag
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">Color</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th width="100" class="text-center">Customers</th>
                            <th width="100" class="text-center">Status</th>
                            <th width="150" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tags as $tag): ?>
                        <tr>
                            <td>
                                <div style="width: 30px; height: 30px; background-color: <?= htmlspecialchars($tag['color']) ?>; border-radius: 4px;"></div>
                            </td>
                            <td>
                                <span class="badge" style="background-color: <?= htmlspecialchars($tag['color']) ?>; color: white; font-size: 0.9rem;">
                                    <?php if ($tag['icon']): ?>
                                    <i class="<?= htmlspecialchars($tag['icon']) ?>"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($tag['name']) ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted"><?= htmlspecialchars($tag['description'] ?: 'No description') ?></small>
                            </td>
                            <td class="text-center">
                                <?php if ($tag['customer_count'] > 0): ?>
                                <a href="/store/customers?tag=<?= $tag['id'] ?>" class="badge bg-primary">
                                    <?= $tag['customer_count'] ?>
                                </a>
                                <?php else: ?>
                                <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($tag['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTagModal<?= $tag['id'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteTag(<?= $tag['id'] ?>, '<?= htmlspecialchars($tag['name']) ?>', <?= $tag['customer_count'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Tag Modal -->
                        <div class="modal fade" id="editTagModal<?= $tag['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="/store/customers/tags/<?= $tag['id'] ?>/update">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Tag</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Tag Name *</label>
                                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($tag['name']) ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Color</label>
                                                <input type="color" class="form-control form-control-color" name="color" value="<?= htmlspecialchars($tag['color']) ?>">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Icon (Bootstrap Icon class)</label>
                                                <input type="text" class="form-control" name="icon" value="<?= htmlspecialchars($tag['icon']) ?>" placeholder="bi-star-fill">
                                                <small class="text-muted">Browse icons at <a href="https://icons.getbootstrap.com/" target="_blank">icons.getbootstrap.com</a></small>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($tag['description']) ?></textarea>
                                            </div>

                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="is_active" id="is_active<?= $tag['id'] ?>" <?= $tag['is_active'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_active<?= $tag['id'] ?>">
                                                    Active (can be assigned to customers)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check-circle"></i> Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tag Usage Examples -->
    <?php if (!empty($tags)): ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Tag Usage Guide</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>How to use tags:</strong></p>
                    <ul class="mb-0">
                        <li>Assign tags to customers from their profile page</li>
                        <li>Filter customers by tags in the customer list</li>
                        <li>Use tags for segmented marketing campaigns</li>
                        <li>Create custom workflows based on customer tags</li>
                    </ul>

                    <p class="mt-3 mb-2"><strong>Default tags you can use:</strong></p>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($tags as $tag): ?>
                        <span class="badge" style="background-color: <?= htmlspecialchars($tag['color']) ?>; color: white;">
                            <?php if ($tag['icon']): ?>
                            <i class="<?= htmlspecialchars($tag['icon']) ?>"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($tag['name']) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function deleteTag(tagId, tagName, customerCount) {
    if (customerCount > 0) {
        alert('Cannot delete tag "' + tagName + '" because it is assigned to ' + customerCount + ' customer(s).\n\nPlease remove the tag from all customers first.');
        return;
    }

    if (!confirm('Are you sure you want to delete the tag "' + tagName + '"?\n\nThis action cannot be undone.')) {
        return;
    }

    // Create a hidden form to submit DELETE request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/store/customers/tags/' + tagId + '/delete';
    document.body.appendChild(form);
    form.submit();
}
</script>
