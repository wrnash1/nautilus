<div class="container">
    <?php if ($section['section_title']): ?>
    <div class="text-center mb-5">
        <h2 class="display-6 fw-bold" style="color: var(--heading-color); font-family: var(--font-heading);">
            <?= htmlspecialchars($section['section_title']) ?>
        </h2>
        <?php if ($section['section_subtitle']): ?>
        <p class="text-muted"><?= htmlspecialchars($section['section_subtitle']) ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($sectionData)): ?>
    <div class="row g-4 align-items-center justify-content-center">
        <?php foreach ($sectionData as $brand): ?>
        <div class="col-6 col-md-3 col-lg-2 text-center">
            <div class="brand-logo p-3">
                <?php if ($brand['logo_url']): ?>
                <img src="<?= htmlspecialchars($brand['logo_url']) ?>"
                     alt="<?= htmlspecialchars($brand['name']) ?>"
                     class="img-fluid"
                     style="max-height: 60px; filter: grayscale(100%); opacity: 0.7; transition: all 0.3s;"
                     onmouseover="this.style.filter='grayscale(0%)'; this.style.opacity='1';"
                     onmouseout="this.style.filter='grayscale(100%)'; this.style.opacity='0.7';">
                <?php else: ?>
                <h6 class="text-muted"><?= htmlspecialchars($brand['name']) ?></h6>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
