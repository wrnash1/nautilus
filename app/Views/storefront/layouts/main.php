<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? $storeName ?? 'Nautilus Dive Shop') ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? '') ?>">

    <?php if (!empty($favicon['file_path'])): ?>
    <link rel="icon" href="<?= htmlspecialchars($favicon['file_path']) ?>" type="image/x-icon">
    <?php endif; ?>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Dynamic Theme CSS -->
    <style id="theme-variables">
        <?php
        if (isset($themeEngine) && isset($theme)) {
            echo $themeEngine->generateThemeCSS($theme);
        }
        ?>
    </style>

    <!-- Storefront CSS -->
    <link rel="stylesheet" href="/assets/css/storefront.css">

    <?php if (!empty($theme['custom_head_html'])): ?>
    <?= $theme['custom_head_html'] ?>
    <?php endif; ?>

    <?php
    // Analytics tracking codes
    $gaId = $settings->get('google_analytics_id') ?? '';
    $fbPixelId = $settings->get('facebook_pixel_id') ?? '';
    $gtmId = $settings->get('google_tag_manager_id') ?? '';
    ?>

    <?php if ($gaId): ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($gaId) ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= htmlspecialchars($gaId) ?>');
    </script>
    <?php endif; ?>

    <?php if ($fbPixelId): ?>
    <!-- Facebook Pixel -->
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?= htmlspecialchars($fbPixelId) ?>');
        fbq('track', 'PageView');
    </script>
    <?php endif; ?>
</head>
<body style="background-color: var(--body-bg); color: var(--text-color); font-family: var(--font-primary);">

<?php
// Top promotional banners
if (!empty($topBanners)) {
    foreach ($topBanners as $banner) {
        include __DIR__ . '/../partials/banner.php';
    }
}
?>

<!-- Header -->
<?php include __DIR__ . '/../partials/header.php'; ?>

<!-- Main Content -->
<main>
    <?= $content ?? '' ?>
</main>

<!-- Footer -->
<?php include __DIR__ . '/../partials/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Storefront JS -->
<script src="/assets/js/storefront.js"></script>

<?php if (!empty($theme['custom_js'])): ?>
<script>
<?= $theme['custom_js'] ?>
</script>
<?php endif; ?>

</body>
</html>
