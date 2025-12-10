<footer class="storefront-footer mt-5" style="background-color: var(--footer-bg); color: #ffffff;">
    <?php
    $footerStyle = $theme['footer_style'] ?? 'detailed';
    $showNewsletter = $theme['show_newsletter_signup'] ?? true;
    $showSocial = $theme['show_social_links'] ?? true;
    $showPaymentIcons = $theme['show_payment_icons'] ?? true;
    ?>

    <?php if ($footerStyle === 'detailed' || $footerStyle === 'mega'): ?>
    <div class="py-5">
        <div class="container">
            <div class="row">
                <!-- About / Contact -->
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3"><?= htmlspecialchars($storeName ?? 'Nautilus Dive Shop') ?></h5>
                    <p><?= htmlspecialchars($storeTagline ?? '') ?></p>
                    <?php if ($settings->get('store_address')): ?>
                    <p class="mb-2">
                        <i class="bi bi-geo-alt"></i>
                        <?= nl2br(htmlspecialchars($settings->get('store_address'))) ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($settings->get('contact_phone')): ?>
                    <p class="mb-2">
                        <i class="bi bi-telephone"></i>
                        <?= htmlspecialchars($settings->get('contact_phone')) ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($settings->get('contact_email')): ?>
                    <p class="mb-2">
                        <i class="bi bi-envelope"></i>
                        <?= htmlspecialchars($settings->get('contact_email')) ?>
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Quick Links -->
                <div class="col-md-2 mb-4">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <?php if (!empty($footerMenu)): ?>
                        <?php foreach ($footerMenu as $item): ?>
                        <li class="mb-2">
                            <a href="<?= htmlspecialchars($item['url']) ?>" class="text-white-50 text-decoration-none">
                                <?= htmlspecialchars($item['label']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div class="col-md-2 mb-4">
                    <h6 class="mb-3">Customer Service</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/contact" class="text-white-50 text-decoration-none">Contact Us</a></li>
                        <li class="mb-2"><a href="/account/orders" class="text-white-50 text-decoration-none">Track Order</a></li>
                        <li class="mb-2"><a href="/faq" class="text-white-50 text-decoration-none">FAQs</a></li>
                        <li class="mb-2"><a href="/shipping" class="text-white-50 text-decoration-none">Shipping Info</a></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <?php if ($showNewsletter): ?>
                <div class="col-md-4 mb-4">
                    <h6 class="mb-3">Stay Connected</h6>
                    <p class="text-white-50">Subscribe to get special offers, free giveaways, and once-in-a-lifetime deals.</p>
                    <form action="/newsletter/subscribe" method="POST" class="mb-3">
                        <div class="input-group">
                            <input type="email" name="email" class="form-control" placeholder="Your email" required>
                            <button type="submit" class="btn btn-primary">Subscribe</button>
                        </div>
                    </form>

                    <?php if ($showSocial && !empty($socialLinks)): ?>
                    <div class="social-links">
                        <?php foreach ($socialLinks as $platform => $url): ?>
                            <?php if ($url): ?>
                            <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="text-white me-3 fs-4" title="<?= ucfirst($platform) ?>">
                                <i class="bi bi-<?= $platform ?>"></i>
                            </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bottom Bar -->
    <div class="border-top border-secondary py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <small class="text-white-50">
                        &copy; <?= date('Y') ?> <?= htmlspecialchars($storeName ?? 'Nautilus Dive Shop') ?>. All rights reserved.
                    </small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <?php if ($showPaymentIcons): ?>
                    <small class="text-white-50">
                        <i class="bi bi-credit-card fs-5 me-2"></i>
                        <i class="bi bi-wallet2 fs-5 me-2"></i>
                        <i class="bi bi-paypal fs-5 me-2"></i>
                        <span class="ms-2">Secure Payments</span>
                    </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</footer>
