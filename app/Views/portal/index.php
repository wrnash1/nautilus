<?php
$pageTitle = 'Customer Portal';
require BASE_PATH . '/app/Views/layouts/public.php';

function pageContent() {
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="text-center mb-5">
                <h1 class="display-4">Customer Portal</h1>
                <p class="lead">Access your dive certifications, bookings, and account information</p>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-award display-1 text-primary mb-3"></i>
                    <h3 class="card-title">My Certifications</h3>
                    <p class="card-text">View your dive certifications and training records</p>
                    <a href="/portal/certifications" class="btn btn-primary">View Certifications</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-check display-1 text-primary mb-3"></i>
                    <h3 class="card-title">My Bookings</h3>
                    <p class="card-text">Manage your course and trip reservations</p>
                    <a href="/portal/bookings" class="btn btn-primary">View Bookings</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-person-circle display-1 text-primary mb-3"></i>
                    <h3 class="card-title">My Account</h3>
                    <p class="card-text">Update your profile and contact information</p>
                    <a href="#" class="btn btn-primary">Manage Account</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-12">
            <div class="alert alert-info">
                <h5><i class="bi bi-info-circle"></i> Customer Portal Features</h5>
                <p class="mb-0">The customer portal is currently under development. Full authentication and account management features will be available soon. For immediate assistance, please <a href="/contact">contact us</a>.</p>
            </div>
        </div>
    </div>
</div>

<?php
}

// Render the page
ob_start();
pageContent();
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/public.php';
?>
