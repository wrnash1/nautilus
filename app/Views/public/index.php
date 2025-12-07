<?php
$pageTitle = 'Welcome';
ob_start();
?>

<!-- Hero Carousel Section -->
<div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
    </div>
    
    <div class="carousel-inner">
        <!-- Slide 1: Ocean Adventure -->
        <div class="carousel-item active">
            <div class="hero-section" style="background: linear-gradient(135deg, rgba(0,102,204,0.9), rgba(0,51,102,0.9)), url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 1200 600\"><defs><linearGradient id=\"water\" x1=\"0%\" y1=\"0%\" x2=\"100%\" y2=\"100%\"><stop offset=\"0%\" style=\"stop-color:%230066cc;stop-opacity:1\" /><stop offset=\"100%\" style=\"stop-color:%23003366;stop-opacity:1\" /></linearGradient></defs><rect fill=\"url(%23water)\" width=\"1200\" height=\"600\"/><circle cx=\"200\" cy=\"150\" r=\"80\" fill=\"%23ffffff\" opacity=\"0.1\"/><circle cx=\"800\" cy=\"400\" r=\"120\" fill=\"%23ffffff\" opacity=\"0.08\"/><circle cx=\"1000\" cy=\"200\" r=\"60\" fill=\"%23ffffff\" opacity=\"0.12\"/></svg>'); background-size: cover; background-position: center;">
                <div class=\"container\">
                    <div class=\"row align-items-center\">
                        <div class=\"col-lg-8 text-white\">
                            <h1 class=\"display-3 fw-bold mb-4\">Explore the Underwater World</h1>
                            <p class=\"lead fs-4 mb-4\">Discover the beauty beneath the waves. Professional training, quality gear, and unforgettable adventures await.</p>
                            <div class=\"mt-4\">
                                <a href=\"/courses\" class=\"btn btn-light btn-lg me-2 mb-2\">
                                    <i class=\"bi bi-mortarboard\"></i> Browse Courses
                                </a>
                                <a href=\"/shop\" class=\"btn btn-outline-light btn-lg mb-2\">
                                    <i class=\"bi bi-cart\"></i> Shop Gear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Slide 2: Travel Destinations -->
        <div class=\"carousel-item\">
            <div class=\"hero-section\" style=\"background: linear-gradient(135deg, rgba(0,153,153,0.9), rgba(0,102,102,0.9)), url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 1200 600\"><defs><linearGradient id=\"tropical\" x1=\"0%\" y1=\"0%\" x2=\"100%\" y2=\"100%\"><stop offset=\"0%\" style=\"stop-color:%23009999;stop-opacity:1\" /><stop offset=\"100%\" style=\"stop-color:%23006666;stop-opacity:1\" /></linearGradient></defs><rect fill=\"url(%23tropical)\" width=\"1200\" height=\"600\"/><path d=\"M 100,500 Q 300,400 500,500 T 900,500\" stroke=\"%23ffffff\" stroke-width=\"3\" fill=\"none\" opacity=\"0.2\"/><circle cx=\"300\" cy=\"100\" r=\"40\" fill=\"%23ffcc00\" opacity=\"0.3\"/></svg>'); background-size: cover; background-position: center;">
                <div class=\"container\">
                    <div class=\"row align-items-center\">
                        <div class=\"col-lg-8 text-white\">
                            <h1 class=\"display-3 fw-bold mb-4\">Dive Exotic Destinations</h1>
                            <p class=\"lead fs-4 mb-4\">Join us on incredible dive trips to the world's most beautiful underwater locations.</p>
                            <div class=\"mt-4\">
                                <a href=\"/trips\" class=\"btn btn-light btn-lg me-2 mb-2\">
                                    <i class=\"bi bi-airplane\"></i> View Trips
                                </a>
                                <a href=\"/contact\" class=\"btn btn-outline-light btn-lg mb-2\">
                                    <i class=\"bi bi-envelope\"></i> Contact Us
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Slide 3: Professional Training -->
        <div class=\"carousel-item\">
            <div class=\"hero-section\" style=\"background: linear-gradient(135deg, rgba(51,102,204,0.9), rgba(25,51,153,0.9)), url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 1200 600\"><defs><linearGradient id=\"deep\" x1=\"0%\" y1=\"0%\" x2=\"100%\" y2=\"100%\"><stop offset=\"0%\" style=\"stop-color:%233366cc;stop-opacity:1\" /><stop offset=\"100%\" style=\"stop-color:%23193399;stop-opacity:1\" /></linearGradient></defs><rect fill=\"url(%23deep)\" width=\"1200\" height=\"600\"/><ellipse cx=\"600\" cy=\"300\" rx=\"400\" ry=\"200\" fill=\"%23ffffff\" opacity=\"0.05\"/><path d=\"M 200,300 L 400,200 L 600,300 L 800,200 L 1000,300\" stroke=\"%23ffffff\" stroke-width=\"2\" fill=\"none\" opacity=\"0.15\"/></svg>'); background-size: cover; background-position: center;">
                <div class=\"container\">
                    <div class=\"row align-items-center\">
                        <div class=\"col-lg-8 text-white\">
                            <h1 class=\"display-3 fw-bold mb-4\">Professional Dive Training</h1>
                            <p class=\"lead fs-4 mb-4\">Learn from PADI-certified instructors. From beginner to advanced, we'll guide your journey.</p>
                            <div class=\"mt-4\">
                                <a href=\"/courses\" class=\"btn btn-light btn-lg me-2 mb-2\">
                                    <i class=\"bi bi-award\"></i> Get Certified
                                </a>
                                <a href=\"/about\" class=\"btn btn-outline-light btn-lg mb-2\">
                                    <i class=\"bi bi-info-circle\"></i> Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <button class=\"carousel-control-prev\" type=\"button\" data-bs-target=\"#heroCarousel\" data-bs-slide=\"prev\">
        <span class=\"carousel-control-prev-icon\" aria-hidden=\"true\"></span>
        <span class=\"visually-hidden\">Previous</span>
    </button>
    <button class=\"carousel-control-next\" type=\"button\" data-bs-target=\"#heroCarousel\" data-bs-slide=\"next\">
        <span class=\"carousel-control-next-icon\" aria-hidden=\"true\"></span>
        <span class=\"visually-hidden\">Next</span>
    </button>
</div>

<style>
.carousel-fade .carousel-item {
    opacity: 0;
    transition: opacity 1s ease-in-out;
}
.carousel-fade .carousel-item.active {
    opacity: 1;
}
.carousel-control-prev-icon,
.carousel-control-next-icon {
    background-color: rgba(0,0,0,0.3);
    border-radius: 50%;
    padding: 20px;
}
</style>

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
