<?php
$pageTitle = 'Products';
$activeMenu = 'products';
$user = currentUser();

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam"></i> Products</h2>
    <div>
        <?php if (hasPermission('products.create')): ?>
        <a href="/store/products/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Product
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-10">
                <input type="text" id="productSearch" class="form-control" 
                       placeholder="Search by name, SKU, or description..." autocomplete="off">
                <div id="searchResults" class="position-absolute bg-white border rounded shadow-sm" style="display: none; z-index: 1000; max-height: 400px; overflow-y: auto;"></div>
            </div>
            <div class="col-md-2">
                <select id="categoryFilter" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <?php if (empty($products)): ?>
        <p class="text-muted text-center py-4">No products found.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 80px;">Image</th>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Cost</th>
                        <th>Retail</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php 
                            $imageUrl = $product['image_url'] ?? 'https://placehold.co/60x60/6c757d/ffffff?text=No+Image';
                            ?>
                            <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 60px; height: 60px; object-fit: cover;" class="rounded">
                        </td>
                        <td><code><?= htmlspecialchars($product['sku']) ?></code></td>
                        <td>
                            <a href="/store/products/<?= $product['id'] ?>">
                                <?= htmlspecialchars($product['name']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($product['category_name'] ?? '-') ?></td>
                        <td>
                            <?php if ($product['track_inventory']): ?>
                                <?php if ($product['stock_quantity'] <= $product['low_stock_threshold']): ?>
                                    <span class="badge bg-danger"><?= $product['stock_quantity'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= $product['stock_quantity'] ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><?= formatCurrency($product['cost_price']) ?></td>
                        <td><?= formatCurrency($product['retail_price']) ?></td>
                        <td>
                            <span class="badge bg-<?= $product['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/store/products/<?= $product['id'] ?>" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (hasPermission('products.edit')): ?>
                                <a href="/store/products/<?= $product['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (hasPermission('products.delete')): ?>
                                <form method="POST" action="/store/products/<?= $product['id'] ?>/delete" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this product?')">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="/store/products?page=<?= $i ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>">
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

$additionalJs = <<<'JS'
<script>
let searchTimeout;
const searchInput = document.getElementById('productSearch');
const searchResults = document.getElementById('searchResults');
const categoryFilter = document.getElementById('categoryFilter');

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    
    if (query.length < 2) {
        searchResults.style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch(`/store/products/search?q=${encodeURIComponent(query)}`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(products => {
            if (products.length === 0) {
                searchResults.innerHTML = '<div class="p-3 text-muted">No products found</div>';
                searchResults.style.display = 'block';
                return;
            }
            
            let html = '<div class="list-group list-group-flush">';
            products.forEach(product => {
                const stockBadge = product.track_inventory ? 
                    `<span class="badge ${product.stock_quantity <= product.low_stock_threshold ? 'bg-danger' : 'bg-success'}">${product.stock_quantity}</span>` : 
                    '<span class="text-muted">N/A</span>';
                
                html += `
                    <a href="/store/products/${product.id}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${escapeHtml(product.name)}</strong>
                                <br><small class="text-muted">SKU: ${escapeHtml(product.sku)}</small>
                            </div>
                            <div class="text-end">
                                <div>${formatCurrency(product.retail_price)}</div>
                                <small>Stock: ${stockBadge}</small>
                            </div>
                        </div>
                    </a>
                `;
            });
            html += '</div>';
            
            searchResults.innerHTML = html;
            searchResults.style.display = 'block';
            searchResults.style.width = searchInput.offsetWidth + 'px';
        })
        .catch(error => {
            searchResults.innerHTML = '<div class="p-3 text-danger">Error searching products</div>';
            searchResults.style.display = 'block';
        });
    }, 300);
});

document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});

searchInput.addEventListener('focus', function() {
    if (searchResults.innerHTML && this.value.length >= 2) {
        searchResults.style.display = 'block';
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

categoryFilter.addEventListener('change', function() {
    window.location.href = '/store/products?category=' + this.value;
});
</script>
JS;

require __DIR__ . '/../layouts/admin.php';
?>
