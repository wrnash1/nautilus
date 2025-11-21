<?php
$pageTitle = 'Shop';
ob_start();
?>

<!-- Shop Header -->
<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-12">
            <h1>Shop Dive Gear</h1>
            <p class="text-muted">Browse our selection of quality diving equipment</p>
        </div>
    </div>
</div>

<!-- Filters & Products -->
<div class="container mb-5">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Categories</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="/shop" class="list-group-item list-group-item-action <?= !isset($_GET['category']) ? 'active' : '' ?>">
                        All Products
                        <span class="badge bg-primary float-end"><?= $totalProducts ?></span>
                    </a>
                    <?php foreach ($categories as $category): ?>
                    <a href="/shop?category=<?= $category['id'] ?>" class="list-group-item list-group-item-action <?= isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($category['name']) ?>
                        <span class="badge bg-secondary float-end"><?= $category['product_count'] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-md-9">
            <?php if (empty($products)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No products found. Check back soon!
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if ($product['image_path']): ?>
                        <img src="<?= htmlspecialchars($product['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="bi bi-box-seam" style="font-size: 3rem; color: #ccc;"></i>
                        </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text text-muted small"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></p>
                            <?php if ($product['description']): ?>
                            <p class="card-text"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                            <?php endif; ?>
                            <div class="mt-auto">
                                <p class="card-text mb-2">
                                    <strong class="text-primary fs-5"><?= formatCurrency($product['price']) ?></strong>
                                </p>
                                <?php if ($product['stock_quantity'] > 0): ?>
                                <a href="/product/<?= $product['id'] ?>" class="btn btn-primary w-100">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </a>
                                <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="bi bi-x-circle"></i> Out of Stock
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Product pagination">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="/shop?page=<?= $i ?><?= isset($_GET['category']) ? '&category=' . $_GET['category'] : '' ?>">
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
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/public.php';
?>
