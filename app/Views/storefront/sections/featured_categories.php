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
        <?php foreach ($sectionData as $category): ?>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="/shop?category=<?= $category['id'] ?>" class="text-decoration-none">
                <div class="card category-card text-center h-100 shadow-sm hover-shadow">
                    <div class="card-body">
                        <?php if ($category['icon']): ?>
                        <i class="<?= htmlspecialchars($category['icon']) ?> fs-1 mb-3" style="color: var(--primary-color);"></i>
                        <?php else: ?>
                        <i class="bi bi-grid fs-1 mb-3" style="color: var(--primary-color);"></i>
                        <?php endif; ?>
                        <h6 class="card-title mb-1" style="color: var(--heading-color);">
                            <?= htmlspecialchars($category['name']) ?>
                        </h6>
                        <?php if ($config['show_product_count'] ?? false): ?>
                        <small class="text-muted">(<?= $category['product_count'] ?> items)</small>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
