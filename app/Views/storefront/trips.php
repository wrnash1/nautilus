<?php ob_start(); ?>

<!-- Hero Section -->
<section class="position-relative overflow-hidden py-5 bg-primary text-white" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
    <div class="container position-relative z-1 text-center">
        <h1 class="display-4 fw-bold font-heading mb-3">Upcoming Dive Trips</h1>
        <p class="lead text-light opacity-75 mb-0">Explore the world with our guided dive excursions.</p>
    </div>
</section>

<!-- Content -->
<section class="py-5">
    <div class="container">
        <div class="row g-5 justify-content-center">
            
            <!-- Sample Trip -->
            <div class="col-lg-10">
                <div class="card rounded-4 border-0 shadow-sm overflow-hidden hover-lift transition-base">
                    <div class="row g-0">
                        <div class="col-md-5">
                            <img src="https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&q=80" 
                                 class="img-fluid h-100 object-fit-cover min-h-250" alt="Cozumel">
                        </div>
                        <div class="col-md-7">
                            <div class="card-body p-4 p-lg-5 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="text-primary fw-bold text-uppercase small mb-2 tracking-wide">Mexico</h5>
                                    <h2 class="card-title font-heading fw-bold mb-3">Cozumel Drift Diving</h2>
                                    <p class="card-text text-muted mb-4">Join us for a week of spectacular drift diving in the crystal clear waters of Cozumel. Experience the thrill of flying underwater along the vibrant reefs.</p>
                                    <div class="d-flex align-items-center text-muted mb-4">
                                         <i class="bi bi-calendar-event me-2"></i> Oct 15 - Oct 22, 2025
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between border-top pt-4">
                                    <div class="d-flex flex-column">
                                        <span class="text-muted small">Starting from</span>
                                        <span class="h3 mb-0 text-gray-900 fw-bold">$1,299<span class="fs-6 fw-normal text-muted">/pp</span></span>
                                    </div>
                                    <button class="btn btn-primary btn-modern px-4 py-2">View Details</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
             <!-- Sample Trip 2 -->
             <div class="col-lg-10">
                <div class="card rounded-4 border-0 shadow-sm overflow-hidden hover-lift transition-base">
                    <div class="row g-0">
                        <div class="col-md-5">
                            <img src="https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&q=80" 
                                 class="img-fluid h-100 object-fit-cover min-h-250" alt="Bonaire">
                        </div>
                        <div class="col-md-7">
                            <div class="card-body p-4 p-lg-5 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="text-primary fw-bold text-uppercase small mb-2 tracking-wide">Caribbean</h5>
                                    <h2 class="card-title font-heading fw-bold mb-3">Bonaire Shore Diving</h2>
                                    <p class="card-text text-muted mb-4">Unlimited shore diving freedom! Drive and dive at over 60 marked sites. Perfect for photographers and independent buddy teams.</p>
                                     <div class="d-flex align-items-center text-muted mb-4">
                                         <i class="bi bi-calendar-event me-2"></i> Jan 10 - Jan 17, 2026
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between border-top pt-4">
                                    <div class="d-flex flex-column">
                                        <span class="text-muted small">Starting from</span>
                                        <span class="h3 mb-0 text-gray-900 fw-bold">$1,099<span class="fs-6 fw-normal text-muted">/pp</span></span>
                                    </div>
                                    <button class="btn btn-primary btn-modern px-4 py-2">View Details</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/layouts/main.php'; ?>
