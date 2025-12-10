<?php
$pageTitle = 'Edit Category';
$activeMenu = 'categories';

ob_start();
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/categories">Categories</a></li>
        <li class="breadcrumb-item active">Edit Category</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tags"></i> Edit Category</h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/categories/<?= $category['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="mb-3">
                <label for="name" class="form-label">Category Name *</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?= htmlspecialchars($category['name']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="parent_id" class="form-label">Parent Category</label>
                <select class="form-select" id="parent_id" name="parent_id">
                    <option value="">None (Top Level)</option>
                    <?php foreach ($categories as $cat): ?>
                        <?php if ($cat['id'] != $category['id']): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category['parent_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                           <?= $category['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Category
                </button>
                <a href="/categories" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
