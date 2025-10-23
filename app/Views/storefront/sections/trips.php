<div class="container">
    <?php if ($section['section_title']): ?>
    <div class="text-center mb-5">
        <h2 class="display-5 fw-bold" style="color: var(--heading-color); font-family: var(--font-heading);">
            <?= htmlspecialchars($section['section_title']) ?>
        </h2>
        <?php if ($section['section_subtitle']): ?>
        <p class="lead text-muted"><?= htmlspecialchars($section['section_subtitle']) ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($sectionData)): ?>
    <div class="row g-4">
        <?php foreach ($sectionData as $trip): ?>
        <div class="col-lg-4">
            <div class="card h-100 shadow">
                <?php if ($trip['image_url']): ?>
                <img src="<?= htmlspecialchars($trip['image_url']) ?>"
                     class="card-img-top"
                     alt="<?= htmlspecialchars($trip['name']) ?>"
                     style="height: 200px; object-fit: cover;">
                <?php else: ?>
                <div class="bg-gradient" style="height: 200px; background: linear-gradient(135deg, var(--primary-color), var(--accent-color));"></div>
                <?php endif; ?>

                <div class="card-body">
                    <h5 class="card-title" style="color: var(--heading-color);">
                        <?= htmlspecialchars($trip['name']) ?>
                    </h5>

                    <p class="card-text text-muted">
                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($trip['destination']) ?>
                    </p>

                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="bi bi-calendar"></i>
                            <?= date('M j', strtotime($trip['departure_date'])) ?>
                            -
                            <?= date('M j, Y', strtotime($trip['return_date'])) ?>
                        </small>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> <?= $trip['duration'] ?> days
                        </small>
                    </div>

                    <?php if ($config['show_availability'] ?? false): ?>
                    <div class="mb-3">
                        <?php if ($trip['spots_available'] > 0): ?>
                        <span class="badge bg-success">
                            <?= $trip['spots_available'] ?> spots available
                        </span>
                        <?php else: ?>
                        <span class="badge bg-danger">Fully Booked</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Starting from</small>
                            <div class="fw-bold fs-4" style="color: var(--primary-color);">
                                $<?= number_format($trip['price_per_person'], 2) ?>
                            </div>
                        </div>
                        <a href="/trips/<?= $trip['id'] ?>" class="btn btn-primary">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-5">
        <a href="/trips" class="btn btn-primary btn-lg">
            Explore All Trips <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <?php endif; ?>
</div>
