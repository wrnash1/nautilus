<?php ob_start(); ?>

<!-- Hero Section -->
<section class="position-relative overflow-hidden py-5 bg-primary text-white" style="background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-800) 100%);">
    <div class="container position-relative z-1 text-center">
        <h1 class="display-4 fw-bold font-heading mb-3">Start Your Adventure</h1>
        <p class="lead text-light opacity-75 mb-0">Explore the underwater world with our professional diving courses.</p>
    </div>
    <!-- Decorative wave SVG bottom -->
    <div class="position-absolute bottom-0 start-0 w-100 overflow-hidden" style="height: 60px;">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none" style="width: 100%; height: 100%; transform: rotate(180deg);">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="var(--body-bg)"></path>
        </svg>
    </div>
</section>

<!-- Courses Grid -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            
            <!-- Open Water Diver -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-lift transition-base">
                    <img src="https://images.unsplash.com/photo-1544551763-46a8723ba1f9?auto=format&fit=crop&q=80&w=800" class="card-img-top" alt="Open Water Diver" style="height: 200px; object-fit: cover;">
                    <div class="card-body d-flex flex-column p-4">
                        <h3 class="h5 font-heading fw-bold mb-2">Open Water Diver</h3>
                        <p class="text-muted small flex-grow-1">The world's most popular scuba course. Get certified to dive anywhere in the world.</p>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <span class="h5 text-primary mb-0 fw-bold">$399.00</span>
                            <a href="#" class="btn btn-primary btn-sm btn-modern rounded-pill px-4">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Open Water -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-lift transition-base">
                    <img src="https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?auto=format&fit=crop&q=80&w=800" class="card-img-top" alt="Advanced Open Water" style="height: 200px; object-fit: cover;">
                    <div class="card-body d-flex flex-column p-4">
                        <h3 class="h5 font-heading fw-bold mb-2">Advanced Open Water</h3>
                        <p class="text-muted small flex-grow-1">Advance your skills with 5 adventure dives including Deep and Navigation.</p>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <span class="h5 text-primary mb-0 fw-bold">$349.00</span>
                            <a href="#" class="btn btn-primary btn-sm btn-modern rounded-pill px-4">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rescue Diver -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-lift transition-base">
                    <img src="https://images.unsplash.com/photo-1588612143573-2a54972be3f5?auto=format&fit=crop&q=80&w=800" class="card-img-top" alt="Rescue Diver" style="height: 200px; object-fit: cover;">
                    <div class="card-body d-flex flex-column p-4">
                        <h3 class="h5 font-heading fw-bold mb-2">Rescue Diver</h3>
                        <p class="text-muted small flex-grow-1">Learn to prevent and manage problems in the water. Serious fun.</p>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <span class="h5 text-primary mb-0 fw-bold">$379.00</span>
                            <a href="#" class="btn btn-primary btn-sm btn-modern rounded-pill px-4">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Divemaster -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-lift transition-base">
                    <img src="https://images.unsplash.com/photo-1510662145379-13537db782dc?auto=format&fit=crop&q=80&w=800" class="card-img-top" alt="Divemaster" style="height: 200px; object-fit: cover;">
                    <div class="card-body d-flex flex-column p-4">
                        <h3 class="h5 font-heading fw-bold mb-2">Divemaster</h3>
                        <p class="text-muted small flex-grow-1">Go pro! The first level of professional training. Mentor other divers.</p>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <span class="h5 text-primary mb-0 fw-bold">$999.00</span>
                            <a href="#" class="btn btn-primary btn-sm btn-modern rounded-pill px-4">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/layouts/main.php'; ?>
