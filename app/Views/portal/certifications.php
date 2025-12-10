<?php
$pageTitle = 'My Certifications';
require BASE_PATH . '/app/Views/layouts/public.php';

function pageContent() {
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4"><i class="bi bi-award"></i> My Certifications</h1>
            
            <div class="alert alert-info">
                <h5><i class="bi bi-info-circle"></i> Authentication Required</h5>
                <p class="mb-0">Customer authentication is currently under development. Once complete, you'll be able to view all your dive certifications, training records, and certification cards here.</p>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Coming Soon</h5>
                    <p class="card-text">This section will display:</p>
                    <ul>
                        <li>Your dive certifications (Open Water, Advanced, etc.)</li>
                        <li>Certification dates and agencies</li>
                        <li>Digital certification cards</li>
                        <li>Training history and course completions</li>
                        <li>Specialty certifications</li>
                    </ul>
                    <a href="/portal" class="btn btn-primary">Back to Portal</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
}

ob_start();
pageContent();
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/public.php';
?>
