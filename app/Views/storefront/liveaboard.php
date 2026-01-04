<?php ob_start(); ?>

<!-- Hero Section with Background -->
<section class="position-relative overflow-hidden py-5 d-flex align-items-center bg-dark" style="min-height: 50vh;">
    <!-- Background Image with Overlay -->
    <div class="position-absolute top-0 start-0 w-100 h-100">
        <img src="https://images.unsplash.com/photo-1599583272912-706c8b939c3e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" 
             alt="Liveaboard boat" 
             class="w-100 h-100 object-fit-cover opacity-50">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(17,24,39,0.7) 0%, rgba(17,24,39,0.4) 100%);"></div>
    </div>

    <!-- Content -->
    <div class="container position-relative z-1 text-center py-5">
        <h1 class="display-3 fw-bold font-heading text-white mb-4 tracking-tight">Liveaboard Adventures</h1>
        <p class="lead text-light opacity-90 fs-3 max-w-2xl mx-auto">Explore remote dive sites and untouched reefs in total comfort.</p>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container py-5">
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-water display-1 text-primary-300"></i>
            </div>
            <h2 class="display-6 font-heading fw-bold text-gray-900 mb-4">Upcoming Expeditions</h2>
            <p class="h4 text-muted fw-light">We are currently finalizing our next season of liveaboard trips.</p>
            <p class="text-muted mt-2">Check back soon for new schedules and destinations!</p>
            
            <div class="mt-5">
                <a href="/contact" class="btn btn-outline-primary btn-lg rounded-pill px-5">Contact Us for Waitlist</a>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/layouts/main.php'; ?>
