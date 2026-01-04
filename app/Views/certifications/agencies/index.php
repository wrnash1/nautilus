<?php
$pageTitle = 'Certification Agencies';
$activeMenu = 'certifications';
$user = currentUser();

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-building"></i> Certification Agencies</h2>
    <div>
        <a href="/certifications" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Certifications
        </a>
        <?php if (hasPermission('certifications.create')): ?>
        <a href="/certifications/agencies/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Agency
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <?php if (empty($agencies)): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-building" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No certification agencies found.</p>
                <a href="/certifications/agencies/create" class="btn btn-primary">Add your first agency</a>
            </div>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($agencies as $agency): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start mb-3">
                    <?php if (!empty($agency['logo_url'])): ?>
                    <img src="<?= htmlspecialchars($agency['logo_url']) ?>"
                         alt="<?= htmlspecialchars($agency['name']) ?>"
                         class="me-3"
                         style="max-height: 60px; max-width: 100px; object-fit: contain;">
                    <?php else: ?>
                    <div class="bg-light rounded p-3 me-3">
                        <i class="bi bi-building" style="font-size: 2rem; color: #999;"></i>
                    </div>
                    <?php endif; ?>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1"><?= htmlspecialchars($agency['name']) ?></h5>
                        <span class="badge bg-secondary"><?= htmlspecialchars($agency['code']) ?></span>
                        <?php if ($agency['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($agency['description'])): ?>
                <p class="card-text small text-muted">
                    <?= htmlspecialchars(substr($agency['description'], 0, 100)) ?>
                    <?= strlen($agency['description']) > 100 ? '...' : '' ?>
                </p>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="small">
                        <div><i class="bi bi-award"></i> <?= $agency['certification_count'] ?? 0 ?> certifications</div>
                        <div><i class="bi bi-people"></i> <?= $agency['students_certified'] ?? 0 ?> students</div>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <?php if (hasPermission('certifications.edit')): ?>
                        <a href="/certifications/agencies/<?= $agency['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($agency['website'])): ?>
                        <a href="<?= htmlspecialchars($agency['website']) ?>" target="_blank" class="btn btn-outline-primary" title="Visit website">
                            <i class="bi bi-link-45deg"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
?>
