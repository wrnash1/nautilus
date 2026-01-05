<?php
$pageTitle = 'AI Product Image Manager';
$activeMenu = 'admin';

ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-images text-primary"></i> AI Product Image Manager</h2>
            <p class="text-muted mb-0">Automatically find and add images for products missing photos</p>
        </div>
        <button class="btn btn-primary" id="scanProductsBtn">
            <i class="bi bi-search"></i> Scan Missing Images
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0" id="productsWithImages">
                                <?= $productsWithImages ?? 0 ?>
                            </h3>
                            <small>With Images</small>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0" id="productsMissingImages">
                                <?= $missingCount ?? 0 ?>
                            </h3>
                            <small>Missing Images</small>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">
                                <?= $sharedLibraryCount ?? 0 ?>
                            </h3>
                            <small>Shared Library</small>
                        </div>
                        <i class="bi bi-cloud fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">
                                <?= $totalProducts ?? 0 ?>
                            </h3>
                            <small>Total Products</small>
                        </div>
                        <i class="bi bi-box-seam fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Actions -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-gradient"
            style="background: linear-gradient(135deg, #ec4899, #be185d); color: white;">
            <h5 class="mb-0"><i class="bi bi-robot"></i> AI Actions</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <button class="btn btn-outline-primary w-100 py-3" id="autoFindBtn">
                        <i class="bi bi-search fs-4 d-block mb-2"></i>
                        Auto-Find Images
                        <small class="d-block text-muted">Search internet for product images</small>
                    </button>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-outline-success w-100 py-3" id="sharedLibraryBtn">
                        <i class="bi bi-cloud-download fs-4 d-block mb-2"></i>
                        Use Shared Library
                        <small class="d-block text-muted">Pull from Nautilus community images</small>
                    </button>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-outline-info w-100 py-3" id="generateAiBtn">
                        <i class="bi bi-magic fs-4 d-block mb-2"></i>
                        Generate AI Images
                        <small class="d-block text-muted">Create images with AI (DALL-E)</small>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Missing Images -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-image"></i> Products Missing Images</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary" id="selectAllBtn">Select All</button>
                <button class="btn btn-sm btn-success ms-2" id="batchProcessBtn" disabled>
                    <i class="bi bi-play-fill"></i> Process Selected
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40"><input type="checkbox" id="selectAllCheckbox"></th>
                            <th width="80">Preview</th>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Suggested Search</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        <?php if (!empty($missingProducts)): ?>
                            <?php foreach ($missingProducts as $product): ?>
                                <tr data-product-id="<?= $product['id'] ?>">
                                    <td><input type="checkbox" class="product-checkbox" value="<?= $product['id'] ?>"></td>
                                    <td>
                                        <div class="product-preview bg-light rounded d-flex align-items-center justify-content-center"
                                            style="width: 60px; height: 60px;">
                                            <i class="bi bi-image text-secondary fs-4"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($product['name']) ?>
                                        </strong>
                                        <?php if (!empty($product['brand'])): ?>
                                            <br><small class="text-muted">
                                                <?= htmlspecialchars($product['brand']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= htmlspecialchars($product['sku'] ?? 'N/A') ?></code></td>
                                    <td>
                                        <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm search-query"
                                            value="<?= htmlspecialchars($product['name'] . ' ' . ($product['brand'] ?? '')) ?>"
                                            placeholder="Search query...">
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary find-image-btn" data-id="<?= $product['id'] ?>">
                                            <i class="bi bi-search"></i> Find
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary upload-btn"
                                            data-id="<?= $product['id'] ?>">
                                            <i class="bi bi-upload"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle fs-1 text-success"></i>
                                    <p class="mb-0 mt-2">All products have images!</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upload"></i> Upload Product Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <input type="hidden" id="uploadProductId" name="product_id">
                    <input type="hidden" name="csrf_token"
                        value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">

                    <div class="mb-3">
                        <label class="form-label">Select Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Or paste image URL</label>
                        <input type="url" class="form-control" name="image_url" placeholder="https://...">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitUploadBtn">
                    <i class="bi bi-upload"></i> Upload
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Processing Modal -->
<div class="modal fade" id="processingModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div class="spinner-border text-primary mb-3" role="status" style="width: 4rem; height: 4rem;">
                <span class="visually-hidden">Processing...</span>
            </div>
            <h4>Finding Images...</h4>
            <p class="text-muted" id="processingStatus">Searching for product images...</p>
            <div class="progress mt-3">
                <div class="progress-bar progress-bar-striped progress-bar-animated" id="processingProgress"
                    style="width: 0%"></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const batchProcessBtn = document.getElementById('batchProcessBtn');

        // Select all
        selectAllCheckbox?.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBatchButton();
        });

        selectAllBtn?.addEventListener('click', function () {
            selectAllCheckbox.checked = true;
            checkboxes.forEach(cb => cb.checked = true);
            updateBatchButton();
        });

        // Individual checkbox
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBatchButton);
        });

        function updateBatchButton() {
            const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
            batchProcessBtn.disabled = checkedCount === 0;
            batchProcessBtn.textContent = checkedCount > 0 ?
                `Process ${checkedCount} Selected` : 'Process Selected';
        }

        // Find image button
        document.querySelectorAll('.find-image-btn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const productId = this.dataset.id;
                const row = this.closest('tr');
                const query = row.querySelector('.search-query').value;

                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                try {
                    const response = await fetch('/store/admin/products/find-image', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ product_id: productId, query: query })
                    });
                    const data = await response.json();

                    if (data.success && data.image_url) {
                        row.querySelector('.product-preview').innerHTML =
                            `<img src="${data.image_url}" class="img-fluid rounded" style="max-height: 60px;">`;
                        this.innerHTML = '<i class="bi bi-check"></i>';
                        this.classList.replace('btn-primary', 'btn-success');
                    } else {
                        throw new Error(data.error || 'Image not found');
                    }
                } catch (e) {
                    this.innerHTML = '<i class="bi bi-x"></i>';
                    this.classList.replace('btn-primary', 'btn-danger');
                    setTimeout(() => {
                        this.innerHTML = '<i class="bi bi-search"></i> Find';
                        this.classList.replace('btn-danger', 'btn-primary');
                        this.disabled = false;
                    }, 2000);
                }
            });
        });

        // Upload button
        document.querySelectorAll('.upload-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('uploadProductId').value = this.dataset.id;
                new bootstrap.Modal(document.getElementById('uploadModal')).show();
            });
        });

        // Batch process
        batchProcessBtn?.addEventListener('click', async function () {
            const selected = Array.from(document.querySelectorAll('.product-checkbox:checked'))
                .map(cb => cb.value);

            if (selected.length === 0) return;

            const modal = new bootstrap.Modal(document.getElementById('processingModal'));
            modal.show();

            const progressBar = document.getElementById('processingProgress');
            const statusText = document.getElementById('processingStatus');

            let processed = 0;
            for (const productId of selected) {
                processed++;
                const progress = (processed / selected.length) * 100;
                progressBar.style.width = progress + '%';
                statusText.textContent = `Processing ${processed} of ${selected.length}...`;

                try {
                    await fetch('/store/admin/products/find-image', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ product_id: productId })
                    });
                } catch (e) {
                    console.error('Failed for product:', productId);
                }

                await new Promise(resolve => setTimeout(resolve, 500)); // Rate limit
            }

            statusText.textContent = 'Complete! Refreshing...';
            setTimeout(() => location.reload(), 1500);
        });
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
?>