<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['business_name'] ?? 'Nautilus Dive Shop') ?> - Explore the Depths</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/storefront.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        /* Top Header Bar */
        .top-header {
            background: linear-gradient(135deg, #0066cc 0%, #004c99 100%);
            color: white;
            padding: 10px 0;
            font-size: 14px;
        }

        .top-header a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            transition: opacity 0.3s;
        }

        .top-header a:hover {
            opacity: 0.8;
        }

        /* Main Navigation */
        .main-nav {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-brand {
            font-size: 28px;
            font-weight: bold;
            color: #0066cc;
            text-decoration: none;
        }

        .nav-brand i {
            font-size: 32px;
            margin-right: 10px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .nav-links a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            font-size: 16px;
        }

        .nav-links a:hover {
            color: #0066cc;
        }

        .portal-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-portal {
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid;
        }

        .btn-customer {
            background: linear-gradient(135deg, #0066cc, #0052a3);
            color: white;
            border-color: #0066cc;
        }

        .btn-customer:hover {
            background: linear-gradient(135deg, #0052a3, #003d7a);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,102,204,0.3);
            color: white;
        }

        .btn-staff {
            background: white;
            color: #0066cc;
            border-color: #0066cc;
        }

        .btn-staff:hover {
            background: #0066cc;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,102,204,0.3);
        }

        /* Hero Carousel */
        .hero-carousel {
            position: relative;
            height: 600px;
            overflow: hidden;
        }

        .carousel-item {
            height: 600px;
            position: relative;
        }

        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            animation: zoomIn 20s ease infinite;
        }

        @keyframes zoomIn {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .carousel-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.6));
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .carousel-content h1 {
            font-size: 56px;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.5);
            animation: fadeInUp 1s ease;
        }

        .carousel-content p {
            font-size: 24px;
            margin-bottom: 30px;
            text-shadow: 1px 1px 5px rgba(0,0,0,0.5);
            animation: fadeInUp 1s ease 0.2s both;
        }

        .carousel-btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #0066cc, #0052a3);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 18px;
            transition: all 0.3s;
            animation: fadeInUp 1s ease 0.4s both;
        }

        .carousel-btn:hover {
            background: linear-gradient(135deg, #0052a3, #003d7a);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,102,204,0.4);
            color: white;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Service Boxes */
        .services-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 42px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .section-title p {
            font-size: 18px;
            color: #666;
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .service-box {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .service-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        .service-image {
            height: 250px;
            overflow: hidden;
            position: relative;
        }

        .service-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .service-box:hover .service-image img {
            transform: scale(1.1);
        }

        .service-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0,102,204,0.9);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .service-content {
            padding: 30px;
        }

        .service-content h3 {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .service-content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .service-link {
            color: #0066cc;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .service-link:hover {
            gap: 15px;
        }

        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, #0066cc 0%, #004c99 100%);
            color: white;
            padding: 60px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            text-align: center;
        }

        .stat-item i {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 18px;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta-section {
            padding: 80px 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100"><path fill="%230066cc" opacity="0.05" d="M0,50 Q250,0 500,50 T1000,50 L1000,100 L0,100 Z"/></svg>') repeat-x bottom;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .cta-section p {
            font-size: 20px;
            color: #666;
            margin-bottom: 40px;
        }

        /* Footer */
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 60px 0 20px 0;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer h4 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #0066cc;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: #0066cc;
        }

        .footer-bottom {
            border-top: 1px solid #333;
            padding-top: 20px;
            text-align: center;
            color: #999;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: #333;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: #0066cc;
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero-carousel,
            .carousel-item {
                height: 400px;
            }

            .carousel-content h1 {
                font-size: 36px;
            }

            .carousel-content p {
                font-size: 18px;
            }

            .service-grid {
                grid-template-columns: 1fr;
            }
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
                    <a href="/shop">Shop</a>
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

    <!-- Hero Carousel -->
    <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach ($data['carousel_slides'] ?? [] as $index => $slide): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $index ?>"
                        class="<?= $index === 0 ? 'active' : '' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php if (!empty($data['carousel_slides'])): ?>
                <?php foreach ($data['carousel_slides'] as $index => $slide): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="<?= htmlspecialchars($slide['image_url']) ?>" alt="<?= htmlspecialchars($slide['title']) ?>">
                        <div class="carousel-overlay">
                            <div class="carousel-content">
                                <h1><?= htmlspecialchars($slide['title']) ?></h1>
                                <p><?= htmlspecialchars($slide['description']) ?></p>
                                <?php if (!empty($slide['button_link'])): ?>
                                    <a href="<?= htmlspecialchars($slide['button_link']) ?>" class="carousel-btn">
                                        <?= htmlspecialchars($slide['button_text'] ?? 'Learn More') ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default Slides -->
                <div class="carousel-item active">
                    <img src="https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=1920&h=600&fit=crop" alt="Scuba Diving">
                    <div class="carousel-overlay">
                        <div class="carousel-content">
                            <h1>Explore the Underwater World</h1>
                            <p>Begin your diving adventure with professional training and equipment</p>
                            <a href="/courses" class="carousel-btn">Browse Courses</a>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=1920&h=600&fit=crop" alt="Dive Trip">
                    <div class="carousel-overlay">
                        <div class="carousel-content">
                            <h1>Unforgettable Dive Trips</h1>
                            <p>Join us on exotic diving destinations around the world</p>
                            <a href="/trips" class="carousel-btn">View Trips</a>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=1920&h=600&fit=crop" alt="Equipment">
                    <div class="carousel-overlay">
                        <div class="carousel-content">
                            <h1>Premium Diving Equipment</h1>
                            <p>Shop the latest gear from top brands</p>
                            <a href="/shop" class="carousel-btn">Shop Now</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <!-- Services Section -->
    <section class="services-section">
        <div class="container">
            <div class="section-title">
                <h2>What We Offer</h2>
                <p>Everything you need for your diving journey</p>
            </div>
            <div class="service-grid">
                <?php
                $defaultServices = [
                    [
                        'icon' => 'fa-graduation-cap',
                        'title' => 'Scuba Diving Courses',
                        'description' => 'Professional PADI, SSI, and NAUI certification courses from beginner to instructor level.',
                        'image' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=400&h=300&fit=crop',
                        'link' => '/courses'
                    ],
                    [
                        'icon' => 'fa-ship',
                        'title' => 'Dive Trips & Charters',
                        'description' => 'Explore breathtaking dive sites locally and around the world with our guided trips.',
                        'image' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=400&h=300&fit=crop',
                        'link' => '/trips'
                    ],
                    [
                        'icon' => 'fa-hotel',
                        'title' => 'Live-Aboard Vacations',
                        'description' => 'Multi-day diving adventures on luxury live-aboard vessels to remote locations.',
                        'image' => 'https://images.unsplash.com/photo-1540202404-a2f2f549e5e7?w=400&h=300&fit=crop',
                        'link' => '/liveaboard'
                    ],
                    [
                        'icon' => 'fa-umbrella-beach',
                        'title' => 'Resort Packages',
                        'description' => 'All-inclusive diving resort packages with accommodation and unlimited dives.',
                        'image' => 'https://images.unsplash.com/photo-1589553416260-f586c8f1514f?w=400&h=300&fit=crop',
                        'link' => '/resorts'
                    ],
                    [
                        'icon' => 'fa-tools',
                        'title' => 'Equipment Repair',
                        'description' => 'Professional maintenance and repair services for all diving equipment brands.',
                        'image' => 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=400&h=300&fit=crop',
                        'link' => '/services/repair'
                    ],
                    [
                        'icon' => 'fa-heart-pulse',
                        'title' => 'First Aid & CPR Training',
                        'description' => 'Emergency First Response and CPR courses for divers and non-divers.',
                        'image' => 'https://images.unsplash.com/photo-1603398938378-e54eab446dde?w=400&h=300&fit=crop',
                        'link' => '/courses/first-aid'
                    ]
                ];

                $services = $data['service_boxes'] ?? $defaultServices;
                foreach ($services as $service):
                ?>
                    <a href="<?= htmlspecialchars($service['link']) ?>" class="service-box">
                        <div class="service-image">
                            <img src="<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['title']) ?>">
                            <div class="service-icon">
                                <i class="fas <?= htmlspecialchars($service['icon']) ?>"></i>
                            </div>
                        </div>
                        <div class="service-content">
                            <h3><?= htmlspecialchars($service['title']) ?></h3>
                            <p><?= htmlspecialchars($service['description']) ?></p>
                            <span class="service-link">
                                Learn More <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <div class="stat-number">5,000+</div>
                    <div class="stat-label">Certified Divers</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-trophy"></i>
                    <div class="stat-number">25+</div>
                    <div class="stat-label">Years Experience</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-star"></i>
                    <div class="stat-number">4.9/5</div>
                    <div class="stat-label">Customer Rating</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-globe"></i>
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Dive Destinations</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Start Your Diving Adventure?</h2>
            <p>Join thousands of certified divers and explore the underwater world</p>
            <a href="/account/register" class="carousel-btn">Create Account</a>
        </div>
    </section>

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
