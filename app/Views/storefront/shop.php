<?php ob_start(); ?>

<!-- Modern Hero Section -->
<section class="position-relative overflow-hidden"
    style="height: 400px; background: url('/assets/images/hero-shop.png') center/cover no-repeat;">
    <div class="position-absolute top-0 start-0 w-100 h-100"
        style="background: linear-gradient(135deg, rgba(0,102,204,0.9) 0%, rgba(0,76,153,0.9) 100%);"></div>
    <div class="container position-relative h-100 d-flex flex-column justify-content-center align-items-center text-white text-center"
        style="z-index: 1;">
        <h1 class="display-3 fw-bold mb-3">Shop Equipment</h1>
        <p class="lead mb-4" style="max-width: 600px;">Premium gear for your underwater adventures</p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center bg-transparent mb-0">
                <li class="breadcrumb-item"><a href="/" class="text-white text-decoration-none"><i
                            class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page">Shop</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Product Grid -->
<section class="py-5">
    <div class="container">
        <?php if (!empty($products)): ?>
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-6 col-lg-3">
                        <a href="/shop/product/<?= $product['id'] ?>" class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm hover-lift transition-base">
                                <div class="product-image" style="height: 250px; overflow: hidden; background: #f1f1f1;">
                                    <?php if (!empty($product['image_url'])): ?>
                                        <img src="<?= htmlspecialchars($product['image_url']) ?>"
                                            alt="<?= htmlspecialchars($product['name']) ?>" class="w-100 h-100 object-fit-cover">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted">
                                            <i class="bi bi-camera" style="font-size: 3rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column p-4">
                                    <div class="text-uppercase small text-muted mb-2" style="letter-spacing: 1px;">
                                        <?= htmlspecialchars($product['category'] ?? 'Equipment') ?>
                                    </div>
                                    <h3 class="h5 font-heading fw-bold mb-2 text-dark">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </h3>
                                    <div class="mt-auto">
                                        <span class="h4 text-primary mb-0 fw-bold">
                                            $<?= number_format($product['price'], 2) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-box-seam text-muted" style="font-size: 4rem;"></i>
                <h3 class="mt-4 mb-2">No products found</h3>
                <p class="text-muted">Check back soon for new inventory!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    }

    .product-image img {
        transition: transform 0.5s ease;
    }

    .hover-lift:hover .product-image img {
        transform: scale(1.05);
    }
</style>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/layouts/main.php'; ?>