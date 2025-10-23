<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <?php if ($section['section_title']): ?>
            <h2 class="display-5 fw-bold mb-3" style="color: var(--heading-color); font-family: var(--font-heading);">
                <?= htmlspecialchars($section['section_title']) ?>
            </h2>
            <?php endif; ?>

            <?php if ($section['section_subtitle']): ?>
            <p class="lead mb-4"><?= htmlspecialchars($section['section_subtitle']) ?></p>
            <?php endif; ?>

            <form action="/newsletter/subscribe" method="POST" class="row g-3 justify-content-center">
                <div class="col-md-8">
                    <input type="email" name="email" class="form-control form-control-lg"
                           placeholder="Enter your email address" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        Subscribe <i class="bi bi-envelope"></i>
                    </button>
                </div>
            </form>

            <?php if ($config['show_social_links'] ?? false): ?>
            <div class="mt-4">
                <p class="text-muted mb-3">Follow us on social media</p>
                <?php if (!empty($socialLinks)): ?>
                <div class="social-links">
                    <?php foreach ($socialLinks as $platform => $url): ?>
                        <?php if ($url): ?>
                        <a href="<?= htmlspecialchars($url) ?>" target="_blank"
                           class="btn btn-outline-primary btn-lg me-2 mb-2">
                            <i class="bi bi-<?= $platform ?>"></i>
                        </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
