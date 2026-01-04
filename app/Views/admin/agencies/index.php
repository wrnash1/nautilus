<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-award"></i> Diving Agencies & Certifications</h2>
    <a href="/store/admin/settings" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Settings
    </a>
</div>

<ul class="nav nav-tabs mb-4" id="agencyTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="agencies-tab" data-bs-toggle="tab" data-bs-target="#agencies" type="button">
            <i class="bi bi-building"></i> Agencies
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="certifications-tab" data-bs-toggle="tab" data-bs-target="#certifications"
            type="button">
            <i class="bi bi-card-checklist"></i> Certification Types
        </button>
    </li>
</ul>

<div class="tab-content" id="agencyTabsContent">
    <!-- Agencies Tab -->
    <div class="tab-pane fade show active" id="agencies" role="tabpanel">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Diving Agency Logos</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAgencyModal">
                            <i class="bi bi-plus"></i> Add Agency
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <?php foreach ($agencies as $agency): ?>
                                <div class="col-md-4 col-lg-3">
                                    <div class="card h-100 text-center">
                                        <div class="card-body">
                                            <div class="agency-logo-container mb-3"
                                                style="height: 80px; display: flex; align-items: center; justify-content: center;">
                                                <?php if (!empty($agency['logo_url'])): ?>
                                                    <img src="<?= htmlspecialchars($agency['logo_url']) ?>"
                                                        alt="<?= htmlspecialchars($agency['name']) ?>"
                                                        style="max-height: 80px; max-width: 100%;">
                                                <?php elseif (!empty($agency['logo_svg'])): ?>
                                                    <?= $agency['logo_svg'] ?>
                                                <?php else: ?>
                                                    <div class="bg-light rounded p-3"
                                                        style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                                        <span class="fw-bold text-muted">
                                                            <?= htmlspecialchars($agency['code']) ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <h6 class="fw-bold">
                                                <?= htmlspecialchars($agency['code']) ?>
                                            </h6>
                                            <small class="text-muted d-block mb-2">
                                                <?= htmlspecialchars($agency['name']) ?>
                                            </small>
                                            <div class="mb-2">
                                                <?php if ($agency['is_recreational']): ?>
                                                    <span class="badge bg-primary">Recreational</span>
                                                <?php endif; ?>
                                                <?php if ($agency['is_technical']): ?>
                                                    <span class="badge bg-purple" style="background: #8b5cf6;">Technical</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary"
                                                    onclick="uploadLogo('<?= $agency['code'] ?>')" title="Upload Logo">
                                                    <i class="bi bi-upload"></i>
                                                </button>
                                                <a href="<?= htmlspecialchars($agency['website'] ?? '#') ?>" target="_blank"
                                                    class="btn btn-outline-secondary" title="Visit Website">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Certifications Tab -->
    <div class="tab-pane fade" id="certifications" role="tabpanel">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="certSearch" placeholder="Search certifications...">
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select" id="certCategoryFilter">
                    <option value="">All Categories</option>
                    <option value="recreational">Recreational</option>
                    <option value="advanced">Advanced</option>
                    <option value="professional">Professional</option>
                    <option value="technical">Technical</option>
                    <option value="specialty">Specialty</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#addCertModal">
                    <i class="bi bi-plus"></i> Add
                </button>
            </div>
        </div>

        <?php
        $categories = [
            'recreational' => 'Recreational',
            'advanced' => 'Advanced',
            'professional' => 'Professional',
            'technical' => 'Technical',
            'specialty' => 'Specialty'
        ];
        foreach ($categories as $catKey => $catName):
            if (!isset($grouped[$catKey]))
                continue;
            ?>
            <div class="card mb-4 cert-category-card" data-category="<?= $catKey ?>">
                <div class="card-header"
                    style="background: <?= $catKey === 'technical' ? '#8b5cf6' : ($catKey === 'professional' ? '#10b981' : '#3b82f6') ?>; color: white;">
                    <h5 class="mb-0">
                        <?= $catName ?> Certifications
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Level</th>
                                <th>Min Age</th>
                                <th>Min Dives</th>
                                <th>Agencies</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grouped[$catKey] as $cert): ?>
                                <tr class="cert-row" data-name="<?= strtolower($cert['name']) ?>">
                                    <td><code><?= htmlspecialchars($cert['code']) ?></code></td>
                                    <td>
                                        <?= htmlspecialchars($cert['name']) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= $cert['level'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $cert['min_age'] ?>+
                                    </td>
                                    <td>
                                        <?= $cert['min_dives'] ?>
                                    </td>
                                    <td>
                                        <?php
                                        $agencies = explode(',', $cert['typical_agencies'] ?? '');
                                        foreach (array_slice($agencies, 0, 3) as $a): ?>
                                            <span class="badge bg-light text-dark">
                                                <?= trim($a) ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (count($agencies) > 3): ?>
                                            <span class="badge bg-light text-muted">+
                                                <?= count($agencies) - 3 ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Agency Modal -->
<div class="modal fade" id="addAgencyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-building"></i> Add Diving Agency</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/store/admin/agencies">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="mb-3">
                        <label class="form-label">Agency Code *</label>
                        <input type="text" class="form-control" name="code" maxlength="20" required
                            placeholder="e.g., PADI">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="name" required
                            placeholder="e.g., Professional Association of Diving Instructors">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Website</label>
                        <input type="url" class="form-control" name="website" placeholder="https://...">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_recreational"
                                    id="isRecreational" checked>
                                <label class="form-check-label" for="isRecreational">Recreational</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_technical" id="isTechnical">
                                <label class="form-check-label" for="isTechnical">Technical</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Agency</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Certification Modal -->
<div class="modal fade" id="addCertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-card-checklist"></i> Add Certification Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/store/admin/agencies/certifications">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Code *</label>
                                <input type="text" class="form-control" name="code" maxlength="50" required
                                    placeholder="e.g., OW">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category *</label>
                                <select class="form-select" name="category" required>
                                    <option value="recreational">Recreational</option>
                                    <option value="advanced">Advanced</option>
                                    <option value="professional">Professional</option>
                                    <option value="technical">Technical</option>
                                    <option value="specialty">Specialty</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="name" required
                            placeholder="e.g., Open Water Diver">
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Level</label>
                                <input type="number" class="form-control" name="level" value="1" min="0" max="10">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Min Age</label>
                                <input type="number" class="form-control" name="min_age" value="10" min="8">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Min Dives</label>
                                <input type="number" class="form-control" name="min_dives" value="0" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Offering Agencies</label>
                        <input type="text" class="form-control" name="typical_agencies"
                            placeholder="e.g., PADI,SSI,NAUI">
                        <small class="text-muted">Comma-separated agency codes</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Certification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Logo Upload Modal -->
<div class="modal fade" id="logoUploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upload"></i> Upload Agency Logo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="logoUploadForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="mb-3">
                        <label class="form-label">Logo File (PNG, JPG, or SVG)</label>
                        <input type="file" class="form-control" name="logo" accept=".png,.jpg,.jpeg,.svg">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Or paste SVG code</label>
                        <textarea class="form-control" name="logo_svg" rows="4" placeholder="<svg>...</svg>"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Search filter
    document.getElementById('certSearch')?.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.cert-row').forEach(row => {
            const name = row.dataset.name;
            row.style.display = name.includes(query) ? '' : 'none';
        });
    });

    // Category filter
    document.getElementById('certCategoryFilter')?.addEventListener('change', function () {
        const cat = this.value;
        document.querySelectorAll('.cert-category-card').forEach(card => {
            if (!cat || card.dataset.category === cat) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });

    // Logo upload
    function uploadLogo(agencyCode) {
        const form = document.getElementById('logoUploadForm');
        form.action = '/store/admin/agencies/' + agencyCode + '/logo';
        new bootstrap.Modal(document.getElementById('logoUploadModal')).show();
    }
</script>