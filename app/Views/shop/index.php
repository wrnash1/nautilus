<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Nautilus Dive Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/shop">
                <i class="bi bi-water"></i> Nautilus Dive Shop
            </a>
            <div class="d-flex">
                <a href="/shop/cart" class="btn btn-outline-light">
                    <i class="bi bi-cart"></i> Cart 
                    <?php if ($cartTotals['item_count'] > 0): ?>
                        <span class="badge bg-danger"><?= $cartTotals['item_count'] ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h1 class="mb-4">Dive Equipment & Gear</h1>
        
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($products as $product): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="text-muted small"><?= htmlspecialchars($product['sku']) ?></p>
                        <p class="card-text text-truncate"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                        <p class="h4 text-primary">$<?= number_format($product['retail_price'], 2) ?></p>
                        <?php if ($product['track_inventory'] && $product['stock_quantity'] > 0): ?>
                            <p class="text-success small">
                                <i class="bi bi-check-circle"></i> In Stock (<?= $product['stock_quantity'] ?>)
                            </p>
                        <?php elseif ($product['track_inventory']): ?>
                            <p class="text-danger small">
                                <i class="bi bi-x-circle"></i> Out of Stock
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-grid gap-2">
                            <a href="/shop/product/<?= $product['id'] ?>" class="btn btn-outline-primary btn-sm">
                                View Details
                            </a>
                            <?php if (!$product['track_inventory'] || $product['stock_quantity'] > 0): ?>
                            <form method="POST" action="/shop/cart/add">
                                <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
