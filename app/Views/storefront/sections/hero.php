<?php
$heroStyle = $theme['hero_style'] ?? 'image';
$heroHeight = $theme['hero_height'] ?? '500px';
$overlayOpacity = $theme['hero_overlay_opacity'] ?? 0.5;
$showCTA = $theme['show_hero_cta'] ?? true;

$ctaPrimaryText = $config['cta_primary_text'] ?? 'Shop Now';
$ctaPrimaryUrl = $config['cta_primary_url'] ?? '/shop';
$ctaSecondaryText = $config['cta_secondary_text'] ?? null;
$ctaSecondaryUrl = $config['cta_secondary_url'] ?? null;
?>

<div class="hero-section position-relative overflow-hidden"
     style="min-height: <?= htmlspecialchars($heroHeight) ?>; background-color: var(--hero-bg);">

    <?php if (!empty($heroImage['file_path'])): ?>
    <div class="hero-background position-absolute w-100 h-100"
         style="background-image: url('<?= htmlspecialchars($heroImage['file_path']) ?>');
                background-size: cover;
                background-position: center;
                opacity: <?= 1 - $overlayOpacity ?>;">
    </div>
    <?php endif; ?>

    <div class="hero-overlay position-absolute w-100 h-100"
         style="background-color: var(--hero-bg); opacity: <?= $overlayOpacity ?>;"></div>

    <div class="container position-relative h-100 d-flex align-items-center" style="min-height: <?= htmlspecialchars($heroHeight) ?>;">
        <div class="row w-100">
            <div class="col-lg-8 mx-auto text-center text-white">
                <?php if ($section['section_title']): ?>
                <h1 class="display-2 fw-bold mb-4" style="font-family: var(--font-heading); text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                    <?= htmlspecialchars($section['section_title']) ?>
                </h1>
                <?php endif; ?>

                <?php if ($section['section_subtitle']): ?>
                <p class="lead mb-5" style="font-size: 1.5rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
                    <?= htmlspecialchars($section['section_subtitle']) ?>
                </p>
                <?php endif; ?>

                <?php if ($showCTA): ?>
                <div class="hero-cta">
                    <a href="<?= htmlspecialchars($ctaPrimaryUrl) ?>"
                       class="btn btn-lg btn-primary me-3 px-5 py-3"
                       style="font-size: 1.2rem;">
                        <?= htmlspecialchars($ctaPrimaryText) ?>
                    </a>

                    <?php if ($ctaSecondaryText && $ctaSecondaryUrl): ?>
                    <a href="<?= htmlspecialchars($ctaSecondaryUrl) ?>"
                       class="btn btn-lg btn-outline-light px-5 py-3"
                       style="font-size: 1.2rem;">
                        <?= htmlspecialchars($ctaSecondaryText) ?>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
