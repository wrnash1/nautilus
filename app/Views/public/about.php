<?php
$pageTitle = 'About Us';
$company = getCompanyInfo();
ob_start();
?>

<!-- About Header -->
<div class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1>About <?= htmlspecialchars($company['name']) ?></h1>
                <p class="lead">Your trusted partner in scuba diving adventures</p>
            </div>
        </div>
    </div>
</div>

<!-- Our Story -->
<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h2 class="text-center mb-4">Our Story</h2>
            <p class="lead text-center">
                At <?= htmlspecialchars($company['name']) ?>, we're passionate about sharing the wonders of the underwater world with divers of all levels.
            </p>
            <p>
                Founded by experienced dive professionals, our mission is to provide safe, enjoyable, and memorable diving experiences. 
                Whether you're taking your first breath underwater or you're a seasoned technical diver, we have the expertise and 
                equipment to support your diving journey.
            </p>
            <p>
                We pride ourselves on maintaining the highest safety standards, using quality equipment, and employing only 
                certified, experienced instructors. Our commitment to excellence has made us a trusted name in the diving community.
            </p>
        </div>
    </div>
</div>

<!-- Our Values -->
<div class="container my-5">
    <h2 class="text-center mb-4">Our Values</h2>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-shield-check" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <h4 class="mt-3">Safety First</h4>
                    <p>We never compromise on safety. All our equipment is regularly serviced and our staff are highly trained professionals.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-heart" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <h4 class="mt-3">Passion</h4>
                    <p>We love what we do, and it shows. Our enthusiasm for diving is contagious and we love sharing it with others.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-globe" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <h4 class="mt-3">Conservation</h4>
                    <p>We're committed to protecting our oceans and marine life for future generations of divers.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Certifications -->
<div class="container my-5">
    <div class="card bg-light">
        <div class="card-body text-center py-5">
            <h3 class="mb-4">Certifications & Affiliations</h3>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <i class="bi bi-award-fill" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <p class="mt-2"><strong>PADI 5-Star Facility</strong></p>
                </div>
                <div class="col-md-3 mb-3">
                    <i class="bi bi-shield-fill-check" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <p class="mt-2"><strong>Insured & Licensed</strong></p>
                </div>
                <div class="col-md-3 mb-3">
                    <i class="bi bi-people-fill" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <p class="mt-2"><strong>Certified Instructors</strong></p>
                </div>
                <div class="col-md-3 mb-3">
                    <i class="bi bi-gear-fill" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <p class="mt-2"><strong>Quality Equipment</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="container my-5">
    <div class="card bg-primary text-white">
        <div class="card-body text-center py-5">
            <h2>Ready to Dive In?</h2>
            <p class="lead">Join us for your next underwater adventure</p>
            <a href="/contact" class="btn btn-light btn-lg mt-3">
                <i class="bi bi-envelope"></i> Contact Us Today
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/public.php';
?>
