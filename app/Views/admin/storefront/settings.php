<?php
// app/Views/admin/storefront/settings.php
$pageTitle = $categories[$category]['name'] . ' - Configuration';
ob_start();
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($categories[$category]['name']) ?></h1>
        <p class="text-muted"><?= htmlspecialchars($categories[$category]['description']) ?></p>
    </div>
    <div class="col-md-6 text-end">
        <a href="/store/storefront/settings" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Categories
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="list-group">
            <?php foreach ($categories as $key => $cat): ?>
                <a href="/store/storefront/settings?category=<?= $key ?>" 
                   class="list-group-item list-group-item-action <?= $category === $key ? 'active' : '' ?>">
                    <i class="<?= $cat['icon'] ?> me-2"></i>
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="/store/storefront/settings/update" method="POST" id="settingsForm">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                    <input type="hidden" name="active_category" value="<?= $category ?>">

                    <?php foreach ($settings as $key => $setting): ?>
                        <div class="mb-4">
                            <label for="<?= $key ?>" class="form-label fw-bold">
                                <?= ucwords(str_replace('_', ' ', $setting['description'] ?? $key)) ?>
                            </label>
                            
                            <?php if ($setting['type'] === 'boolean'): ?>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="<?= $key ?>" name="<?= $key ?>" value="1" 
                                           <?= $setting['value'] ? 'checked' : '' ?>>
                                    <label class="form-check-label text-muted" for="<?= $key ?>">
                                        <?= $setting['value'] ? 'Enabled' : 'Disabled' ?>
                                    </label>
                                </div>
                            <?php elseif ($setting['type'] === 'textarea'): ?>
                                <textarea class="form-control" id="<?= $key ?>" name="<?= $key ?>" rows="3"><?= htmlspecialchars($setting['value']) ?></textarea>
                            <?php elseif ($setting['type'] === 'number'): ?>
                                <input type="number" class="form-control" id="<?= $key ?>" name="<?= $key ?>" 
                                       value="<?= htmlspecialchars($setting['value']) ?>" step="any">
                            <?php else: ?>
                                <input type="text" class="form-control" id="<?= $key ?>" name="<?= $key ?>" 
                                       value="<?= htmlspecialchars($setting['value']) ?>">
                            <?php endif; ?>
                            
                            <div class="form-text text-muted small">
                                Key: <code><?= $key ?></code>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-flex justify-content-end pt-3 border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Simple helper to update switch labels on toggle
    document.querySelectorAll('.form-check-input').forEach(switchInput => {
        switchInput.addEventListener('change', function() {
            const label = this.nextElementSibling;
            label.textContent = this.checked ? 'Enabled' : 'Disabled';
        });
    });
</script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/admin.php';
?>
