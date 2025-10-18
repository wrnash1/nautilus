<?php
$pageTitle = 'Air Fill Pricing';
$activeMenu = 'settings';

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/admin/settings">Settings</a></li>
            <li class="breadcrumb-item active">Air Fill Pricing</li>
        </ol>
    </nav>

    <h1 class="h3">
        <i class="bi bi-wind"></i> Air Fill Pricing Settings
    </h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Default Pricing (@ Standard Pressure)</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/update">
                    <input type="hidden" name="category" value="air_fills">

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="air_price" class="form-label">
                                <i class="bi bi-wind text-primary"></i> Air Fill Price
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="settings[air_price]" id="air_price"
                                       class="form-control" step="0.01" min="0"
                                       value="<?= $settings['air_price'] ?? 8.00 ?>" required>
                            </div>
                            <small class="text-muted">Standard compressed air (21% O2)</small>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="air_pressure" class="form-label">Standard Pressure</label>
                            <div class="input-group">
                                <input type="number" name="settings[air_pressure]" id="air_pressure"
                                       class="form-control" min="500" max="5000"
                                       value="<?= $settings['air_pressure'] ?? 3000 ?>" required>
                                <span class="input-group-text">PSI</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="nitrox_price" class="form-label">
                                <i class="bi bi-wind text-success"></i> Nitrox Fill Price
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="settings[nitrox_price]" id="nitrox_price"
                                       class="form-control" step="0.01" min="0"
                                       value="<?= $settings['nitrox_price'] ?? 12.00 ?>" required>
                            </div>
                            <small class="text-muted">Enriched Air Nitrox (EAN)</small>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="nitrox_pressure" class="form-label">Standard Pressure</label>
                            <div class="input-group">
                                <input type="number" name="settings[nitrox_pressure]" id="nitrox_pressure"
                                       class="form-control" min="500" max="5000"
                                       value="<?= $settings['nitrox_pressure'] ?? 3000 ?>" required>
                                <span class="input-group-text">PSI</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="trimix_price" class="form-label">
                                <i class="bi bi-wind text-warning"></i> Trimix Fill Price
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="settings[trimix_price]" id="trimix_price"
                                       class="form-control" step="0.01" min="0"
                                       value="<?= $settings['trimix_price'] ?? 25.00 ?>" required>
                            </div>
                            <small class="text-muted">Helium/Oxygen/Nitrogen mix</small>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="trimix_pressure" class="form-label">Standard Pressure</label>
                            <div class="input-group">
                                <input type="number" name="settings[trimix_pressure]" id="trimix_pressure"
                                       class="form-control" min="500" max="5000"
                                       value="<?= $settings['trimix_pressure'] ?? 3000 ?>" required>
                                <span class="input-group-text">PSI</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="oxygen_price" class="form-label">
                                <i class="bi bi-wind text-info"></i> Oxygen Fill Price
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="settings[oxygen_price]" id="oxygen_price"
                                       class="form-control" step="0.01" min="0"
                                       value="<?= $settings['oxygen_price'] ?? 15.00 ?>" required>
                            </div>
                            <small class="text-muted">100% oxygen for decompression</small>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="oxygen_pressure" class="form-label">Standard Pressure</label>
                            <div class="input-group">
                                <input type="number" name="settings[oxygen_pressure]" id="oxygen_pressure"
                                       class="form-control" min="500" max="5000"
                                       value="<?= $settings['oxygen_pressure'] ?? 3000 ?>" required>
                                <span class="input-group-text">PSI</span>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> <strong>Note:</strong>
                        Prices will automatically adjust proportionally based on the actual fill pressure.
                        For example, a fill at 3300 PSI will cost 10% more than the price shown here.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Save Pricing
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
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-calculator"></i> Pricing Examples</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Fill Type</th>
                            <th>Pressure</th>
                            <th class="text-end">Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Air</td>
                            <td>3000 PSI</td>
                            <td class="text-end">$<?= number_format($settings['air_price'] ?? 8.00, 2) ?></td>
                        </tr>
                        <tr>
                            <td>Air</td>
                            <td>3300 PSI</td>
                            <td class="text-end">$<?= number_format(($settings['air_price'] ?? 8.00) * 1.1, 2) ?></td>
                        </tr>
                        <tr>
                            <td>Nitrox</td>
                            <td>3000 PSI</td>
                            <td class="text-end">$<?= number_format($settings['nitrox_price'] ?? 12.00, 2) ?></td>
                        </tr>
                        <tr>
                            <td>Trimix</td>
                            <td>3000 PSI</td>
                            <td class="text-end">$<?= number_format($settings['trimix_price'] ?? 25.00, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Industry Standards</h6>
            </div>
            <div class="card-body small">
                <dl class="mb-0">
                    <dt>Air Fills</dt>
                    <dd>Typically $5-$10 depending on location</dd>

                    <dt>Nitrox (EAN32/36)</dt>
                    <dd>Usually $8-$15, premium over air</dd>

                    <dt>Trimix</dt>
                    <dd>$20-$35+ due to helium cost</dd>

                    <dt>Oxygen</dt>
                    <dd>$10-$20 for deco bottles</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
