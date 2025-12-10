<?php
$pageTitle = 'My Bookings';
require BASE_PATH . '/app/Views/layouts/public.php';

function pageContent() {
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4"><i class="bi bi-calendar-check"></i> My Bookings</h1>
            
            <div class="alert alert-info">
                <h5><i class="bi bi-info-circle"></i> Authentication Required</h5>
                <p class="mb-0">Customer authentication is currently under development. Once complete, you'll be able to view and manage all your course and trip bookings here.</p>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Coming Soon</h5>
                    <p class="card-text">This section will display:</p>
                    <ul>
                        <li>Upcoming dive trips and reservations</li>
                        <li>Course enrollments and schedules</li>
                        <li>Equipment rental reservations</li>
                        <li>Booking history and receipts</li>
                        <li>Ability to modify or cancel bookings</li>
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
