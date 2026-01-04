<?php ob_start(); ?>

<!-- Hero Section -->
<section class="position-relative overflow-hidden py-5 bg-dark text-white" style="background: linear-gradient(135deg, var(--gray-800) 0%, var(--gray-900) 100%);">
    <div class="container position-relative z-1 text-center">
        <h1 class="display-4 fw-bold font-heading mb-3">Equipment Repair & Service</h1>
        <p class="lead text-light opacity-75 mb-0">Certified technicians for all major brands.</p>
    </div>
</section>

<!-- Content -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Service Menu -->
            <div class="col-lg-8">
                 <h2 class="h3 font-heading fw-bold text-gray-900 mb-4">Service Menu</h2>
                 <div class="d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-center bg-white p-4 rounded-3 shadow-sm border border-light">
                        <span class="h6 mb-0">Regulator Annual Service</span>
                        <span class="h5 mb-0 text-primary fw-bold">$85 <span class="text-muted small fw-normal">+ parts</span></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-white p-4 rounded-3 shadow-sm border border-light">
                        <span class="h6 mb-0">BCD Service & Cleaning</span>
                        <span class="h5 mb-0 text-primary fw-bold">$45</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-white p-4 rounded-3 shadow-sm border border-light">
                        <span class="h6 mb-0">Tank Visual Inspection (VIP)</span>
                        <span class="h5 mb-0 text-primary fw-bold">$20</span>
                    </div>
                     <div class="d-flex justify-content-between align-items-center bg-white p-4 rounded-3 shadow-sm border border-light">
                        <span class="h6 mb-0">Tank Hydrostatic Test</span>
                        <span class="h5 mb-0 text-primary fw-bold">$55</span>
                    </div>
                 </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                 <div class="bg-light p-4 rounded-4 border border-light">
                    <h3 class="h5 font-heading fw-bold text-gray-900 mb-3">Turnaround Time</h3>
                    <p class="text-muted mb-4">Standard turnaround is 7-10 days. Rush service available for an additional fee.</p>
                    <div class="d-flex align-items-center text-success fw-bold">
                        <i class="bi bi-patch-check-fill me-2 fs-5"></i> Authorized Dealer Service
                    </div>
                    <hr class="my-4">
                    <a href="/contact" class="btn btn-primary btn-modern w-100">Schedule Service</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../../layouts/main.php'; ?>
