<?php ob_start(); ?>

<!-- Modern Hero Section -->
<section class="position-relative overflow-hidden"
    style="height: 400px; background: url('/assets/images/hero-trips.png') center/cover no-repeat;">
    <div class="position-absolute top-0 start-0 w-100 h-100"
        style="background: linear-gradient(135deg, rgba(0,102,204,0.9) 0%, rgba(0,76,153,0.9) 100%);"></div>
    <div class="container position-relative h-100 d-flex flex-column justify-content-center align-items-center text-white text-center"
        style="z-index: 1;">
        <h1 class="display-3 fw-bold mb-3">Dive Trips</h1>
        <p class="lead mb-4" style="max-width: 600px;">Guided adventures to amazing dive sites worldwide</p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center bg-transparent mb-0">
                <li class="breadcrumb-item"><a href="/" class="text-white text-decoration-none"><i
                            class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page">Dive Trips</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Trips Grid -->
<section class="py-5">
    <div class="container">
        <?php if (!empty($trips)): ?>
            <div class="row g-4">
                <?php foreach ($trips as $trip): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm hover-lift">
                            <?php if (!empty($trip['image_url'])): ?>
                                <img src="<?= htmlspecialchars($trip['image_url']) ?>" class="card-img-top"
                                    alt="<?= htmlspecialchars($trip['destination']) ?>" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h3 class="h5 mb-0"><?= htmlspecialchars($trip['destination']) ?></h3>
                                    <span class="badge bg-primary"><?= htmlspecialchars($trip['duration'] ?? '') ?> days</span>
                                </div>
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-calendar"></i> <?= date('M d, Y', strtotime($trip['start_date'])) ?>
                                </p>
                                <p class="text-muted mb-3"><?= htmlspecialchars($trip['description'] ?? '') ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 text-primary mb-0">$<?= number_format($trip['price'], 2) ?></span>
                                    <a href="/trips/<?= $trip['id'] ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-geo-alt text-muted" style="font-size: 4rem;"></i>
                <h3 class="mt-4 mb-2">No trips scheduled</h3>
                <p class="text-muted">Check back soon for exciting dive adventures!</p>
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