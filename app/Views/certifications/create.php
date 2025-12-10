<?php
$pageTitle = 'Create Certification';
$activeMenu = 'certifications';
$user = currentUser();

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-award"></i> Create Certification</h2>
    <a href="/certifications" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Certifications
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/certifications" id="certificationForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="agency_id" class="form-label">Certification Agency <span class="text-danger">*</span></label>
                    <select name="agency_id" id="agency_id" class="form-select" required>
                        <option value="">Select Agency</option>
                        <?php foreach ($agencies as $agency): ?>
                        <option value="<?= $agency['id'] ?>">
                            <?= htmlspecialchars($agency['name']) ?> (<?= htmlspecialchars($agency['code']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">
                        Don't see your agency? <a href="/certifications/agencies/create" target="_blank">Add it here</a>
                    </small>
                </div>

                <div class="col-md-6">
                    <label for="code" class="form-label">Certification Code</label>
                    <input type="text" class="form-control" id="code" name="code"
                           placeholder="e.g., OW, AOW, RD" maxlength="20">
                    <small class="form-text text-muted">Short code/abbreviation</small>
                </div>

                <div class="col-md-12">
                    <label for="name" class="form-label">Certification Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name"
                           placeholder="e.g., Open Water Diver" required maxlength="255">
                </div>

                <div class="col-md-4">
                    <label for="level" class="form-label">Level</label>
                    <select name="level" id="level" class="form-select">
                        <option value="">Select Level</option>
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                        <option value="Professional">Professional</option>
                        <option value="Instructor">Instructor</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="minimum_age" class="form-label">Minimum Age</label>
                    <input type="number" class="form-control" id="minimum_age" name="minimum_age"
                           min="0" max="99" value="0">
                </div>

                <div class="col-md-4">
                    <label for="course_duration_days" class="form-label">Course Duration (days)</label>
                    <input type="number" class="form-control" id="course_duration_days" name="course_duration_days"
                           min="0" max="365" value="0">
                </div>

                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"
                              placeholder="Brief description of this certification"></textarea>
                </div>

                <div class="col-md-12">
                    <label for="prerequisites" class="form-label">Prerequisites</label>
                    <textarea class="form-control" id="prerequisites" name="prerequisites" rows="2"
                              placeholder="List any prerequisite certifications or requirements"></textarea>
                </div>

                <div class="col-md-4">
                    <label for="max_depth_meters" class="form-label">Max Depth (meters)</label>
                    <input type="number" class="form-control" id="max_depth_meters" name="max_depth_meters"
                           min="0" max="200" step="0.1" value="0">
                </div>

                <div class="col-md-4">
                    <label for="price" class="form-label">Course Price ($)</label>
                    <input type="number" class="form-control" id="price" name="price"
                           min="0" step="0.01" value="0.00">
                </div>

                <div class="col-md-4">
                    <label for="certification_fee" class="form-label">Certification Fee ($)</label>
                    <input type="number" class="form-control" id="certification_fee" name="certification_fee"
                           min="0" step="0.01" value="0.00">
                    <small class="form-text text-muted">Agency certification card fee</small>
                </div>

                <div class="col-md-4">
                    <label for="materials_cost" class="form-label">Materials Cost ($)</label>
                    <input type="number" class="form-control" id="materials_cost" name="materials_cost"
                           min="0" step="0.01" value="0.00">
                </div>

                <div class="col-md-4">
                    <label for="expiration_months" class="form-label">Expiration (months)</label>
                    <input type="number" class="form-control" id="expiration_months" name="expiration_months"
                           min="0" max="999" placeholder="Leave blank if no expiration">
                    <small class="form-text text-muted">e.g., 24 for 2 years</small>
                </div>

                <div class="col-md-4">
                    <div class="pt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="requires_renewal" name="requires_renewal">
                            <label class="form-check-label" for="requires_renewal">
                                Requires Renewal
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">
                            Active (available for enrollment)
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Create Certification
                </button>
                <a href="/certifications" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
