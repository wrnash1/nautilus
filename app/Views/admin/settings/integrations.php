<?php
$pageTitle = 'Integrations';
$activeMenu = 'settings';

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/admin/settings">Settings</a></li>
            <li class="breadcrumb-item active">Integrations</li>
        </ol>
    </nav>

    <h1 class="h3"><i class="bi bi-plugin"></i> Third-Party Integrations</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Wave Apps -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="bi bi-cloud"></i> Wave Apps (Accounting)</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/update">
                    <input type="hidden" name="category" value="wave">

                    <div class="mb-3">
                        <label for="wave_access_token" class="form-label">Wave Access Token</label>
                        <input type="password" name="settings[wave_access_token]" id="wave_access_token"
                               class="form-control" value="<?= htmlspecialchars($settings['wave_access_token'] ?? '') ?>">
                        <small class="text-muted">Get this from Wave Settings → Integrations → API</small>
                    </div>

                    <div class="mb-3">
                        <label for="wave_business_id" class="form-label">Wave Business ID</label>
                        <input type="text" name="settings[wave_business_id]" id="wave_business_id"
                               class="form-control" value="<?= htmlspecialchars($settings['wave_business_id'] ?? '') ?>">
                        <small class="text-muted">Found in your Wave business URL</small>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="settings[wave_auto_sync]" class="form-check-input" id="wave_auto_sync"
                               <?= !empty($settings['wave_auto_sync']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="wave_auto_sync">
                            Auto-sync transactions to Wave (creates invoices automatically)
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Wave Settings</button>
                    <a href="/integrations/wave" class="btn btn-success">
                        <i class="bi bi-cloud-upload"></i> Go to Wave Sync Dashboard
                    </a>
                </form>
            </div>
        </div>

        <!-- Other integrations placeholders -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Other Integrations</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">PADI, SSI, and Twilio integrations can be configured here.</p>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Wave Setup Guide</h6>
            </div>
            <div class="card-body small">
                <h6>1. Get Wave API Token</h6>
                <ol>
                    <li>Log in to Wave Apps</li>
                    <li>Go to Settings → Integrations</li>
                    <li>Create API Application</li>
                    <li>Copy Access Token</li>
                </ol>

                <h6>2. Find Business ID</h6>
                <ol>
                    <li>In Wave, go to your Business</li>
                    <li>Check URL for Business ID</li>
                </ol>

                <h6>3. Configure & Test</h6>
                <ol>
                    <li>Enter credentials above</li>
                    <li>Save settings</li>
                    <li>Click "Go to Wave Sync Dashboard"</li>
                    <li>Test connection</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
