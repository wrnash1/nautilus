<?php
// Start output buffering for content
ob_start();
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="mb-4">About <?= htmlspecialchars($settings->get('store_name', 'Nautilus Dive Shop')) ?></h1>

            <div class="mb-4">
                <p class="lead">
                    <?= htmlspecialchars($settings->get('store_description', 'Your premier diving equipment and training center.')) ?>
                </p>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Our Mission</h2>
                    <p>
                        At <?= htmlspecialchars($settings->get('store_name', 'Nautilus Dive Shop')) ?>, we're passionate about
                        making scuba diving accessible, safe, and enjoyable for everyone. Whether you're a beginner taking your
                        first breath underwater or an experienced diver planning your next adventure, we're here to support you
                        every step of the way.
                    </p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">What We Offer</h2>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h3 class="h5"><i class="bi bi-cart3"></i> Equipment Sales</h3>
                            <p>Top-quality diving equipment from leading brands.</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h3 class="h5"><i class="bi bi-tools"></i> Equipment Rentals</h3>
                            <p>Professional-grade rental equipment for all experience levels.</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h3 class="h5"><i class="bi bi-book"></i> Training Courses</h3>
                            <p>PADI certification courses from beginner to professional.</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h3 class="h5"><i class="bi bi-globe"></i> Dive Trips</h3>
                            <p>Guided dive trips to amazing underwater destinations.</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h3 class="h5"><i class="bi bi-wrench"></i> Equipment Service</h3>
                            <p>Professional equipment maintenance and repairs.</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h3 class="h5"><i class="bi bi-wind"></i> Air Fills</h3>
                            <p>Compressed air and nitrox fills available daily.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Visit Us</h2>
                    <?php if ($storeAddress = $settings->get('store_address')): ?>
                    <p><i class="bi bi-geo-alt"></i> <strong>Address:</strong><br>
                    <?= nl2br(htmlspecialchars($storeAddress)) ?></p>
                    <?php endif; ?>

                    <?php if ($contactPhone = $settings->get('contact_phone')): ?>
                    <p><i class="bi bi-telephone"></i> <strong>Phone:</strong>
                    <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9]/', '', $contactPhone)) ?>">
                        <?= htmlspecialchars($contactPhone) ?>
                    </a></p>
                    <?php endif; ?>

                    <?php if ($contactEmail = $settings->get('contact_email')): ?>
                    <p><i class="bi bi-envelope"></i> <strong>Email:</strong>
                    <a href="mailto:<?= htmlspecialchars($contactEmail) ?>">
                        <?= htmlspecialchars($contactEmail) ?>
                    </a></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center">
                <a href="/contact" class="btn btn-primary btn-lg">Contact Us</a>
                <a href="/shop" class="btn btn-outline-primary btn-lg">Shop Equipment</a>
            </div>
        </div>
    </div>
</div>

<?php
// Capture content
$content = ob_get_clean();

// Set page variables
$pageTitle = 'About Us - ' . ($settings->get('store_name', 'Nautilus Dive Shop'));
$metaDescription = 'Learn more about ' . ($settings->get('store_name', 'Nautilus Dive Shop')) . ' - your premier diving equipment and training center.';

// Include layout
include __DIR__ . '/layouts/main.php';
?>
