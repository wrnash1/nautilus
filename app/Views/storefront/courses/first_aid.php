<?php ob_start(); ?>

<!-- Hero Section -->
<section class="position-relative overflow-hidden py-5 bg-danger text-white" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">
    <div class="container position-relative z-1 text-center">
        <h1 class="display-4 fw-bold font-heading mb-3">Emergency First Response</h1>
        <p class="lead text-white opacity-90 mb-0">CPR, AED, and First Aid Training for everyone.</p>
    </div>
</section>

<!-- Content -->
<section class="py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2 class="display-5 font-heading fw-bold mb-4 text-gray-900">Course Description</h2>
                <div class="lead text-muted">
                    <p class="mb-4">Emergency First Response® primary and secondary care training teaches you what to do in the critical moments between when a life-threatening emergency occurs and when emergency medical services arrive.</p>
                    <p class="mb-4">You will learn how to perform CPR, use an AED, and provide first aid that eases pain and reduces the risk of further harm.</p>
                </div>
                 <div class="mt-5">
                    <a href="#" class="btn btn-danger btn-lg btn-modern rounded-pill px-5 shadow-sm">Sign Up Now</a>
                </div>
            </div>
             <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" 
                     alt="First Aid" 
                     class="img-fluid rounded-4 shadow-lg hover-scale transition-base">
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../../layouts/main.php'; ?>
