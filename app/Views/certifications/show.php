<?php
$pageTitle = htmlspecialchars($certification['name']);
$activeMenu = 'certifications';
$user = currentUser();

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-award"></i> <?= htmlspecialchars($certification['name']) ?></h2>
    <div>
        <a href="/certifications" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
        <?php if (hasPermission('certifications.edit')): ?>
        <a href="/certifications/<?= $certification['id'] ?>/edit" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Certification Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Certification Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Agency</label>
                        <div class="d-flex align-items-center">
                            <?php if (!empty($certification['agency_logo'])): ?>
                            <img src="<?= htmlspecialchars($certification['agency_logo']) ?>"
                                 alt="<?= htmlspecialchars($certification['agency_name']) ?>"
                                 style="max-height: 40px; margin-right: 10px;">
                            <?php endif; ?>
                            <div>
                                <strong><?= htmlspecialchars($certification['agency_name'] ?? 'N/A') ?></strong>
                                <?php if (!empty($certification['agency_website'])): ?>
                                <br><a href="<?= htmlspecialchars($certification['agency_website']) ?>" target="_blank" class="small">
                                    <i class="bi bi-link-45deg"></i> Website
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Certification Code</label>
                        <div><strong><?= htmlspecialchars($certification['code'] ?? 'N/A') ?></strong></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Level</label>
                        <div>
                            <?php if (!empty($certification['level'])): ?>
                            <span class="badge bg-secondary"><?= htmlspecialchars($certification['level']) ?></span>
                            <?php else: ?>
                            <span class="text-muted">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Status</label>
                        <div>
                            <?php if ($certification['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($certification['description'])): ?>
                    <div class="col-md-12 mb-3">
                        <label class="text-muted small">Description</label>
                        <div><?= nl2br(htmlspecialchars($certification['description'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Course Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar3"></i> Course Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Minimum Age</label>
                        <div><strong><?= $certification['minimum_age'] ?? 0 ?></strong> years</div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Course Duration</label>
                        <div><strong><?= $certification['course_duration_days'] ?? 0 ?></strong> days</div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Max Depth</label>
                        <div><strong><?= $certification['max_depth_meters'] ?? 0 ?></strong> meters</div>
                    </div>

                    <?php if (!empty($certification['prerequisites'])): ?>
                    <div class="col-md-12 mb-3">
                        <label class="text-muted small">Prerequisites</label>
                        <div><?= nl2br(htmlspecialchars($certification['prerequisites'])) ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($prerequisites)): ?>
                    <div class="col-md-12">
                        <label class="text-muted small">Required Certifications</label>
                        <div>
                            <?php foreach ($prerequisites as $prereq): ?>
                            <a href="/certifications/<?= $prereq['id'] ?>" class="badge bg-info text-decoration-none me-1">
                                <?= htmlspecialchars($prereq['name']) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Students with this Certification -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Certified Students (<?= count($students) ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                <p class="text-muted text-center py-3">No students certified yet.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Certification #</th>
                                <th>Issue Date</th>
                                <th>Expiration</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td>
                                    <a href="/customers/<?= $student['customer_id'] ?>">
                                        <?= htmlspecialchars($student['student_name']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($student['certification_number'] ?? 'N/A') ?></td>
                                <td><?= $student['issue_date'] ? date('M j, Y', strtotime($student['issue_date'])) : 'N/A' ?></td>
                                <td>
                                    <?php if ($student['expiration_date']): ?>
                                        <?= date('M j, Y', strtotime($student['expiration_date'])) ?>
                                        <?php if (strtotime($student['expiration_date']) < time()): ?>
                                        <span class="badge bg-danger ms-1">Expired</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No expiration</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($student['is_verified']): ?>
                                    <span class="badge bg-success">Verified</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Pricing Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-currency-dollar"></i> Pricing</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Course Price</label>
                    <div class="h4 mb-0"><?= $certification['price'] > 0 ? '$' . number_format($certification['price'], 2) : 'Not set' ?></div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Certification Fee</label>
                    <div><?= $certification['certification_fee'] > 0 ? '$' . number_format($certification['certification_fee'], 2) : 'N/A' ?></div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Materials Cost</label>
                    <div><?= $certification['materials_cost'] > 0 ? '$' . number_format($certification['materials_cost'], 2) : 'N/A' ?></div>
                </div>

                <?php
                $totalCost = ($certification['price'] ?? 0) + ($certification['certification_fee'] ?? 0) + ($certification['materials_cost'] ?? 0);
                if ($totalCost > 0):
                ?>
                <hr>
                <div>
                    <label class="text-muted small">Total Cost</label>
                    <div class="h5 mb-0 text-primary">$<?= number_format($totalCost, 2) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Certification Validity -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Validity</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Expiration Period</label>
                    <div>
                        <?php if ($certification['expiration_months']): ?>
                        <strong><?= $certification['expiration_months'] ?></strong> months
                        <?php else: ?>
                        <span class="text-muted">No expiration</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label class="text-muted small">Requires Renewal</label>
                    <div>
                        <?php if ($certification['requires_renewal']): ?>
                        <span class="badge bg-warning">Yes</span>
                        <?php else: ?>
                        <span class="badge bg-success">No</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <?php if (hasPermission('certifications.delete')): ?>
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Danger Zone</h5>
            </div>
            <div class="card-body">
                <p class="small text-muted">Delete this certification permanently. This action cannot be undone.</p>
                <form method="POST" action="/certifications/<?= $certification['id'] ?>/delete"
                      onsubmit="return confirm('Are you sure you want to delete this certification? This cannot be undone.')">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> Delete Certification
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
