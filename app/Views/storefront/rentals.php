<?php ob_start(); ?>

<!-- Hero Section -->
<section class="position-relative overflow-hidden py-5 bg-primary text-white" style="background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-800) 100%);">
    <div class="container position-relative z-1 text-center">
        <h1 class="display-4 fw-bold font-heading mb-3">Equipment Rental</h1>
        <p class="lead text-light opacity-75 mb-0">Top-of-the-line gear for your dive.</p>
    </div>
</section>

<!-- Content -->
<section class="py-5">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <!-- BCDs -->
             <div class="col-md-4">
                 <div class="card h-100 border-0 shadow-sm text-center py-5 px-4 rounded-4 hover-lift transition-base">
                    <div class="mb-4 text-primary">
                        <i class="bi bi-file-earmark-person-fill display-4"></i>
                    </div>
                    <h3 class="h4 font-heading fw-bold mb-2">BCDs</h3>
                    <p class="h3 text-primary fw-bold mb-0">$20 <span class="fs-6 text-muted fw-normal">/ day</span></p>
                </div>
            </div>
            
            <!-- Regulators -->
             <div class="col-md-4">
                 <div class="card h-100 border-0 shadow-sm text-center py-5 px-4 rounded-4 hover-lift transition-base">
                    <div class="mb-4 text-primary">
                        <i class="bi bi-lungs-fill display-4"></i>
                    </div>
                    <h3 class="h4 font-heading fw-bold mb-2">Regulators</h3>
                    <p class="h3 text-primary fw-bold mb-0">$25 <span class="fs-6 text-muted fw-normal">/ day</span></p>
                </div>
            </div>
            
            <!-- Wetsuits -->
             <div class="col-md-4">
                 <div class="card h-100 border-0 shadow-sm text-center py-5 px-4 rounded-4 hover-lift transition-base">
                    <div class="mb-4 text-primary">
                        <i class="bi bi-person-badge-fill display-4"></i>
                    </div>
                    <h3 class="h4 font-heading fw-bold mb-2">Wetsuits</h3>
                    <p class="h3 text-primary fw-bold mb-0">$15 <span class="fs-6 text-muted fw-normal">/ day</span></p>
                </div>
            </div>
        </div>
        
         <div class="mt-5 text-center">
             <div class="bg-primary-subtle text-primary p-5 rounded-4 d-inline-block w-100 border border-primary-subtle" style="max-width: 800px;">
                <h2 class="h3 font-heading fw-bold mb-3">Full Package Deal</h2>
                <p class="lead mb-0">Rent BCD, Regulator, Wetsuit, Weights, and Tanks for just <span class="fw-bold">$75/day</span>!</p>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/layouts/main.php'; ?>
