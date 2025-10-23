<div class="container">
    <?php if ($section['section_title']): ?>
    <div class="text-center mb-5">
        <h2 class="display-5 fw-bold" style="color: var(--heading-color); font-family: var(--font-heading);">
            <?= htmlspecialchars($section['section_title']) ?>
        </h2>
        <?php if ($section['section_subtitle']): ?>
        <p class="lead text-muted"><?= htmlspecialchars($section['section_subtitle']) ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($sectionData)): ?>
    <div class="row g-4">
        <?php
        $productsPerRow = $theme['products_per_row'] ?? 4;
        $colClass = "col-md-6 col-lg-" . (12 / $productsPerRow);
        $cardStyle = $theme['product_card_style'] ?? 'classic';
        $showRatings = $theme['show_product_ratings'] ?? true;
        $showAddToCart = $theme['show_add_to_cart_button'] ?? true;
        $showWishlist = $theme['show_wishlist_button'] ?? true;
        ?>

        <?php foreach ($sectionData as $product): ?>
        <div class="<?= $colClass ?>">
            <div class="card h-100 product-card shadow-sm">
                <div class="position-relative">
                    <?php if ($product['image_path']): ?>
                    <img src="<?= htmlspecialchars($product['image_path']) ?>"
                         class="card-img-top"
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         style="height: 250px; object-fit: cover;">
                    <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <?php endif; ?>

                    <?php if ($showWishlist): ?>
                    <button class="btn btn-light btn-sm position-absolute top-0 end-0 m-2"
                            onclick="addToWishlist(<?= $product['id'] ?>)">
                        <i class="bi bi-heart"></i>
                    </button>
                    <?php endif; ?>

                    <?php if ($product['stock_quantity'] < 5 && $product['stock_quantity'] > 0): ?>
                    <span class="badge bg-warning position-absolute top-0 start-0 m-2">
                        Low Stock
                    </span>
                    <?php elseif ($product['stock_quantity'] <= 0): ?>
                    <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                        Out of Stock
                    </span>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <h5 class="card-title">
                        <a href="/shop/product/<?= $product['id'] ?>" class="text-decoration-none" style="color: var(--heading-color);">
                            <?= htmlspecialchars($product['name']) ?>
                        </a>
                    </h5>

                    <?php if ($showRatings): ?>
                    <div class="mb-2">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star text-muted"></i>
                        <small class="text-muted">(4.0)</small>
                    </div>
                    <?php endif; ?>

                    <p class="card-text text-muted small">
                        <?= htmlspecialchars(substr($product['description'] ?? '', 0, 100)) ?>...
                    </p>

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="price">
                            <?php if ($product['sale_price'] && $product['sale_price'] < $product['retail_price']): ?>
                            <span class="text-danger fw-bold fs-5">
                                $<?= number_format($product['sale_price'], 2) ?>
                            </span>
                            <span class="text-muted text-decoration-line-through small ms-1">
                                $<?= number_format($product['retail_price'], 2) ?>
                            </span>
                            <?php else: ?>
                            <span class="fw-bold fs-5" style="color: var(--primary-color);">
                                $<?= number_format($product['retail_price'], 2) ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($showAddToCart && $product['stock_quantity'] > 0): ?>
                        <button class="btn btn-primary btn-sm"
                                onclick="addToCart(<?= $product['id'] ?>)">
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-5">
        <a href="/shop" class="btn btn-outline-primary btn-lg">
            View All Products <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <?php else: ?>
    <p class="text-center text-muted">No products available at this time.</p>
    <?php endif; ?>
</div>
