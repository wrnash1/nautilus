<?php
/**
 * Alpha Development Warning Banner
 * Displayed at the top of all authenticated pages
 */

// Only show if APP_ENV is not production
if (($_ENV['APP_ENV'] ?? 'local') !== 'production'):
    $appName = $_ENV['APP_NAME'] ?? 'Nautilus v2.0 Alpha';
    $isAlpha = stripos($appName, 'alpha') !== false || stripos($appName, 'beta') !== false || stripos($appName, 'dev') !== false;

    if ($isAlpha):
?>
<div class="alert alert-warning alert-dismissible fade show mb-3" role="alert" style="border-left: 4px solid #ff6b6b; background-color: #fff3cd;">
    <div class="d-flex align-items-center">
        <div class="me-3" style="font-size: 2rem;">⚠️</div>
        <div class="flex-grow-1">
            <h5 class="alert-heading mb-1">
                <strong>Alpha Development Version</strong>
            </h5>
            <p class="mb-1">
                <small>
                    You are using <strong><?= htmlspecialchars($appName) ?></strong> - This software is in active development.
                    Some features may be incomplete or under testing.
                </small>
            </p>
            <hr class="my-2">
            <p class="mb-0">
                <small>
                    <strong>Known limitations:</strong>
                    Email notifications (appointments, RMA, travel packets),
                    PDF generation for travel packets.
                    <?php if (empty($_ENV['STRIPE_SECRET_KEY']) && empty($_ENV['SQUARE_ACCESS_TOKEN'])): ?>
                    Payment gateways not configured.
                    <?php endif; ?>
                    <?php if (empty($_ENV['MAIL_USERNAME'])): ?>
                    Email service not configured.
                    <?php endif; ?>
                </small>
            </p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php
    endif;
endif;
?>
