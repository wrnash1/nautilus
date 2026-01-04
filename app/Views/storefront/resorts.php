<?php ob_start(); ?>

<!-- Hero Section with Background -->
<section class="position-relative overflow-hidden py-5 d-flex align-items-center bg-dark" style="min-height: 50vh;">
    <div class="position-absolute top-0 start-0 w-100 h-100">
        <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" 
             alt="Resort" 
             class="w-100 h-100 object-fit-cover opacity-50">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(13, 148, 136, 0.7) 0%, rgba(17, 94, 89, 0.4) 100%);"></div>
    </div>

    <!-- Content -->
    <div class="container position-relative z-1 text-center py-5">
        <h1 class="display-3 fw-bold font-heading text-white mb-4 tracking-tight">Luxury Dive Resorts</h1>
        <p class="lead text-light opacity-90 fs-3 max-w-2xl mx-auto">Relax in paradise with our curated dive resort partners.</p>
    </div>
</section>

<!-- Content -->
<section class="py-5">
    <div class="container py-5">
         <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-sun display-1 text-warning"></i>
            </div>
            <h2 class="display-6 font-heading fw-bold text-gray-900 mb-4">Featured Destinations</h2>
            <p class="h4 text-muted fw-light">New resort packages coming this summer.</p>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/layouts/main.php'; ?>
