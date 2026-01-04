<?php
$pageTitle = 'Certifications';
$activeMenu = 'certifications';
$user = currentUser();

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-award"></i> Certifications</h2>
    <div>
        <a href="/certifications/agencies" class="btn btn-outline-secondary">
            <i class="bi bi-building"></i> Manage Agencies
        </a>

        <?php if (hasPermission('certifications.create')): ?>
        <a href="/certifications/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Certification
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <form method="GET" action="/certifications">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control"
                               placeholder="Search certifications by name, code, or agency..."
                               value="<?= htmlspecialchars($search ?? '') ?>" autocomplete="off">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="bi bi-search"></i> Search
                        </button>
                        <?php if (!empty($search)): ?>
                        <a href="/certifications" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($certifications)): ?>
        <p class="text-muted text-center py-4">No certifications found. <a href="/certifications/create">Add your first certification</a></p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Agency</th>
                        <th>Level</th>
                        <th>Students</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certifications as $cert): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($cert['code'] ?? '-') ?></strong>
                        </td>
                        <td>
                            <a href="/certifications/<?= $cert['id'] ?>">
                                <?= htmlspecialchars($cert['name']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <?= htmlspecialchars($cert['agency_code'] ?? $cert['agency_name'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($cert['level'])): ?>
                            <span class="badge bg-secondary"><?= htmlspecialchars($cert['level']) ?></span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-success"><?= $cert['students_certified'] ?? 0 ?></span>
                        </td>
                        <td>
                            <?php if ($cert['price'] > 0): ?>
                                $<?= number_format($cert['price'], 2) ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($cert['course_duration_days'] > 0): ?>
                                <?= $cert['course_duration_days'] ?> days
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($cert['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/certifications/<?= $cert['id'] ?>" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (hasPermission('certifications.edit')): ?>
                                <a href="/certifications/<?= $cert['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav aria-label="Certifications pagination">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="/certifications?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
