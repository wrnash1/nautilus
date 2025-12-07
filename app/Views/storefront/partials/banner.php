<div class="promotional-banner"
     style="background-color: <?= htmlspecialchars($banner['background_color']) ?>;
            color: <?= htmlspecialchars($banner['text_color']) ?>;">
    <div class="container">
        <div class="d-flex justify-content-center align-items-center">
            <div>
                <?php if ($banner['title']): ?>
                <strong><?= htmlspecialchars($banner['title']) ?></strong>
                <?php endif; ?>

                <?php if ($banner['content']): ?>
                <?= htmlspecialchars($banner['content']) ?>
                <?php endif; ?>

                <?php if ($banner['link_url'] && $banner['link_text']): ?>
                <a href="<?= htmlspecialchars($banner['link_url']) ?>"
                   onclick="fetch('/admin/storefront/banner/<?= $banner['id'] ?>/click', {method: 'POST'})"
                   style="color: inherit;">
                    <?= htmlspecialchars($banner['link_text']) ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
