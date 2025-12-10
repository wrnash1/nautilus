<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-airplane"></i> Trips</h2>
    <div>
        <?php if (hasPermission('trips.create')): ?>
        <a href="/trips/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Trip
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <?php if (empty($trips)): ?>
    <div class="col-12">
        <div class="alert alert-info">No trips found</div>
    </div>
    <?php else: ?>
        <?php foreach ($trips as $trip): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($trip['name']) ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted">
                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($trip['destination']) ?>
                    </h6>
                    <p class="card-text"><?= htmlspecialchars(substr($trip['description'] ?? '', 0, 100)) ?>...</p>
                    
                    <hr>
                    
                    <div class="mb-2">
                        <i class="bi bi-clock"></i> <?= $trip['duration_days'] ?> days
                    </div>
                    <div class="mb-2">
                        <i class="bi bi-people"></i> Max <?= $trip['max_participants'] ?> participants
                    </div>
                    <div class="mb-3">
                        <i class="bi bi-currency-dollar"></i> <?= formatCurrency($trip['price']) ?>
                    </div>
                    
                    <a href="/trips/<?= $trip['id'] ?>" class="btn btn-sm btn-info">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
