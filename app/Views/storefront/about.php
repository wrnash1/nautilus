<?php ob_start(); ?>

<!-- Hero Section -->
<section class="position-relative overflow-hidden py-5 d-flex align-items-center bg-dark" style="min-height: 40vh;">
    <div class="position-absolute top-0 start-0 w-100 h-100">
        <img src="https://images.unsplash.com/photo-1544551763-46a8723ba1f9?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" 
             alt="About Background" 
             class="w-100 h-100 object-fit-cover opacity-50">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.8) 0%, rgba(5, 44, 101, 0.9) 100%);"></div>
    </div>
    
    <div class="container position-relative z-1 text-center text-white">
        <h1 class="display-3 fw-bold font-heading mb-3">Our Story</h1>
        <p class="lead opacity-90 mx-auto" style="max-width: 700px;">Founded by divers, for divers. We are passionate about exploring and protecting the world's oceans.</p>
    </div>
</section>

<!-- Content -->
<section class="py-5">
    <div class="container py-4">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2 class="display-6 font-heading fw-bold mb-4 text-gray-900">Why Choose <?= htmlspecialchars($business_name ?? 'Nautilus') ?>?</h2>
                <div class="text-muted fs-5">
                    <p class="mb-4">
                        We started with a simple mission: to provide the highest quality dive education and equipment in a friendly, inclusive environment. Whether you're taking your first breath underwater or training to become an instructor, we are here to support your journey.
                    </p>
                    <p class="mb-4">
                        Our facility features a heated indoor pool, state-of-the-art classroom, and a fully stocked retail floor with top brands like Scubapro, Atomic, and Shearwater.
                    </p>
                    
                    <ul class="list-unstyled mt-5 d-flex flex-column gap-3">
                        <li class="d-flex align-items-center">
                             <i class="bi bi-patch-check-fill text-success fs-4 me-3"></i>
                             <span class="fw-bold text-gray-800">PADI 5 Star IDC Center</span>
                        </li>
                         <li class="d-flex align-items-center">
                             <i class="bi bi-patch-check-fill text-success fs-4 me-3"></i>
                             <span class="fw-bold text-gray-800">Experienced, patient instructors</span>
                        </li>
                         <li class="d-flex align-items-center">
                             <i class="bi bi-patch-check-fill text-success fs-4 me-3"></i>
                             <span class="fw-bold text-gray-800">Small class sizes for personalized attention</span>
                        </li>
                    </ul>
                </div>
            </div>
            
             <div class="col-lg-6">
                 <div class="row g-3">
                     <div class="col-6 mt-5">
                        <img class="img-fluid rounded-4 shadow-lg hover-scale transition-base" 
                             src="https://images.unsplash.com/photo-1582967788606-a171f1080ca8?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80" 
                             alt="Diving instruction">
                     </div>
                     <div class="col-6">
                        <img class="img-fluid rounded-4 shadow-lg hover-scale transition-base" 
                             src="https://images.unsplash.com/photo-1510662145379-13537db782dc?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80" 
                             alt="Reef diving">
                     </div>
                 </div>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/layouts/main.php'; ?>
