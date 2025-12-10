<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $company = getCompanyInfo();
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($company['name']) ?> - Professional dive shop offering courses, equipment, and dive trips">
    <title><?= $pageTitle ?? 'Welcome' ?> - <?= htmlspecialchars($company['name']) ?></title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="<?= htmlspecialchars($company['primary_color']) ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars($company['name']) ?>">
    
    <!-- Favicons -->
    <?php if ($company['favicon']): ?>
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($company['favicon']) ?>">
    <?php else: ?>
    <link rel="icon" type="image/svg+xml" href="/favicon.ico">
    <?php endif; ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/modern-theme.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: <?= htmlspecialchars($company['primary_color']) ?>;
            --secondary-color: <?= htmlspecialchars($company['secondary_color']) ?>;
        }

        /* Public Navigation */
        .public-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }

        .public-navbar .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .public-navbar .nav-link {
            color: #333;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: color 0.2s;
        }

        .public-navbar .nav-link:hover {
            color: var(--primary-color);
        }

        .public-navbar .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
        }

        .hero-section h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .hero-section p {
            font-size: 1.25rem;
            opacity: 0.9;
        }

        /* Footer */
        .public-footer {
            background: #212529;
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }

        .public-footer a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
        }

        .public-footer a:hover {
            color: white;
        }
    </style>
</head>
<body>
    <!-- Public Navigation -->
    <nav class="navbar navbar-expand-lg public-navbar">
        <div class="container">
            <a class="navbar-brand" href="/">
                <?php if ($company['logo_small']): ?>
                    <img src="<?= htmlspecialchars($company['logo_small']) ?>" alt="<?= htmlspecialchars($company['name']) ?>" style="height: 40px;">
                <?php else: ?>
                    <i class="bi bi-water"></i> <?= htmlspecialchars($company['name']) ?>
                <?php endif; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="publicNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/shop">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/courses">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/trips">Trips</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact">Contact</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a class="btn btn-outline-primary btn-sm" href="/account/login">
                            <i class="bi bi-person"></i> Customer Login
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary btn-sm" href="/store/login">
                            <i class="bi bi-shield-lock"></i> Staff Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash_error'])): ?>
    <div class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Main Content -->
    <?php if (isset($content)) echo $content; ?>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><?= htmlspecialchars($company['name']) ?></h5>
                    <p class="text-muted">
                        <?= htmlspecialchars($company['address']) ?><br>
                        <?= htmlspecialchars($company['city']) ?>, <?= htmlspecialchars($company['state']) ?> <?= htmlspecialchars($company['zip']) ?>
                    </p>
                    <p class="text-muted">
                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($company['phone']) ?><br>
                        <i class="bi bi-envelope"></i> <?= htmlspecialchars($company['email']) ?>
                    </p>
                    
                    <!-- Social Media Links -->
                    <h6 class="mt-4">Follow Us</h6>
                    <div class="social-links">
                        <a href="#" class="text-white me-3" title="Facebook"><i class="bi bi-facebook fs-4"></i></a>
                        <a href="#" class="text-white me-3" title="Instagram"><i class="bi bi-instagram fs-4"></i></a>
                        <a href="#" class="text-white me-3" title="Twitter"><i class="bi bi-twitter fs-4"></i></a>
                        <a href="#" class="text-white me-3" title="YouTube"><i class="bi bi-youtube fs-4"></i></a>
                        <a href="#" class="text-white" title="LinkedIn"><i class="bi bi-linkedin fs-4"></i></a>
                    </div>
                    <small class="text-muted d-block mt-2">Social links can be configured in admin settings</small>
                </div>
                <div class="col-md-2 mb-4">
                    <h6>Shop</h6>
                    <ul class="list-unstyled">
                        <li><a href="/shop">All Products</a></li>
                        <li><a href="/shop?category=gear">Dive Gear</a></li>
                        <li><a href="/shop?category=accessories">Accessories</a></li>
                        <li><a href="/shop?category=apparel">Apparel</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h6>Learn</h6>
                    <ul class="list-unstyled">
                        <li><a href="/courses">All Courses</a></li>
                        <li><a href="/courses?level=beginner">Beginner</a></li>
                        <li><a href="/courses?level=advanced">Advanced</a></li>
                        <li><a href="/courses?level=specialty">Specialty</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h6>Travel</h6>
                    <ul class="list-unstyled">
                        <li><a href="/trips">All Trips</a></li>
                        <li><a href="/trips?type=local">Local Dives</a></li>
                        <li><a href="/trips?type=liveaboard">Liveaboards</a></li>
                        <li><a href="/trips?type=international">International</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h6>Account</h6>
                    <ul class="list-unstyled">
                        <li><a href="/account/login">Customer Login</a></li>
                        <li><a href="/account/register">Register</a></li>
                        <li><a href="/account">My Account</a></li>
                        <li><a href="/store/login">Staff Login</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-muted mb-0">&copy; <?= date('Y') ?> <?= htmlspecialchars($company['name']) ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="text-muted mb-0">
                        Powered by <a href="https://github.com/yourusername/nautilus" target="_blank">Nautilus</a> - Open Source Dive Shop Management
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/notifications.js"></script>
    <script src="/assets/js/pwa-installer.js"></script>
    
    <?php if (isset($additionalJs)) echo $additionalJs; ?>
</body>
</html>
