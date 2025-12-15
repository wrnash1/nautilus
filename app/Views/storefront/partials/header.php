<header class="storefront-header" style="background-color: var(--header-bg);">
    <!-- Top Bar (optional) -->
    <div class="top-bar bg-dark text-white py-2 d-none d-md-block">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small>
                        <?php if ($settings->get('contact_phone')): ?>
                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($settings->get('contact_phone')) ?>
                        <?php endif; ?>
                        <?php if ($settings->get('contact_email')): ?>
                        <span class="ms-3">
                            <i class="bi bi-envelope"></i> <?= htmlspecialchars($settings->get('contact_email')) ?>
                        </span>
                        <?php endif; ?>
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <?php if (!empty($socialLinks)): ?>
                    <?php foreach ($socialLinks as $platform => $url): ?>
                        <?php if ($url): ?>
                        <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="text-white me-2" title="<?= ucfirst($platform) ?>">
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
    <nav class="navbar navbar-expand-lg glass-nav" style="<?= ($theme['header_style'] ?? 'solid') === 'sticky' ? 'position: sticky; top: 0; z-index: 1000;' : '' ?>">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand font-heading" href="/">
                <?php if (!empty($logo['file_path'])): ?>
                <img src="<?= htmlspecialchars($logo['file_path']) ?>"
                     alt="<?= htmlspecialchars($storeName ?? 'Logo') ?>"
                     height="50">
                <?php else: ?>
                <h3 class="mb-0 gradient-text d-flex align-items-center gap-2" style="font-family: var(--font-heading);">
                    <i class="bi bi-water text-primary"></i> <?= htmlspecialchars($storeName ?? 'Nautilus') ?>
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
                    <?php if (!empty($headerMenu)): ?>
                    <?php foreach ($headerMenu as $item): ?>
                    <li class="nav-item <?= !empty($item['children']) ? 'dropdown' : '' ?>">
                        <?php if (!empty($item['children'])): ?>
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <?php if ($item['icon_class']): ?><i class="<?= htmlspecialchars($item['icon_class']) ?>"></i> <?php endif; ?>
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($item['children'] as $child): ?>
                            <li>
                                <a class="dropdown-item" href="<?= htmlspecialchars($child['url']) ?>" target="<?= htmlspecialchars($child['link_target']) ?>">
                                    <?= htmlspecialchars($child['label']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <a class="nav-link" href="<?= htmlspecialchars($item['url']) ?>" target="<?= htmlspecialchars($item['link_target']) ?>">
                            <?php if ($item['icon_class']): ?><i class="<?= htmlspecialchars($item['icon_class']) ?>"></i> <?php endif; ?>
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </ul>

                <!-- Right Side Icons -->
                <div class="d-flex align-items-center">
                    <?php if ($theme['show_search_bar'] ?? true): ?>
                    <form action="/shop" method="GET" class="d-flex me-3">
                        <input type="search" name="q" class="form-control form-control-sm" placeholder="Search..." style="width: 150px;">
                    </form>
                    <?php endif; ?>

                    <?php if ($theme['show_account_icon'] ?? true): ?>
                    <a href="/account" class="text-decoration-none me-3" style="color: var(--text-color);" title="Account">
                        <i class="bi bi-person fs-5"></i>
                    </a>
                    <?php endif; ?>

                    <?php if ($theme['show_cart_icon'] ?? true): ?>
                    <a href="/shop/cart" class="text-decoration-none position-relative" style="color: var(--text-color);" title="Shopping Cart">
                        <i class="bi bi-cart3 fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">
                            <?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>
                        </span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>
