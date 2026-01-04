<?php
$pageTitle = 'Edit Certification Agency';
$activeMenu = 'certifications';
$user = currentUser();

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-building"></i> Edit Agency</h2>
    <a href="/certifications/agencies" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Agencies
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/certifications/agencies/<?= $agency['id'] ?>/update" id="agencyForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="_method" value="PUT">

            <div class="row g-3">
                <div class="col-md-8">
                    <label for="name" class="form-label">Agency Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="<?= htmlspecialchars($agency['name']) ?>"
                           required maxlength="255">
                </div>

                <div class="col-md-4">
                    <label for="code" class="form-label">Agency Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="code" name="code"
                           value="<?= htmlspecialchars($agency['code']) ?>"
                           required maxlength="20">
                </div>

                <div class="col-md-12">
                    <label for="website" class="form-label">Website</label>
                    <input type="url" class="form-control" id="website" name="website"
                           value="<?= htmlspecialchars($agency['website'] ?? '') ?>"
                           maxlength="255">
                </div>

                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($agency['description'] ?? '') ?></textarea>
                </div>

                <div class="col-md-12">
                    <label for="logo_url" class="form-label">Logo URL</label>
                    <input type="url" class="form-control" id="logo_url" name="logo_url"
                           value="<?= htmlspecialchars($agency['logo_url'] ?? '') ?>"
                           maxlength="500">
                    <?php if (!empty($agency['logo_url'])): ?>
                    <div class="mt-2">
                        <img src="<?= htmlspecialchars($agency['logo_url']) ?>"
                             alt="Current logo"
                             style="max-height: 80px; max-width: 200px; object-fit: contain; border: 1px solid #ddd; padding: 5px;">
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                               <?= !empty($agency['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            Active (available for certifications)
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Agency
                </button>
                <a href="/certifications/agencies" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
?>
