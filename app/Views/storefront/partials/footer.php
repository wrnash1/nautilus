<footer class="bg-dark text-white pt-5 pb-3">
    <div class="container">
        <div class="row g-4">
            <!-- About Section -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-water text-primary"></i>
                    <?= htmlspecialchars($theme['business_name'] ?? 'Nautilus Dive Shop') ?>
                </h5>
                <p class="text-light opacity-75 small">
                    Your premier destination for scuba diving training, equipment, and unforgettable underwater
                    adventures.
                </p>
                <!-- Social Links -->
                <?php if (!empty($social_links)): ?>
                    <div class="d-flex gap-2 mt-3">
                        <?php if (!empty($social_links['facebook'])): ?>
                            <a href="<?= htmlspecialchars($social_links['facebook']) ?>" target="_blank"
                                class="btn btn-sm btn-outline-light rounded-circle"
                                style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-facebook"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($social_links['instagram'])): ?>
                            <a href="<?= htmlspecialchars($social_links['instagram']) ?>" target="_blank"
                                class="btn btn-sm btn-outline-light rounded-circle"
                                style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-instagram"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($social_links['youtube'])): ?>
                            <a href="<?= htmlspecialchars($social_links['youtube']) ?>" target="_blank"
                                class="btn btn-sm btn-outline-light rounded-circle"
                                style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-youtube"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($social_links['twitter'])): ?>
                            <a href="<?= htmlspecialchars($social_links['twitter']) ?>" target="_blank"
                                class="btn btn-sm btn-outline-light rounded-circle"
                                style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-twitter"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h6 class="fw-bold mb-3">Quick Links</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="/shop"
                            class="text-light text-decoration-none opacity-75 hover-opacity-100"><i
                                class="bi bi-chevron-right small"></i> Shop</a></li>
                    <li class="mb-2"><a href="/courses"
                            class="text-light text-decoration-none opacity-75 hover-opacity-100"><i
                                class="bi bi-chevron-right small"></i> Courses</a></li>
                    <li class="mb-2"><a href="/trips"
                            class="text-light text-decoration-none opacity-75 hover-opacity-100"><i
                                class="bi bi-chevron-right small"></i> Dive Trips</a></li>
                    <li class="mb-2"><a href="/rentals"
                            class="text-light text-decoration-none opacity-75 hover-opacity-100"><i
                                class="bi bi-chevron-right small"></i> Rentals</a></li>
                    <li class="mb-2"><a href="/services"
                            class="text-light text-decoration-none opacity-75 hover-opacity-100"><i
                                class="bi bi-chevron-right small"></i> Services</a></li>
                    <li class="mb-2"><a href="/about"
                            class="text-light text-decoration-none opacity-75 hover-opacity-100"><i
                                class="bi bi-chevron-right small"></i> About Us</a></li>
                    <li class="mb-2"><a href="/contact"
                            class="text-light text-decoration-none opacity-75 hover-opacity-100"><i
                                class="bi bi-chevron-right small"></i> Contact</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold mb-3">Contact Us</h6>
                <ul class="list-unstyled text-light opacity-75 small">
                    <?php if (!empty($theme['business_address'])): ?>
                        <li class="mb-2">
                            <i class="bi bi-geo-alt text-primary"></i>
                            <?= htmlspecialchars($theme['business_address']) ?><br>
                            <?php if (!empty($theme['business_city'])): ?>
                                <?= htmlspecialchars($theme['business_city']) ?>,
                                <?= htmlspecialchars($theme['business_state']) ?>
                                <?= htmlspecialchars($theme['business_zip']) ?>
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($theme['business_phone'])): ?>
                        <li class="mb-2">
                            <i class="bi bi-telephone text-primary"></i>
                            <a href="tel:<?= htmlspecialchars($theme['business_phone']) ?>"
                                class="text-light text-decoration-none">
                                <?= htmlspecialchars($theme['business_phone']) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($theme['business_email'])): ?>
                        <li class="mb-2">
                            <i class="bi bi-envelope text-primary"></i>
                            <a href="mailto:<?= htmlspecialchars($theme['business_email']) ?>"
                                class="text-light text-decoration-none">
                                <?= htmlspecialchars($theme['business_email']) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Business Hours -->
                <h6 class="fw-bold mb-2 mt-3">Hours</h6>
                <ul class="list-unstyled text-light opacity-75 small">
                    <li>Mon-Fri: 9:00 AM - 6:00 PM</li>
                    <li>Saturday: 10:00 AM - 5:00 PM</li>
                    <li>Sunday: Closed</li>
                </ul>
            </div>

            <!-- Map -->
            <div class="col-lg-4 col-md-6">
                <h6 class="fw-bold mb-3">Find Us</h6>
                <?php
                $address = trim(($theme['business_address'] ?? '') . ', ' . ($theme['business_city'] ?? '') . ', ' . ($theme['business_state'] ?? '') . ' ' . ($theme['business_zip'] ?? ''));
                $mapAddress = !empty($address) && $address !== ', , ' ? urlencode($address) : urlencode('149 W Main Street, Azle, TX 76020');
                ?>
                <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
                    <iframe
                        src="https://www.google.com/maps/embed/v1/place?key=AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8&q=<?= $mapAddress ?>&zoom=17"
                        style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <div class="mt-2">
                    <a href="https://www.google.com/maps/search/?api=1&query=<?= $mapAddress ?>" target="_blank"
                        class="btn btn-sm btn-outline-light">
                        <i class="bi bi-map"></i> Get Directions
                    </a>
                </div>
            </div>
        </div>

        <!-- Newsletter Signup -->
        <div class="row mt-4 pt-4 border-top border-secondary">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <h6 class="fw-bold mb-2">Subscribe to Our Newsletter</h6>
                <p class="text-light opacity-75 small mb-2">Get special offers, dive tips, and updates delivered to your
                    inbox.</p>
                <form action="/api/newsletter/subscribe" method="POST" class="d-flex gap-2">
                    <input type="email" name="email" class="form-control form-control-sm"
                        placeholder="Your email address" required>
                    <button type="submit" class="btn btn-primary btn-sm text-nowrap">Subscribe</button>
                </form>
            </div>
            <div class="col-lg-6 text-lg-end">
                <h6 class="fw-bold mb-2">Certifications & Affiliations</h6>
                <div class="d-flex gap-3 justify-content-lg-end flex-wrap">
                    <?php
                    $primaryOrg = $theme['primary_certification_org'] ?? 'PADI';
                    $certLevel = $theme['certification_level'] ?? '5-Star Center';
                    $secondaryCerts = !empty($theme['secondary_certifications']) ? explode(',', $theme['secondary_certifications']) : ['SSI', 'NAUI'];
                    ?>
                    <span class="badge bg-primary"><?= htmlspecialchars($primaryOrg) ?>
                        <?= htmlspecialchars($certLevel) ?></span>
                    <?php foreach ($secondaryCerts as $cert): ?>
                        <?php if (trim($cert)): ?>
                            <span class="badge bg-info"><?= htmlspecialchars(trim($cert)) ?> Certified</span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="row mt-4 pt-3 border-top border-secondary">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 small text-light opacity-75">
                    &copy; <?= date('Y') ?> <?= htmlspecialchars($theme['business_name'] ?? 'Nautilus Dive Shop') ?>.
                    All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <ul class="list-inline mb-0 small">
                    <li class="list-inline-item"><a href="/privacy"
                            class="text-light text-decoration-none opacity-75">Privacy Policy</a></li>
                    <li class="list-inline-item">|</li>
                    <li class="list-inline-item"><a href="/terms"
                            class="text-light text-decoration-none opacity-75">Terms of Service</a></li>
                    <li class="list-inline-item">|</li>
                    <li class="list-inline-item"><a href="/sitemap"
                            class="text-light text-decoration-none opacity-75">Sitemap</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<style>
    .hover-opacity-100:hover {
        opacity: 1 !important;
    }
</style>