<?php ob_start(); ?>

<!-- Modern Hero Section -->
<section class="position-relative overflow-hidden"
    style="height: 400px; background: url('/assets/images/hero-rentals.png') center/cover no-repeat;">
    <div class="position-absolute top-0 start-0 w-100 h-100"
        style="background: linear-gradient(135deg, rgba(0,102,204,0.9) 0%, rgba(0,76,153,0.9) 100%);"></div>
    <div class="container position-relative h-100 d-flex flex-column justify-content-center align-items-center text-white text-center"
        style="z-index: 1;">
        <h1 class="display-3 fw-bold mb-3">Equipment Rental</h1>
        <p class="lead mb-4" style="max-width: 600px;">Professional-grade rental equipment available</p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center bg-transparent mb-0">
                <li class="breadcrumb-item"><a href="/" class="text-white text-decoration-none"><i
                            class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page">Rentals</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Rentals Grid -->
<section class="py-5">
    <div class="container">
        <?php if (!empty($rentals)): ?>
            <div class="row g-4">
                <?php foreach ($rentals as $rental): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm hover-lift">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary text-white rounded-circle p-3 me-3">
                                        <i class="bi bi-tools fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="h5 mb-0"><?= htmlspecialchars($rental['name']) ?></h3>
                                        <small
                                            class="text-muted"><?= htmlspecialchars($rental['category'] ?? 'Equipment') ?></small>
                                    </div>
                                </div>
                                <p class="text-muted mb-3"><?= htmlspecialchars($rental['description'] ?? '') ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span
                                            class="h5 text-primary mb-0">$<?= number_format($rental['daily_rate'], 2) ?></span>
                                        <small class="text-muted">/day</small>
                                    </div>
                                    <a href="/rentals/<?= $rental['id'] ?>" class="btn btn-outline-primary btn-sm">Rent Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-tools text-muted" style="font-size: 4rem;"></i>
                <h3 class="mt-4 mb-2">No rental equipment available</h3>
                <p class="text-muted">Check back soon or contact us for availability!</p>
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
</style>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/layouts/main.php'; ?>