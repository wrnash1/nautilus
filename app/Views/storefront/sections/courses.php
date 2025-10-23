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
        <?php foreach ($sectionData as $course): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title" style="color: var(--heading-color);">
                            <?= htmlspecialchars($course['name']) ?>
                        </h5>
                        <span class="badge bg-primary"><?= htmlspecialchars($course['certification_level'] ?? 'PADI') ?></span>
                    </div>

                    <p class="card-text text-muted">
                        <?= htmlspecialchars(substr($course['description'] ?? '', 0, 120)) ?>...
                    </p>

                    <?php if ($config['show_schedule'] ?? false): ?>
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="bi bi-calendar"></i>
                            <?= date('M j, Y', strtotime($course['start_date'])) ?>
                            -
                            <?= date('M j, Y', strtotime($course['end_date'])) ?>
                        </small>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold fs-5" style="color: var(--primary-color);">
                                $<?= number_format($course['price'], 2) ?>
                            </span>
                        </div>
                        <div>
                            <?php if ($course['spots_available'] > 0): ?>
                            <small class="text-success">
                                <i class="bi bi-check-circle"></i> <?= $course['spots_available'] ?> spots left
                            </small>
                            <?php else: ?>
                            <small class="text-danger">
                                <i class="bi bi-x-circle"></i> Full
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <a href="/courses/<?= $course['id'] ?>" class="btn btn-outline-primary w-100 mt-3">
                        Learn More <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-5">
        <a href="/courses" class="btn btn-primary btn-lg">
            View All Courses <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <?php endif; ?>
</div>
