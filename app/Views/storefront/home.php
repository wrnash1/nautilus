<?php
// Start output buffering for content
ob_start();
?>

<?php
// Render each homepage section dynamically
foreach ($sections as $section):
    if (!$section['is_active']) continue;

    $sectionData = $sectionData[$section['id']] ?? [];
    $config = $section['config'] ?? [];
    $sectionType = $section['section_type'];
?>

<section class="homepage-section section-<?= htmlspecialchars($sectionType) ?>"
         style="background-color: <?= htmlspecialchars($section['background_color'] ?? 'transparent') ?>;
                color: <?= htmlspecialchars($section['text_color'] ?? 'inherit') ?>;
                padding-top: <?= htmlspecialchars($section['padding_top'] ?? '3rem') ?>;
                padding-bottom: <?= htmlspecialchars($section['padding_bottom'] ?? '3rem') ?>;
                <?php if ($section['background_image']): ?>
                background-image: url('<?= htmlspecialchars($section['background_image']) ?>');
                background-size: cover;
                background-position: center;
                <?php endif; ?>">

    <?php
    // Include section template based on type
    $sectionTemplate = __DIR__ . "/sections/{$sectionType}.php";
    if (file_exists($sectionTemplate)) {
        include $sectionTemplate;
    }
    ?>

</section>

<?php endforeach; ?>

<?php
// Capture content
$content = ob_get_clean();

// Include layout
include __DIR__ . '/layouts/main.php';
?>
