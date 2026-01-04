<?php ob_start(); ?>

<!-- Hero Section -->
<section class="position-relative overflow-hidden py-5 bg-primary text-white"
    style="background: linear-gradient(135deg, var(--primary-700) 0%, var(--primary-900) 100%);">
    <div class="container position-relative z-1 text-center">
        <h1 class="display-4 fw-bold font-heading mb-3">Contact Us</h1>
        <p class="lead text-light opacity-75 mb-0">Have questions? We'd love to hear from you.</p>
    </div>
</section>

<!-- Content -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Info -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="h4 font-heading fw-bold text-gray-900 mb-4">Get in Touch</h3>
                        <div class="d-flex flex-column gap-4">

                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3"
                                        style="width: 48px; height: 48px;">
                                        <i class="bi bi-geo-alt-fill fs-4"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <h4 class="h6 fw-bold text-gray-900 mb-1">Visit Our Shop</h4>
                                    <p class="text-muted mb-0">
                                        <?php if (!empty($theme['business_address'])): ?>
                                            <?= htmlspecialchars($theme['business_address']) ?><br>
                                            <?php if (!empty($theme['business_city'])): ?>
                                                <?= htmlspecialchars($theme['business_city']) ?>,
                                                <?= htmlspecialchars($theme['business_state']) ?>
                                                <?= htmlspecialchars($theme['business_zip']) ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            149 W main street<br>Azle, Texas 76020
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3"
                                        style="width: 48px; height: 48px;">
                                        <i class="bi bi-telephone-fill fs-4"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <h4 class="h6 fw-bold text-gray-900 mb-1">Call Us</h4>
                                    <p class="text-muted mb-0">
                                        <a href="tel:<?= htmlspecialchars(str_replace([' ', '-', '(', ')'], '', $theme['business_phone'] ?? '8174064080')) ?>"
                                            class="text-decoration-none text-muted">
                                            <?= htmlspecialchars($theme['business_phone'] ?? '817-406-4080') ?>
                                        </a>
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3"
                                        style="width: 48px; height: 48px;">
                                        <i class="bi bi-envelope-fill fs-4"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <h4 class="h6 fw-bold text-gray-900 mb-1">Email Us</h4>
                                    <p class="text-muted mb-0">
                                        <a href="mailto:<?= htmlspecialchars($theme['business_email'] ?? 'info@nautilus.local') ?>"
                                            class="text-decoration-none text-muted">
                                            <?= htmlspecialchars($theme['business_email'] ?? 'info@nautilus.local') ?>
                                        </a>
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3"
                                        style="width: 48px; height: 48px;">
                                        <i class="bi bi-clock-fill fs-4"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <h4 class="h6 fw-bold text-gray-900 mb-1">Hours</h4>
                                    <p class="text-muted mb-0">Mon-Fri: 10am - 7pm<br>Sat: 10am - 6pm<br>Sun: 12pm - 5pm
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="h4 font-heading fw-bold text-gray-900 mb-4">Send a Message</h3>
                        <form action="#" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-medium text-gray-700">Name</label>
                                <input type="text" name="name" id="name" class="form-control form-control-lg"
                                    placeholder="Your Name">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-medium text-gray-700">Email</label>
                                <input type="email" name="email" id="email" class="form-control form-control-lg"
                                    placeholder="you@example.com">
                            </div>

                            <div class="mb-4">
                                <label for="message" class="form-label fw-medium text-gray-700">Message</label>
                                <textarea id="message" name="message" rows="4" class="form-control form-control-lg"
                                    placeholder="How can we help?"></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg btn-modern">
                                    Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/layouts/main.php'; ?>