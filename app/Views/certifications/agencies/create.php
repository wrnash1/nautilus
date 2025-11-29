<?php
$pageTitle = 'Add Certification Agency';
$activeMenu = 'certifications';
$user = currentUser();

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-building"></i> Add Certification Agency</h2>
    <a href="/certifications/agencies" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Agencies
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/certifications/agencies" id="agencyForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="row g-3">
                <div class="col-md-8">
                    <label for="name" class="form-label">Agency Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name"
                           placeholder="e.g., Professional Association of Diving Instructors"
                           required maxlength="255">
                </div>

                <div class="col-md-4">
                    <label for="code" class="form-label">Agency Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="code" name="code"
                           placeholder="e.g., PADI" required maxlength="20">
                    <small class="form-text text-muted">Short abbreviation</small>
                </div>

                <div class="col-md-12">
                    <label for="website" class="form-label">Website</label>
                    <input type="url" class="form-control" id="website" name="website"
                           placeholder="https://www.example.com" maxlength="255">
                </div>

                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"
                              placeholder="Brief description of the certification agency"></textarea>
                </div>

                <div class="col-md-12">
                    <label for="logo_url" class="form-label">Logo URL</label>
                    <input type="url" class="form-control" id="logo_url" name="logo_url"
                           placeholder="https://example.com/logo.png" maxlength="500">
                    <small class="form-text text-muted">Direct link to agency logo image</small>
                </div>

                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">
                            Active (available for certifications)
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Add Agency
                </button>
                <a href="/certifications/agencies" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- Common Agencies Reference -->
<div class="card mt-4 bg-light">
    <div class="card-body">
        <h6 class="card-title"><i class="bi bi-info-circle"></i> Common Certification Agencies</h6>
        <div class="row">
            <div class="col-md-6">
                <ul class="list-unstyled small mb-0">
                    <li><strong>PADI</strong> - Professional Association of Diving Instructors</li>
                    <li><strong>SSI</strong> - Scuba Schools International</li>
                    <li><strong>NAUI</strong> - National Association of Underwater Instructors</li>
                    <li><strong>SDI</strong> - Scuba Diving International</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled small mb-0">
                    <li><strong>RAID</strong> - Rebreather Association of International Divers</li>
                    <li><strong>BSAC</strong> - British Sub-Aqua Club</li>
                    <li><strong>CMAS</strong> - Confédération Mondiale des Activités Subaquatiques</li>
                    <li><strong>GUE</strong> - Global Underwater Explorers</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
