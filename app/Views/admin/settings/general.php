<?php
$pageTitle = 'General Settings';
$activeMenu = 'settings';

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/admin/settings">Settings</a></li>
            <li class="breadcrumb-item active">General Settings</li>
        </ol>
    </nav>

    <h1 class="h3">
        <i class="bi bi-gear"></i> General Settings
    </h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Company Branding & Logo -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-image"></i> Company Branding & Logo
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/upload-logo" enctype="multipart/form-data" id="logoUploadForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_logo" class="form-label">
                                Company Logo <span class="text-muted">(Main)</span>
                            </label>

                            <?php if (!empty($settings['company_logo_path'])): ?>
                                <div class="mb-2 p-3 bg-light border rounded text-center">
                                    <img src="<?= htmlspecialchars($settings['company_logo_path']) ?>"
                                         alt="Company Logo"
                                         style="max-width: 200px; max-height: 100px;"
                                         class="img-fluid">
                                    <div class="mt-2">
                                        <small class="text-muted">Current logo</small>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <input type="file" name="company_logo" id="company_logo"
                                   class="form-control" accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/webp">
                            <small class="text-muted d-block mt-1">
                                Used on invoices, receipts, and emails<br>
                                Recommended: 400x100px, max 5MB<br>
                                Formats: JPG, PNG, SVG, WebP
                            </small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="company_logo_small" class="form-label">
                                Logo Icon <span class="text-muted">(Small)</span>
                            </label>

                            <?php if (!empty($settings['company_logo_small_path'])): ?>
                                <div class="mb-2 p-3 bg-light border rounded text-center">
                                    <img src="<?= htmlspecialchars($settings['company_logo_small_path']) ?>"
                                         alt="Logo Icon"
                                         style="max-width: 60px; max-height: 60px;"
                                         class="img-fluid">
                                    <div class="mt-2">
                                        <small class="text-muted">Current icon</small>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <input type="file" name="company_logo_small" id="company_logo_small"
                                   class="form-control" accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/webp">
                            <small class="text-muted d-block mt-1">
                                Used in navigation bar<br>
                                Recommended: 100x100px (square)<br>
                                Formats: JPG, PNG, SVG, WebP
                            </small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="company_tagline" class="form-label">Company Tagline</label>
                        <input type="text" name="company_tagline" id="company_tagline"
                               class="form-control"
                               value="<?= htmlspecialchars($settings['company_tagline'] ?? '') ?>"
                               placeholder="e.g., Dive Into Adventure">
                        <small class="text-muted">Optional slogan displayed with logo</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="brand_primary_color" class="form-label">Primary Brand Color</label>
                            <div class="input-group">
                                <input type="color" name="brand_primary_color" id="brand_primary_color"
                                       class="form-control form-control-color"
                                       value="<?= htmlspecialchars($settings['brand_primary_color'] ?? '#0066CC') ?>">
                                <input type="text" class="form-control"
                                       value="<?= htmlspecialchars($settings['brand_primary_color'] ?? '#0066CC') ?>"
                                       readonly>
                            </div>
                            <small class="text-muted">Main color used in templates</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="brand_secondary_color" class="form-label">Secondary Brand Color</label>
                            <div class="input-group">
                                <input type="color" name="brand_secondary_color" id="brand_secondary_color"
                                       class="form-control form-control-color"
                                       value="<?= htmlspecialchars($settings['brand_secondary_color'] ?? '#00A8E8') ?>">
                                <input type="text" class="form-control"
                                       value="<?= htmlspecialchars($settings['brand_secondary_color'] ?? '#00A8E8') ?>"
                                       readonly>
                            </div>
                            <small class="text-muted">Accent color for highlights</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Upload Logo & Save Branding
                    </button>
                </form>
            </div>
        </div>

        <!-- Business Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Business Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/update">
                    <input type="hidden" name="category" value="general">

                    <div class="mb-3">
                        <label for="business_name" class="form-label">Business Name</label>
                        <input type="text" name="settings[business_name]" id="business_name"
                               class="form-control" value="<?= htmlspecialchars($settings['business_name'] ?? '') ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="business_email" class="form-label">Business Email</label>
                            <input type="email" name="settings[business_email]" id="business_email"
                                   class="form-control" value="<?= htmlspecialchars($settings['business_email'] ?? '') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="business_phone" class="form-label">Business Phone</label>
                            <input type="tel" name="settings[business_phone]" id="business_phone"
                                   class="form-control" value="<?= htmlspecialchars($settings['business_phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="business_address" class="form-label">Street Address</label>
                        <input type="text" name="settings[business_address]" id="business_address"
                               class="form-control" value="<?= htmlspecialchars($settings['business_address'] ?? '') ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="business_city" class="form-label">City</label>
                            <input type="text" name="settings[business_city]" id="business_city"
                                   class="form-control" value="<?= htmlspecialchars($settings['business_city'] ?? '') ?>">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="business_state" class="form-label">State</label>
                            <input type="text" name="settings[business_state]" id="business_state"
                                   class="form-control" value="<?= htmlspecialchars($settings['business_state'] ?? '') ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="business_zip" class="form-label">ZIP Code</label>
                            <input type="text" name="settings[business_zip]" id="business_zip"
                                   class="form-control" value="<?= htmlspecialchars($settings['business_zip'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="business_country" class="form-label">Country</label>
                        <select name="settings[business_country]" id="business_country" class="form-select">
                            <option value="USA" <?= ($settings['business_country'] ?? 'USA') === 'USA' ? 'selected' : '' ?>>United States</option>
                            <option value="CAN" <?= ($settings['business_country'] ?? '') === 'CAN' ? 'selected' : '' ?>>Canada</option>
                            <option value="MEX" <?= ($settings['business_country'] ?? '') === 'MEX' ? 'selected' : '' ?>>Mexico</option>
                            <option value="Other" <?= ($settings['business_country'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3">Regional Settings</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select name="settings[timezone]" id="timezone" class="form-select">
                                <option value="America/New_York" <?= ($settings['timezone'] ?? 'America/New_York') === 'America/New_York' ? 'selected' : '' ?>>Eastern Time</option>
                                <option value="America/Chicago" <?= ($settings['timezone'] ?? '') === 'America/Chicago' ? 'selected' : '' ?>>Central Time</option>
                                <option value="America/Denver" <?= ($settings['timezone'] ?? '') === 'America/Denver' ? 'selected' : '' ?>>Mountain Time</option>
                                <option value="America/Los_Angeles" <?= ($settings['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' ?>>Pacific Time</option>
                                <option value="America/Anchorage" <?= ($settings['timezone'] ?? '') === 'America/Anchorage' ? 'selected' : '' ?>>Alaska Time</option>
                                <option value="Pacific/Honolulu" <?= ($settings['timezone'] ?? '') === 'Pacific/Honolulu' ? 'selected' : '' ?>>Hawaii Time</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="currency" class="form-label">Currency</label>
                            <select name="settings[currency]" id="currency" class="form-select">
                                <option value="USD" <?= ($settings['currency'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                                <option value="CAD" <?= ($settings['currency'] ?? '') === 'CAD' ? 'selected' : '' ?>>CAD - Canadian Dollar</option>
                                <option value="EUR" <?= ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                                <option value="GBP" <?= ($settings['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_format" class="form-label">Date Format</label>
                            <select name="settings[date_format]" id="date_format" class="form-select">
                                <option value="Y-m-d" <?= ($settings['date_format'] ?? 'Y-m-d') === 'Y-m-d' ? 'selected' : '' ?>>2025-01-15</option>
                                <option value="m/d/Y" <?= ($settings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' ?>>01/15/2025</option>
                                <option value="d/m/Y" <?= ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' ?>>15/01/2025</option>
                                <option value="F j, Y" <?= ($settings['date_format'] ?? '') === 'F j, Y' ? 'selected' : '' ?>>January 15, 2025</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="time_format" class="form-label">Time Format</label>
                            <select name="settings[time_format]" id="time_format" class="form-select">
                                <option value="g:i A" <?= ($settings['time_format'] ?? 'g:i A') === 'g:i A' ? 'selected' : '' ?>>12-hour (3:45 PM)</option>
                                <option value="H:i" <?= ($settings['time_format'] ?? '') === 'H:i' ? 'selected' : '' ?>>24-hour (15:45)</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Save Settings
                        </button>
                        <a href="/admin/settings" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> About Settings</h6>
            </div>
            <div class="card-body">
                <p class="small mb-2">
                    These settings control the basic configuration of your dive shop business.
                </p>
                <hr>
                <dl class="small mb-0">
                    <dt>Business Information</dt>
                    <dd>Appears on receipts, invoices, and customer communications</dd>

                    <dt>Timezone</dt>
                    <dd>Affects scheduling, timestamps, and reports</dd>

                    <dt>Currency</dt>
                    <dd>Default currency for pricing and transactions</dd>

                    <dt>Date/Time Format</dt>
                    <dd>How dates and times are displayed throughout the system</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
