<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - <?= htmlspecialchars($data['business_name'] ?? 'Nautilus Dive Shop') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/storefront.css">
    <style>
        /* Reusing styles from index.php */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; background-color: #f8f9fa; }
        
        .top-header { background: linear-gradient(135deg, #0066cc 0%, #004c99 100%); color: white; padding: 10px 0; font-size: 14px; }
        .top-header a { color: white; text-decoration: none; margin: 0 15px; transition: opacity 0.3s; }
        .top-header a:hover { opacity: 0.8; }
        
        .main-nav { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 15px 0; position: sticky; top: 0; z-index: 1000; }
        .nav-brand { font-size: 28px; font-weight: bold; color: #0066cc; text-decoration: none; }
        .nav-links { display: flex; align-items: center; gap: 30px; }
        .nav-links a { color: #333; text-decoration: none; font-weight: 500; transition: color 0.3s; font-size: 16px; }
        .nav-links a:hover { color: #0066cc; }
        
        .portal-buttons { display: flex; gap: 10px; }
        .btn-portal { padding: 10px 25px; border-radius: 25px; text-decoration: none; font-weight: 600; transition: all 0.3s; border: 2px solid; }
        .btn-customer { background: linear-gradient(135deg, #0066cc, #0052a3); color: white; border-color: #0066cc; }
        .btn-customer:hover { background: linear-gradient(135deg, #0052a3, #003d7a); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,102,204,0.3); color: white; }
        .btn-staff { background: white; color: #0066cc; border-color: #0066cc; }
        .btn-staff:hover { background: #0066cc; color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,102,204,0.3); }

        /* Shop Specific Styles */
        .page-header {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=1920&h=400&fit=crop');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 { font-size: 48px; font-weight: bold; margin-bottom: 15px; }
        .page-header p { font-size: 20px; opacity: 0.9; }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .product-image {
            height: 250px;
            overflow: hidden;
            position: relative;
            background: #f1f1f1;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-category {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #666;
            margin-bottom: 5px;
        }

        .product-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .product-price {
            font-size: 20px;
            color: #0066cc;
            font-weight: bold;
            margin-top: auto;
        }

        .footer { background: #1a1a1a; color: white; padding: 60px 0 20px 0; margin-top: auto; }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; margin-bottom: 40px; }
        .footer h4 { font-size: 20px; margin-bottom: 20px; color: #0066cc; }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: #ccc; text-decoration: none; transition: color 0.3s; }
        .footer-links a:hover { color: #0066cc; }
        .footer-bottom { border-top: 1px solid #333; padding-top: 20px; text-align: center; color: #999; }
        .social-links { display: flex; gap: 15px; margin-top: 20px; }
        .social-links a { width: 40px; height: 40px; background: #333; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: all 0.3s; }
        .social-links a:hover { background: #0066cc; transform: translateY(-3px); }

        @media (max-width: 768px) {
            .nav-links { display: none; }
        }
    </style>
</head>
<body>
    <!-- Top Header Bar -->
    <div class="top-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-phone"></i> (555) 123-4567
                    <i class="fas fa-envelope ms-3"></i> info@nautilusdive.com
                </div>
                <div>
                    <a href="/about"><i class="fas fa-info-circle"></i> About Us</a>
                    <a href="/contact"><i class="fas fa-envelope"></i> Contact</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="main-nav">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="/" class="nav-brand">
                    <i class="fas fa-water"></i> <?= htmlspecialchars($data['business_name'] ?? 'Nautilus Dive Shop') ?>
                </a>
                <div class="nav-links d-none d-lg-flex">
                    <a href="/shop" style="color: #0066cc;">Shop</a>
                    <a href="/courses">Courses</a>
                    <a href="/trips">Dive Trips</a>
                    <a href="/rentals">Rentals</a>
                    <a href="/services">Services</a>
                </div>
                <div class="portal-buttons">
                    <a href="/account/login" class="btn-portal btn-customer">
                        <i class="fas fa-user"></i> Customer Portal
                    </a>
                    <a href="/login" class="btn-portal btn-staff">
                        <i class="fas fa-user-tie"></i> Staff
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>Shop Equipment</h1>
            <p>Premium gear for your underwater adventures</p>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="container">
        <div class="product-grid">
            <?php if (!empty($data['products'])): ?>
                <?php foreach ($data['products'] as $product): ?>
                    <a href="/shop/product/<?= $product['id'] ?>" class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted">
                                    <i class="fas fa-camera fa-3x"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?= htmlspecialchars($product['category'] ?? 'Equipment') ?></div>
                            <div class="product-title"><?= htmlspecialchars($product['name']) ?></div>
                            <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h3>No products found</h3>
                    <p class="text-muted">Check back soon for new inventory!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h4>About Nautilus</h4>
                    <p>Your premier destination for scuba diving training, equipment, and unforgettable underwater adventures since 1999.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div>
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="/shop">Shop Equipment</a></li>
                        <li><a href="/courses">Training Courses</a></li>
                        <li><a href="/trips">Dive Trips</a></li>
                        <li><a href="/rentals">Equipment Rentals</a></li>
                        <li><a href="/about">About Us</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Services</h4>
                    <ul class="footer-links">
                        <li><a href="/services/repair">Equipment Repair</a></li>
                        <li><a href="/services/fills">Air & Nitrox Fills</a></li>
                        <li><a href="/courses/first-aid">First Aid Training</a></li>
                        <li><a href="/liveaboard">Live-Aboard Trips</a></li>
                        <li><a href="/resorts">Resort Packages</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Contact Info</h4>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Ocean Drive, Beach City, FL 12345</li>
                        <li><i class="fas fa-phone"></i> (555) 123-4567</li>
                        <li><i class="fas fa-envelope"></i> info@nautilusdive.com</li>
                        <li><i class="fas fa-clock"></i> Mon-Sat: 9am-6pm, Sun: 10am-4pm</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <?= date('Y') ?> <?= htmlspecialchars($data['business_name'] ?? 'Nautilus Dive Shop') ?>. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
