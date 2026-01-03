<?php ob_start(); ?>

<!-- Hero Carousel -->
<div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" style="margin-top: -20px;">
    <div class="carousel-indicators">
        <?php if (!empty($carousel_slides)): ?>
            <?php foreach ($carousel_slides as $index => $slide): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $index ?>"
                    class="<?= $index === 0 ? 'active' : '' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
            <?php endforeach; ?>
        <?php else: ?>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
        <?php endif; ?>
    </div>

    <div class="carousel-inner">
        <?php if (!empty($carousel_slides)): ?>
            <?php foreach ($carousel_slides as $index => $slide): ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>" style="height: 600px;">
                    <img src="<?= htmlspecialchars($slide['image_url']) ?>" alt="<?= htmlspecialchars($slide['title']) ?>"
                        style="width: 100%; height: 100%; object-fit: cover;">
                    <div class="carousel-caption d-flex flex-column justify-content-center align-items-center h-100"
                        style="background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.6)); left: 0; right: 0; bottom: 0; top: 0;">
                        <h1 class="display-3 fw-bold mb-4"><?= htmlspecialchars($slide['title']) ?></h1>
                        <p class="lead mb-4"><?= htmlspecialchars($slide['description']) ?></p>
                        <?php if (!empty($slide['button_link'])): ?>
                            <a href="<?= htmlspecialchars($slide['button_link']) ?>" class="btn btn-primary btn-lg px-5 py-3">
                                <?= htmlspecialchars($slide['button_text'] ?? 'Learn More') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Default slide if no carousel data -->
            <div class="carousel-item active" style="height: 600px;">
                <img src="/assets/images/carousel-hero.png" alt="Underwater Adventure"
                    style="width: 100%; height: 100%; object-fit: cover;">
                <div class="carousel-caption d-flex flex-column justify-content-center align-items-center h-100"
                    style="background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.6)); left: 0; right: 0; bottom: 0; top: 0;">
                    <h1 class="display-3 fw-bold mb-4">Explore the Underwater World</h1>
                    <p class="lead mb-4">Begin your diving adventure with professional training and equipment</p>
                    <a href="/courses" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-lg">Browse Courses <i
                            class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<!-- What We Offer Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">What We Offer</h2>
            <p class="lead text-muted">Everything you need for your diving journey</p>
        </div>

        <div class="row g-4">
            <?php if (!empty($service_boxes)): ?>
                <?php foreach ($service_boxes as $service): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm text-center p-4 hover-lift">
                            <div class="mb-3">
                                <i class="<?= htmlspecialchars($service['icon'] ?? 'bi bi-star') ?> text-primary"
                                    style="font-size: 3rem;"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-3"><?= htmlspecialchars($service['title']) ?></h3>
                            <p class="text-muted mb-3"><?= htmlspecialchars($service['description']) ?></p>
                            <?php if (!empty($service['link'])): ?>
                                <a href="<?= htmlspecialchars($service['link']) ?>" class="btn btn-outline-primary btn-sm">
                                    Learn More <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default service boxes -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-award text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">PADI Courses</h3>
                        <p class="text-muted mb-3">Professional diving certification from beginner to instructor</p>
                        <a href="/courses" class="btn btn-outline-primary btn-sm">Learn More <i
                                class="bi bi-arrow-right ms-2"></i></a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-shop text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Equipment Shop</h3>
                        <p class="text-muted mb-3">Top-quality diving gear and accessories</p>
                        <a href="/shop" class="btn btn-outline-primary btn-sm">Shop Now <i
                                class="bi bi-arrow-right ms-2"></i></a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-geo-alt text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Dive Trips</h3>
                        <p class="text-muted mb-3">Guided adventures to amazing dive sites worldwide</p>
                        <a href="/trips" class="btn btn-outline-primary btn-sm">View Trips <i
                                class="bi bi-arrow-right ms-2"></i></a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-tools text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Equipment Rental</h3>
                        <p class="text-muted mb-3">Professional-grade rental equipment available</p>
                        <a href="/rentals" class="btn btn-outline-primary btn-sm">View Rentals <i
                                class="bi bi-arrow-right ms-2"></i></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-6 col-md-3">
                <i class="bi bi-people-fill" style="font-size: 3rem;"></i>
                <div class="h2 fw-bold mt-3 mb-2"><?= htmlspecialchars($theme['stats_certified_divers'] ?? '5000') ?>+
                </div>
                <div>Certified Divers</div>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-award-fill" style="font-size: 3rem;"></i>
                <div class="h2 fw-bold mt-3 mb-2"><?= htmlspecialchars($theme['stats_years_experience'] ?? '25') ?>+
                </div>
                <div>Years Experience</div>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-globe" style="font-size: 3rem;"></i>
                <div class="h2 fw-bold mt-3 mb-2"><?= htmlspecialchars($theme['stats_dive_destinations'] ?? '100') ?>+
                </div>
                <div>Dive Destinations</div>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-star-fill" style="font-size: 3rem;"></i>
                <div class="h2 fw-bold mt-3 mb-2"><?= htmlspecialchars($theme['stats_customer_rating'] ?? '4.9') ?>/5
                </div>
                <div>Customer Rating</div>
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
<?php include __DIR__ . '/layouts/main.php'; ?>