<?php ob_start(); ?>

<!-- Modern Hero Section -->
<section class="position-relative overflow-hidden"
    style="height: 400px; background: url('/assets/images/hero-memberships.png') center/cover no-repeat;">
    <div class="position-absolute top-0 start-0 w-100 h-100"
        style="background: linear-gradient(135deg, rgba(0,102,204,0.9) 0%, rgba(0,76,153,0.9) 100%);"></div>
    <div class="container position-relative h-100 d-flex flex-column justify-content-center align-items-center text-white text-center"
        style="z-index: 1;">
        <h1 class="display-3 fw-bold mb-3">Club Memberships</h1>
        <p class="lead mb-4" style="max-width: 600px;">Join our dive club and enjoy exclusive benefits, discounts, and
            priority access</p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center bg-transparent mb-0">
                <li class="breadcrumb-item"><a href="/" class="text-white text-decoration-none"><i
                            class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page">Memberships</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Membership Tiers -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Choose Your Membership</h2>
            <p class="lead text-muted">Select the plan that best fits your diving lifestyle</p>
        </div>

        <?php if (!empty($tiers)): ?>
            <div class="row g-4">
                <?php foreach ($tiers as $tier): ?>
                    <?php
                    $badges = [
                        1 => ['color' => 'secondary', 'icon' => 'bi-star'],
                        2 => ['color' => 'info', 'icon' => 'bi-star-fill'],
                        3 => ['color' => 'warning', 'icon' => 'bi-gem'],
                        4 => ['color' => 'primary', 'icon' => 'bi-trophy-fill']
                    ];
                    $badge = $badges[$tier['display_order']] ?? ['color' => 'secondary', 'icon' => 'bi-star'];
                    $isPopular = $tier['display_order'] == 2; // Silver is popular
                    ?>
                    <div class="col-lg-3 col-md-6">
                        <div
                            class="card h-100 border-0 shadow-sm <?= $isPopular ? 'border-primary' : '' ?> hover-lift position-relative">
                            <?php if ($isPopular): ?>
                                <div class="position-absolute top-0 start-50 translate-middle">
                                    <span class="badge bg-primary px-3 py-2">Most Popular</span>
                                </div>
                            <?php endif; ?>

                            <div class="card-body p-4 text-center">
                                <div class="mb-3">
                                    <i class="<?= $badge['icon'] ?> text-<?= $badge['color'] ?>" style="font-size: 3rem;"></i>
                                </div>
                                <h3 class="h4 fw-bold mb-2">
                                    <?= htmlspecialchars($tier['name']) ?>
                                </h3>
                                <p class="text-muted small mb-4">
                                    <?= htmlspecialchars($tier['description']) ?>
                                </p>

                                <div class="mb-4">
                                    <span class="display-4 fw-bold text-primary">$
                                        <?= number_format($tier['price'], 0) ?>
                                    </span>
                                    <span class="text-muted">/year</span>
                                </div>

                                <div class="text-start mb-4">
                                    <?php
                                    $benefits = explode("\n", $tier['benefits']);
                                    foreach ($benefits as $benefit):
                                        if (trim($benefit)):
                                            ?>
                                            <div class="d-flex align-items-start mb-2">
                                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                                <small>
                                                    <?= htmlspecialchars(trim($benefit)) ?>
                                                </small>
                                            </div>
                                        <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </div>

                                <a href="/memberships/join/<?= $tier['id'] ?>"
                                    class="btn btn-<?= $isPopular ? 'primary' : 'outline-primary' ?> w-100">
                                    Join Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-award text-muted" style="font-size: 4rem;"></i>
                <h3 class="mt-4 mb-2">No memberships available</h3>
                <p class="text-muted">Check back soon for membership options!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Why Join Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold mb-3">Why Join Our Dive Club?</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                        style="width: 80px; height: 80px;">
                        <i class="bi bi-piggy-bank fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Save Money</h4>
                    <p class="text-muted">Get exclusive discounts on equipment, courses, and dive trips</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                        style="width: 80px; height: 80px;">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Join Community</h4>
                    <p class="text-muted">Connect with fellow divers and participate in exclusive events</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                        style="width: 80px; height: 80px;">
                        <i class="bi bi-star fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Priority Access</h4>
                    <p class="text-muted">Get first dibs on course bookings and popular dive trips</p>
                </div>
            </div>
        </div>
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
<?php include __DIR__ . '/../layouts/main.php'; ?>