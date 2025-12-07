<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Shop') ?> | <?= htmlspecialchars($branding['business_name'] ?? 'Nautilus Dive Shop') ?></title>
    <meta name="description" content="<?= htmlspecialchars($store_settings['meta_description'] ?? 'Professional dive equipment, courses, and trips') ?>">

    <!-- Favicon -->
    <?php if (!empty($branding['favicon'])): ?>
    <link rel="icon" href="<?= htmlspecialchars($branding['favicon']) ?>">
    <?php endif; ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: <?= $branding['primary_color'] ?? '#0066cc' ?>;
            --secondary-color: <?= $branding['secondary_color'] ?? '#003d7a' ?>;
            --accent-color: <?= $branding['accent_color'] ?? '#ff6b35' ?>;
            --text-color: #333;
            --light-gray: #f8f9fa;
            --border-color: #e0e0e0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-top {
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 0;
            font-size: 0.875rem;
        }

        .header-main {
            padding: 1rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo img {
            height: 50px;
            width: auto;
        }

        .nav {
            display: flex;
            gap: 2rem;
            list-style: none;
            align-items: center;
        }

        .nav a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav a:hover {
            color: var(--primary-color);
        }

        .header-actions {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 0.5rem 2.5rem 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 25px;
            width: 250px;
            font-size: 0.875rem;
        }

        .search-box i {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .cart-icon {
            position: relative;
            color: var(--text-color);
            font-size: 1.5rem;
            cursor: pointer;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Hero Section */
        .hero {
            position: relative;
            height: 600px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .btn {
            display: inline-block;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--accent-color);
            color: white;
        }

        .btn-primary:hover {
            background: #ff5520;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255,107,53,0.3);
        }

        .btn-secondary {
            background: white;
            color: var(--primary-color);
            margin-left: 1rem;
        }

        .btn-secondary:hover {
            background: var(--light-gray);
        }

        /* Featured Categories */
        .section {
            padding: 5rem 0;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--secondary-color);
        }

        .section-header p {
            font-size: 1.125rem;
            color: #666;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .category-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
        }

        .category-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .category-image {
            height: 200px;
            background: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: var(--primary-color);
        }

        .category-content {
            padding: 1.5rem;
        }

        .category-content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .category-content p {
            color: #666;
            font-size: 0.875rem;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .product-image {
            height: 250px;
            background: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--accent-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .product-content {
            padding: 1.5rem;
        }

        .product-category {
            color: var(--primary-color);
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .product-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--secondary-color);
        }

        .product-title a {
            color: inherit;
            text-decoration: none;
        }

        .product-title a:hover {
            color: var(--primary-color);
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }

        .product-footer {
            display: flex;
            gap: 0.75rem;
        }

        .btn-add-cart {
            flex: 1;
            background: var(--primary-color);
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-add-cart:hover {
            background: var(--secondary-color);
        }

        .btn-wishlist {
            width: 45px;
            background: var(--light-gray);
            color: var(--text-color);
            padding: 0.75rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-wishlist:hover {
            background: var(--accent-color);
            color: white;
        }

        /* Stats Section */
        .stats {
            background: var(--primary-color);
            color: white;
            padding: 4rem 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            font-size: 1.125rem;
            opacity: 0.9;
        }

        /* Footer */
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 4rem 0 2rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer h4 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .footer ul {
            list-style: none;
        }

        .footer ul li {
            margin-bottom: 0.75rem;
        }

        .footer a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer a:hover {
            color: white;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #333;
            color: #999;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav {
                display: none;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .section-header h2 {
                font-size: 2rem;
            }
        }

        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <i class="bi bi-telephone"></i> <?= htmlspecialchars($store_settings['phone'] ?? '(555) 123-4567') ?>
                    <span style="margin: 0 1rem;">|</span>
                    <i class="bi bi-envelope"></i> <?= htmlspecialchars($store_settings['email'] ?? 'info@nautilus.com') ?>
                </div>
                <div>
                    <a href="/customer/login" style="color: white; text-decoration: none; margin-left: 1rem;">
                        <i class="bi bi-person"></i> Account
                    </a>
                </div>
            </div>
        </div>

        <div class="header-main">
            <div class="container">
                <div class="header-content">
                    <a href="/" class="logo">
                        <?php if (!empty($branding['logo'])): ?>
                            <img src="<?= htmlspecialchars($branding['logo']) ?>" alt="Logo">
                        <?php else: ?>
                            <i class="bi bi-droplet-fill"></i>
                            <?= htmlspecialchars($branding['business_name'] ?? 'Nautilus Dive Shop') ?>
                        <?php endif; ?>
                    </a>

                    <nav>
                        <ul class="nav">
                            <li><a href="/">Home</a></li>
                            <li><a href="/shop">Shop</a></li>
                            <li><a href="/courses">Courses</a></li>
                            <li><a href="/trips">Trips</a></li>
                            <li><a href="/rentals">Rentals</a></li>
                            <li><a href="/about">About</a></li>
                            <li><a href="/contact">Contact</a></li>
                        </ul>
                    </nav>

                    <div class="header-actions">
                        <div class="search-box">
                            <form action="/shop" method="GET">
                                <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                <i class="bi bi-search"></i>
                            </form>
                        </div>

                        <a href="/cart" class="cart-icon">
                            <i class="bi bi-bag"></i>
                            <span class="cart-count" id="cart-count">0</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1><?= htmlspecialchars($store_settings['hero_title'] ?? 'Dive Into Adventure') ?></h1>
                <p><?= htmlspecialchars($store_settings['hero_subtitle'] ?? 'Premium dive equipment, expert courses, and unforgettable underwater experiences') ?></p>
                <div>
                    <a href="/shop" class="btn btn-primary">Shop Now</a>
                    <a href="/courses" class="btn btn-secondary">View Courses</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="section" style="background: var(--light-gray);">
        <div class="container">
            <div class="section-header">
                <h2>Shop by Category</h2>
                <p>Find exactly what you need for your next dive</p>
            </div>

            <div class="categories-grid">
                <?php
                $categoryIcons = [
                    'Regulators' => 'bi-speedometer',
                    'BCDs' => 'bi-shield-check',
                    'Masks & Fins' => 'bi-eye',
                    'Wetsuits' => 'bi-person',
                    'Computers' => 'bi-smartwatch',
                    'Accessories' => 'bi-gear'
                ];

                foreach ($categories ?? [] as $category):
                    if (($category['product_count'] ?? 0) == 0) continue;
                ?>
                <a href="/shop?category=<?= $category['id'] ?>" class="category-card">
                    <div class="category-image">
                        <i class="bi <?= $categoryIcons[$category['name']] ?? 'bi-box' ?>"></i>
                    </div>
                    <div class="category-content">
                        <h3><?= htmlspecialchars($category['name']) ?></h3>
                        <p><?= $category['product_count'] ?> products</p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Featured Products</h2>
                <p>Hand-picked gear from our collection</p>
            </div>

            <div class="products-grid">
                <?php foreach ($featured_products ?? [] as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['image']): ?>
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <i class="bi bi-image" style="font-size: 4rem; color: #ccc;"></i>
                        <?php endif; ?>

                        <?php if ($product['is_featured'] ?? false): ?>
                            <div class="product-badge">Featured</div>
                        <?php endif; ?>
                    </div>

                    <div class="product-content">
                        <div class="product-category">
                            <?= htmlspecialchars($product['category_name'] ?? 'Products') ?>
                        </div>
                        <div class="product-title">
                            <a href="/product/<?= $product['id'] ?>">
                                <?= htmlspecialchars($product['name']) ?>
                            </a>
                        </div>
                        <div class="product-price">
                            $<?= number_format($product['price'], 2) ?>
                        </div>
                        <div class="product-footer">
                            <button class="btn-add-cart" onclick="addToCart(<?= $product['id'] ?>)">
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                            <button class="btn-wishlist">
                                <i class="bi bi-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="text-align: center; margin-top: 3rem;">
                <a href="/shop" class="btn btn-primary">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>500+</h3>
                    <p>Products Available</p>
                </div>
                <div class="stat-item">
                    <h3>2,000+</h3>
                    <p>Happy Customers</p>
                </div>
                <div class="stat-item">
                    <h3>15+</h3>
                    <p>Years Experience</p>
                </div>
                <div class="stat-item">
                    <h3>50+</h3>
                    <p>Dive Courses</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h4><?= htmlspecialchars($branding['business_name'] ?? 'Nautilus Dive Shop') ?></h4>
                    <p style="color: #ccc; margin-bottom: 1rem;">Your trusted partner for diving equipment, training, and adventure.</p>
                    <div style="font-size: 1.5rem; margin-top: 1rem;">
                        <a href="#" style="margin-right: 1rem;"><i class="bi bi-facebook"></i></a>
                        <a href="#" style="margin-right: 1rem;"><i class="bi bi-instagram"></i></a>
                        <a href="#" style="margin-right: 1rem;"><i class="bi bi-youtube"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>

                <div>
                    <h4>Shop</h4>
                    <ul>
                        <li><a href="/shop?category=regulators">Regulators</a></li>
                        <li><a href="/shop?category=bcds">BCDs</a></li>
                        <li><a href="/shop?category=masks">Masks & Fins</a></li>
                        <li><a href="/shop?category=wetsuits">Wetsuits</a></li>
                        <li><a href="/shop?category=computers">Dive Computers</a></li>
                    </ul>
                </div>

                <div>
                    <h4>Learn</h4>
                    <ul>
                        <li><a href="/courses">All Courses</a></li>
                        <li><a href="/courses?type=beginner">Beginner Courses</a></li>
                        <li><a href="/courses?type=advanced">Advanced Courses</a></li>
                        <li><a href="/courses?type=specialty">Specialty Courses</a></li>
                        <li><a href="/instructors">Our Instructors</a></li>
                    </ul>
                </div>

                <div>
                    <h4>Contact</h4>
                    <ul>
                        <li><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($store_settings['address'] ?? '123 Ocean Ave, Miami, FL') ?></li>
                        <li><i class="bi bi-telephone"></i> <?= htmlspecialchars($store_settings['phone'] ?? '(555) 123-4567') ?></li>
                        <li><i class="bi bi-envelope"></i> <?= htmlspecialchars($store_settings['email'] ?? 'info@nautilus.com') ?></li>
                        <li><i class="bi bi-clock"></i> Mon-Sat: 9AM-6PM</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($branding['business_name'] ?? 'Nautilus Dive Shop') ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Cart JavaScript -->
    <script>
        // Update cart count on page load
        updateCartCount();

        function addToCart(productId) {
            const button = event.target.closest('.btn-add-cart');
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="spinner"></span>';
            button.disabled = true;

            fetch('/api/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.innerHTML = '<i class="bi bi-check2"></i> Added!';
                    button.style.background = '#28a745';
                    updateCartCount();

                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.style.background = '';
                        button.disabled = false;
                    }, 2000);
                } else {
                    alert(data.message || 'Error adding to cart');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding to cart');
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function updateCartCount() {
            fetch('/api/cart/count')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('cart-count').textContent = data.count || 0;
                })
                .catch(error => console.error('Error updating cart:', error));
        }
    </script>
</body>
</html>
