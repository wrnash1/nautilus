<?php
$pageTitle = 'Dive Trips';
ob_start();
?>

<!-- Trips Header -->
<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-12">
            <h1>Dive Trips & Adventures</h1>
            <p class="text-muted">Join us for unforgettable diving experiences around the world</p>
        </div>
    </div>
</div>

<!-- Trips Grid -->
<div class="container mb-5">
    <?php if (empty($trips)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> No trips scheduled at this time. Contact us to learn about upcoming adventures!
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($trips as $trip): ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="card-title"><?= htmlspecialchars($trip['name']) ?></h4>
                    
                    <?php if ($trip['destination']): ?>
                    <p class="text-primary mb-3">
                        <i class="bi bi-geo-alt-fill"></i>
                        <strong><?= htmlspecialchars($trip['destination']) ?></strong>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($trip['description']): ?>
                    <p class="card-text"><?= htmlspecialchars($trip['description']) ?></p>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <?php if ($trip['duration_days']): ?>
                        <p class="mb-1">
                            <i class="bi bi-calendar3 text-primary"></i>
                            <strong>Duration:</strong> <?= htmlspecialchars($trip['duration_days']) ?> days
                        </p>
                        <?php endif; ?>
                        
                        <?php if ($trip['price']): ?>
                        <p class="mb-1">
                            <i class="bi bi-currency-dollar text-primary"></i>
                            <strong>Price:</strong> <?= formatCurrency($trip['price']) ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if ($trip['upcoming_count'] > 0): ?>
                        <p class="mb-1">
                            <i class="bi bi-calendar-check text-success"></i>
                            <strong><?= $trip['upcoming_count'] ?></strong> upcoming departure<?= $trip['upcoming_count'] > 1 ? 's' : '' ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <a href="/trip/<?= $trip['id'] ?>" class="btn btn-primary">
                        <i class="bi bi-info-circle"></i> View Details & Dates
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Trip Benefits -->
<div class="container mb-5">
    <div class="card bg-light">
        <div class="card-body">
            <h3 class="text-center mb-4">What's Included</h3>
            <div class="row">
                <div class="col-md-3 text-center mb-3">
                    <i class="bi bi-person-check" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <h5 class="mt-3">Expert Guides</h5>
                    <p>Experienced dive masters</p>
                </div>
                <div class="col-md-3 text-center mb-3">
                    <i class="bi bi-gear" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <h5 class="mt-3">Equipment</h5>
                    <p>Quality dive gear provided</p>
                </div>
                <div class="col-md-3 text-center mb-3">
                    <i class="bi bi-house" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <h5 class="mt-3">Accommodation</h5>
                    <p>Comfortable lodging</p>
                </div>
                <div class="col-md-3 text-center mb-3">
                    <i class="bi bi-cup-straw" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <h5 class="mt-3">Meals</h5>
                    <p>Delicious food included</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/public.php';
?>
