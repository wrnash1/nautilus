<?php $this->layout('layouts/admin', ['title' => $title ?? 'Vendor Catalog Import']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/inventory">Inventory</a></li>
                    <li class="breadcrumb-item active">Vendor Import</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-cloud-upload me-2"></i>Vendor Catalog Import</h2>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Import Catalog File</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/store/vendor-catalog/import" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="mb-4">
                            <label class="form-label">Vendor</label>
                            <select name="vendor_id" class="form-select" required>
                                <option value="">-- Select Vendor --</option>
                                <?php foreach ($vendors ?? [] as $vendor): ?>
                                    <option value="<?= $vendor['id'] ?>"><?= htmlspecialchars($vendor['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Catalog File</label>
                            <input type="file" name="catalog_file" class="form-control" accept=".csv,.xlsx,.xls" required>
                            <div class="form-text">Accepted formats: CSV, Excel (.xlsx, .xls)</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Import Type</label>
                            <select name="import_type" class="form-select">
                                <option value="update">Update existing & add new</option>
                                <option value="add_only">Add new products only</option>
                                <option value="update_only">Update existing only</option>
                            </select>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="preview_only" id="previewOnly" checked>
                            <label class="form-check-label" for="previewOnly">
                                Preview changes before importing
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i>Upload & Process
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>Import Instructions</h5>
                    <ol class="small mb-0">
                        <li>Select the vendor for this catalog</li>
                        <li>Upload a CSV or Excel file with product data</li>
                        <li>Review the column mapping on the next screen</li>
                        <li>Preview changes before final import</li>
                    </ol>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Recent Imports</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($recentImports ?? [])): ?>
                        <p class="text-muted small mb-0">No recent imports</p>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($recentImports as $import): ?>
                                <li class="mb-2">
                                    <small>
                                        <?= htmlspecialchars($import['vendor_name']) ?><br>
                                        <span class="text-muted"><?= date('M j, Y', strtotime($import['created_at'])) ?></span>
                                        - <?= $import['products_imported'] ?> products
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
