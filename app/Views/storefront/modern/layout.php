<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Shop') ?> -
        <?= htmlspecialchars($branding['company_name'] ?? 'Nautilus Dive Shop') ?>
    </title>

    <!-- Favicon -->
    <?php if (!empty($branding['favicon_url'])): ?>
        <link rel="icon" href="<?= htmlspecialchars($branding['favicon_url']) ?>">
    <?php endif; ?>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color:
                <?= $branding['primary_color'] ?? '#1976d2' ?>
            ;
            --secondary-color:
                <?= $branding['secondary_color'] ?? '#dc004e' ?>
            ;
            --accent-color:
                <?= $branding['accent_color'] ?? '#f50057' ?>
            ;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        /* Header */
        .top-bar {
            background: #f8f9fa;
            padding: 8px 0;
            font-size: 0.875rem;
            border-bottom: 1px solid #dee2e6;
        }

        .navbar-brand img {
            max-height: 50px;
            height: auto;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .nav-link {
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: color-mix(in srgb, var(--primary-color) 90%, black);
            border-color: color-mix(in srgb, var(--primary-color) 90%, black);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        /* Product Cards */
        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .product-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .product-card .card-body {
            padding: 1.25rem;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        /* Categories */
        .category-card {
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            height: 200px;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .category-card:hover {
            transform: scale(1.05);
        }

        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .category-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
            color: white;
            padding: 20px;
        }

        /* Cart Badge */
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--secondary-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 40px 0 20px;
            margin-top: 80px;
        }

        .footer a {
            color: #ecf0f1;
            text-decoration: none;
        }

        .footer a:hover {
            color: var(--primary-color);
        }

        /* Search Bar */
        .search-bar {
            max-width: 600px;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: none;
            padding: 0;
            margin-bottom: 2rem;
        }

        /* Utilities */
        .text-primary-custom {
            color: var(--primary-color) !important;
        }

        .bg-primary-custom {
            background-color: var(--primary-color) !important;
        }

        .border-primary-custom {
            border-color: var(--primary-color) !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2rem;
            }

            .product-card img {
                height: 200px;
            }
        }
    </style>

    <?php if (!empty($branding['custom_css'])): ?>
        <style>
            <?= $branding['custom_css'] ?>
        </style>
    <?php endif; ?>
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span><i class="bi bi-telephone"></i>
                        <?= htmlspecialchars($branding['phone'] ?? $branding['business_phone'] ?? '817-406-4080') ?></span>
                    <span class="ms-3"><i class="bi bi-envelope"></i>
                        <?= htmlspecialchars($branding['email'] ?? $branding['business_email'] ?? 'info@nautilus.local') ?></span>
                </div>
                <div class="col-md-6 text-md-end">
                    <?php if (isset($_SESSION['customer_id'])): ?>
                        <a href="/portal/dashboard" class="text-decoration-none"><i class="bi bi-person-circle"></i> My
                            Account</a>
                        <a href="/portal/logout" class="text-decoration-none ms-3"><i class="bi bi-box-arrow-right"></i>
                            Logout</a>
                    <?php else: ?>
                        <a href="/login" class="text-decoration-none"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                        <a href="/register" class="text-decoration-none ms-3"><i class="bi bi-person-plus"></i> Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <?php if (!empty($branding['logo_url'])): ?>
                    <img src="<?= htmlspecialchars($branding['logo_url']) ?>"
                        alt="<?= htmlspecialchars($branding['company_name'] ?? 'Logo') ?>">
                <?php else: ?>
                    <h3 class="mb-0"><?= htmlspecialchars($branding['company_name'] ?? 'Nautilus Dive Shop') ?></h3>
                <?php endif; ?>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
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
                        <a class="nav-link" href="/rentals">Rentals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact">Contact</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a href="/cart" class="btn btn-outline-primary position-relative">
                            <i class="bi bi-cart3"></i> Cart
                            <span class="cart-badge" id="cart-count">0</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><?= htmlspecialchars($branding['company_name'] ?? 'Nautilus Dive Shop') ?></h5>
                    <p>Your trusted partner for diving equipment, training, and adventures.</p>
                    <div class="social-links">
                        <a href="#" class="me-3"><i class="bi bi-facebook fs-4"></i></a>
                        <a href="#" class="me-3"><i class="bi bi-instagram fs-4"></i></a>
                        <a href="#" class="me-3"><i class="bi bi-twitter fs-4"></i></a>
                        <a href="#"><i class="bi bi-youtube fs-4"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4">
                    <h6>Shop</h6>
                    <ul class="list-unstyled">
                        <li><a href="/shop">All Products</a></li>
                        <li><a href="/shop?category=regulators">Regulators</a></li>
                        <li><a href="/shop?category=bcds">BCDs</a></li>
                        <li><a href="/shop?category=wetsuits">Wetsuits</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h6>Learn</h6>
                    <ul class="list-unstyled">
                        <li><a href="/courses">Courses</a></li>
                        <li><a href="/courses/open-water">Open Water</a></li>
                        <li><a href="/courses/advanced">Advanced</a></li>
                        <li><a href="/courses/rescue">Rescue Diver</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h6>Company</h6>
                    <ul class="list-unstyled">
                        <li><a href="/about">About Us</a></li>
                        <li><a href="/contact">Contact</a></li>
                        <li><a href="/faq">FAQ</a></li>
                        <li><a href="/blog">Blog</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h6>Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="/shipping">Shipping</a></li>
                        <li><a href="/returns">Returns</a></li>
                        <li><a href="/privacy">Privacy Policy</a></li>
                        <li><a href="/terms">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center">
                <p class="mb-0">&copy; <?= date('Y') ?>
                    <?= htmlspecialchars($branding['company_name'] ?? 'Nautilus Dive Shop') ?>. All rights reserved.
                </p>
                <p class="small mt-2">Powered by Nautilus v3.0</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Update cart count
        function updateCartCount() {
            fetch('/cart/count')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('cart-count').textContent = data.count || 0;
                })
                .catch(err => console.error('Error updating cart:', err));
        }

        // Add to cart
        function addToCart(productId, quantity = 1) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            fetch('/cart/add', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount();
                        showToast('Product added to cart!', 'success');
                    } else {
                        showToast(data.error || 'Failed to add to cart', 'error');
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showToast('An error occurred', 'error');
                });
        }

        // Toast notification
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : 'success'} position-fixed top-0 end-0 m-3`;
            toast.style.zIndex = '9999';
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            updateCartCount();

            // Manually initialize navbar toggler if needed
            const navLinks = document.querySelectorAll('.nav-link');
            const menuToggle = document.getElementById('navbarNav');
            if (menuToggle) {
                const bsCollapse = new bootstrap.Collapse(menuToggle, { toggle: false });

                // Close menu when a link is clicked
                navLinks.forEach((l) => {
                    l.addEventListener('click', () => {
                        if (menuToggle.classList.contains('show')) {
                            bsCollapse.hide();
                        }
                    })
                });
            }
        });
    </script>

    <?= $extra_scripts ?? '' ?>
</body>

</html>