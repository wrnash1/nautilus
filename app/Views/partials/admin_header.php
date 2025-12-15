    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container-fluid">
            <!-- Sidebar Toggle (Mobile) -->
            <button class="navbar-toggler me-2 d-md-none" type="button" id="mobileSidebarToggle">
                <span class="navbar-toggler-icon"></span>
            </button>

            <span class="navbar-brand mb-0 h1 d-none d-md-block">
                <?php if (!empty($brandingSettings['company_logo_small_path'])): ?>
                    <img src="<?= htmlspecialchars($brandingSettings['company_logo_small_path']) ?>"
                         alt="<?= htmlspecialchars($companyName) ?>"
                         style="height: 32px; width: auto; margin-right: 0.5rem; vertical-align: middle;">
                <?php else: ?>
                    <i class="bi bi-water" style="color: <?= htmlspecialchars($brandingSettings['brand_primary_color'] ?? '#0066CC') ?>"></i>
                <?php endif; ?>
                <?= htmlspecialchars($companyName) ?>
            </span>
            
            <div class="d-flex align-items-center ms-auto">
                <a href="/" target="_blank" class="btn btn-outline-primary btn-sm me-3 d-none d-md-inline-block">
                    <i class="bi bi-eye"></i> View Store
                </a>

                <!-- Language Switcher -->
                <?php if(file_exists(BASE_PATH . '/app/Views/components/language_switcher.php')) require BASE_PATH . '/app/Views/components/language_switcher.php'; ?>

                <div class="dropdown ms-3">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                            <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <span class="d-none d-md-inline">
                            <?= htmlspecialchars($user['first_name'] ?? 'User') ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                        <li><span class="dropdown-item-text text-muted small"><?= htmlspecialchars($user['role_name'] ?? 'Unknown') ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/store/profile"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="/store/admin/settings/general"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="/store/logout">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
