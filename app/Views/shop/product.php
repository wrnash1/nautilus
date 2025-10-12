<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Nautilus Dive Shop</title>
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
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/shop">Shop</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-6">
                <?php 
                $imageUrl = $product['image_url'] ?? 'https://placehold.co/600x450/6c757d/ffffff?text=No+Image';
                $imageAlt = $product['image_alt'] ?? htmlspecialchars($product['name']);
                ?>
                <img src="<?= htmlspecialchars($imageUrl) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($imageAlt) ?>">
            </div>
            
            <div class="col-md-6">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <p class="text-muted">SKU: <?= htmlspecialchars($product['sku']) ?></p>
                <p class="text-muted">Category: <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></p>
                
                <hr>
                
                <h2 class="text-primary">$<?= number_format($product['retail_price'], 2) ?></h2>
                
                <?php if ($product['track_inventory']): ?>
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <p class="text-success">
                            <i class="bi bi-check-circle"></i> In Stock (<?= $product['stock_quantity'] ?> available)
                        </p>
                    <?php else: ?>
                        <p class="text-danger">
                            <i class="bi bi-x-circle"></i> Out of Stock
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
                
                <hr>
                
                <h5>Description</h5>
                <p><?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?></p>
                
                <hr>
                
                <?php if (!$product['track_inventory'] || $product['stock_quantity'] > 0): ?>
                <form method="POST" action="/shop/cart/add">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="row g-3 align-items-end">
                        <div class="col-auto">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?= $product['track_inventory'] ? $product['stock_quantity'] : 99 ?>" style="width: 100px;">
                        </div>
                        <div class="col">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </form>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg" disabled>
                        <i class="bi bi-x-circle"></i> Out of Stock
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
