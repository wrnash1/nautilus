<?php
$pageTitle = 'Welcome';
ob_start();
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1>Explore the Underwater World</h1>
                <p class="lead">Your gateway to scuba diving adventures, professional training, and quality dive gear.</p>
                <div class="mt-4">
                    <a href="/courses" class="btn btn-light btn-lg me-2">
                        <i class="bi bi-mortarboard"></i> Browse Courses
                    </a>
                    <a href="/shop" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-cart"></i> Shop Gear
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <i class="bi bi-water" style="font-size: 15rem; opacity: 0.2;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Featured Products -->
<?php if (!empty($featuredProducts)): ?>
<div class="container mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Featured Products</h2>
            <p class="text-muted">Check out our latest and most popular dive gear</p>
        </div>
    </div>
    <div class="row">
        <?php foreach (array_slice($featuredProducts, 0, 4) as $product): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <?php if ($product['image_path']): ?>
                <img src="<?= htmlspecialchars($product['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="bi bi-box-seam" style="font-size: 3rem; color: #ccc;"></i>
                </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                    <p class="card-text text-muted small"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></p>
                    <p class="card-text">
                        <strong class="text-primary"><?= formatCurrency($product['price']) ?></strong>
                    </p>
                    <a href="/shop/product/<?= $product['id'] ?>" class="btn btn-outline-primary btn-sm w-100">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center">
        <a href="/shop" class="btn btn-primary">View All Products</a>
    </div>
</div>
<?php endif; ?>

<!-- Upcoming Courses -->
<?php if (!empty($upcomingCourses)): ?>
<div class="container mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Upcoming Courses</h2>
            <p class="text-muted">Start your diving journey or advance your skills</p>
        </div>
    </div>
    <div class="row">
        <?php foreach (array_slice($upcomingCourses, 0, 3) as $schedule): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($schedule['course_name']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars(substr($schedule['description'] ?? '', 0, 100)) ?>...</p>
                    <p class="card-text">
                        <i class="bi bi-calendar"></i> <?= date('M d, Y', strtotime($schedule['start_date'])) ?><br>
                        <i class="bi bi-clock"></i> <?= htmlspecialchars($schedule['duration'] ?? 'TBD') ?><br>
                        <i class="bi bi-currency-dollar"></i> <?= formatCurrency($schedule['price'] ?? 0) ?>
                    </p>
                    <a href="/courses/schedule/<?= $schedule['id'] ?>" class="btn btn-primary btn-sm w-100">
                        Learn More
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center">
        <a href="/courses" class="btn btn-primary">View All Courses</a>
    </div>
</div>
<?php endif; ?>

<!-- Upcoming Trips -->
<?php if (!empty($upcomingTrips)): ?>
<div class="container mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Upcoming Dive Trips</h2>
            <p class="text-muted">Join us for unforgettable diving adventures</p>
        </div>
    </div>
    <div class="row">
        <?php foreach (array_slice($upcomingTrips, 0, 3) as $schedule): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($schedule['trip_name']) ?></h5>
                    <p class="card-text">
                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($schedule['destination'] ?? 'TBD') ?>
                    </p>
                    <p class="card-text"><?= htmlspecialchars(substr($schedule['description'] ?? '', 0, 100)) ?>...</p>
                    <p class="card-text">
                        <i class="bi bi-calendar"></i> <?= date('M d', strtotime($schedule['start_date'])) ?> - <?= date('M d, Y', strtotime($schedule['end_date'])) ?><br>
                        <i class="bi bi-currency-dollar"></i> <?= formatCurrency($schedule['price'] ?? 0) ?>
                    </p>
                    <a href="/trips/schedule/<?= $schedule['id'] ?>" class="btn btn-primary btn-sm w-100">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center">
        <a href="/trips" class="btn btn-primary">View All Trips</a>
    </div>
</div>
<?php endif; ?>

<!-- Why Choose Us -->
<div class="container mb-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2>Why Choose Us</h2>
            <p class="text-muted">We're committed to providing the best diving experience</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3 text-center mb-4">
            <div class="mb-3">
                <i class="bi bi-award" style="font-size: 3rem; color: var(--primary-color);"></i>
            </div>
            <h5>Certified Instructors</h5>
            <p class="text-muted">All our instructors are PADI certified professionals</p>
        </div>
        <div class="col-md-3 text-center mb-4">
            <div class="mb-3">
                <i class="bi bi-shield-check" style="font-size: 3rem; color: var(--primary-color);"></i>
            </div>
            <h5>Safety First</h5>
            <p class="text-muted">We maintain the highest safety standards</p>
        </div>
        <div class="col-md-3 text-center mb-4">
            <div class="mb-3">
                <i class="bi bi-gear" style="font-size: 3rem; color: var(--primary-color);"></i>
            </div>
            <h5>Quality Equipment</h5>
            <p class="text-muted">Top-brand gear, regularly serviced and maintained</p>
        </div>
        <div class="col-md-3 text-center mb-4">
            <div class="mb-3">
                <i class="bi bi-people" style="font-size: 3rem; color: var(--primary-color);"></i>
            </div>
            <h5>Community</h5>
            <p class="text-muted">Join our vibrant community of divers</p>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="container mb-5">
    <div class="card bg-primary text-white">
        <div class="card-body text-center py-5">
            <h2>Ready to Start Your Diving Journey?</h2>
            <p class="lead">Contact us today to learn more about our courses and services</p>
            <a href="/contact" class="btn btn-light btn-lg mt-3">
                <i class="bi bi-envelope"></i> Contact Us
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/public.php';
?>
