<header class="storefront-header" style="background-color: var(--header-bg);">
    <!-- Top Bar (optional) -->
    <div class="top-bar bg-dark text-white py-2 d-none d-md-block">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small>
                        <?php if (!empty($theme['business_phone'])): ?>
                            <i class="bi bi-telephone"></i> <?= htmlspecialchars($theme['business_phone']) ?>
                        <?php endif; ?>
                        <?php if (!empty($theme['business_email'])): ?>
                            <span class="ms-3">
                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($theme['business_email']) ?>
                            </span>
                        <?php endif; ?>
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <?php if (!empty($socialLinks)): ?>
                        <?php foreach ($socialLinks as $platform => $url): ?>
                            <?php if ($url): ?>
                                <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="text-white me-2"
                                    title="<?= ucfirst($platform) ?>">
                                    <i class="bi bi-<?= $platform ?>"></i>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg glass-nav"
        style="<?= ($theme['header_style'] ?? 'solid') === 'sticky' ? 'position: sticky; top: 0; z-index: 1000;' : '' ?>">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand font-heading" href="/">
                <?php if (!empty($theme['logo_path'])): ?>
                    <img src="<?= htmlspecialchars($theme['logo_path']) ?>"
                        alt="<?= htmlspecialchars($theme['business_name'] ?? 'Logo') ?>" height="50">
                <?php else: ?>
                    <h3 class="mb-0 gradient-text d-flex align-items-center gap-2"
                        style="font-family: var(--font-heading);">
                        <i class="bi bi-water text-primary"></i>
                        <?= htmlspecialchars($theme['business_name'] ?? 'Nautilus Dive Shop') ?>
                    </h3>
                <?php endif; ?>
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="/shop">Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="/courses">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="/trips">Dive Trips</a></li>
                    <li class="nav-item"><a class="nav-link" href="/rentals">Rentals</a></li>
                    <li class="nav-item"><a class="nav-link" href="/services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="/memberships">Memberships</a></li>
                </ul>

                <!-- Portal Buttons -->
                <div class="d-flex align-items-center gap-2">
                    <a href="/account/login" class="btn btn-primary btn-sm">
                        <i class="bi bi-person"></i> Customer Portal
                    </a>
                    <a href="/store/login" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-person-badge"></i> Staff
                    </a>
                </div>
            </div>

            <!-- Right Side Icons -->
            <div class="d-flex align-items-center">
                <?php if ($theme['show_search_bar'] ?? true): ?>
                    <form action="/shop" method="GET" class="d-flex me-3">
                        <input type="search" name="q" class="form-control form-control-sm" placeholder="Search..."
                            style="width: 150px;">
                    </form>
                <?php endif; ?>

                <?php if ($theme['show_account_icon'] ?? true): ?>
                    <a href="/account" class="text-decoration-none me-3" style="color: var(--text-color);" title="Account">
                        <i class="bi bi-person fs-5"></i>
                    </a>
                <?php endif; ?>

                <?php if ($theme['show_cart_icon'] ?? true): ?>
                    <a href="/shop/cart" class="text-decoration-none position-relative" style="color: var(--text-color);"
                        title="Shopping Cart">
                        <i class="bi bi-cart3 fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            id="cart-count">
                            <?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>
                        </span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </nav>
</header>