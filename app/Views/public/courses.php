<?php
$pageTitle = 'Diving Courses';
ob_start();
?>

<!-- Courses Header -->
<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-12">
            <h1>Scuba Diving Courses</h1>
            <p class="text-muted">Start your diving journey or advance your skills with our PADI certified courses</p>
        </div>
    </div>
</div>

<!-- Courses Grid -->
<div class="container mb-5">
    <?php if (empty($courses)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> No courses available at this time. Contact us for more information.
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($courses as $course): ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h4 class="card-title"><?= htmlspecialchars($course['name']) ?></h4>
                    </div>
                    
                    <?php if ($course['description']): ?>
                    <p class="card-text"><?= htmlspecialchars($course['description']) ?></p>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <?php if ($course['duration_days']): ?>
                        <p class="mb-1">
                            <i class="bi bi-clock text-primary"></i>
                            <strong>Duration:</strong> <?= htmlspecialchars($course['duration_days']) ?> days
                        </p>
                        <?php endif; ?>
                        
                        <?php if ($course['price']): ?>
                        <p class="mb-1">
                            <i class="bi bi-currency-dollar text-primary"></i>
                            <strong>Price:</strong> <?= formatCurrency($course['price']) ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if ($course['upcoming_count'] > 0): ?>
                        <p class="mb-1">
                            <i class="bi bi-calendar-check text-success"></i>
                            <strong><?= $course['upcoming_count'] ?></strong> upcoming session<?= $course['upcoming_count'] > 1 ? 's' : '' ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <a href="/course/<?= $course['id'] ?>" class="btn btn-primary">
                        <i class="bi bi-info-circle"></i> View Details & Schedule
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Why Learn to Dive -->
<div class="container mb-5">
    <div class="card bg-light">
        <div class="card-body">
            <h3 class="text-center mb-4">Why Learn to Dive?</h3>
            <div class="row">
                <div class="col-md-4 text-center mb-3">
                    <i class="bi bi-water" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <h5 class="mt-3">Explore New Worlds</h5>
                    <p>Discover the beauty of the underwater world</p>
                </div>
                <div class="col-md-4 text-center mb-3">
                    <i class="bi bi-people" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <h5 class="mt-3">Meet New People</h5>
                    <p>Join a global community of divers</p>
                </div>
                <div class="col-md-4 text-center mb-3">
                    <i class="bi bi-trophy" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <h5 class="mt-3">Achieve Your Goals</h5>
                    <p>Earn internationally recognized certifications</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/public.php';
?>
