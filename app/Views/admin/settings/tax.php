<?php
/**
 * Tax Settings View
 * Path: app/Views/admin/settings/tax.php
 */

$pageTitle = 'Tax Settings';
ob_start();
use App\Core\Settings;
$settings = Settings::getInstance();
// global $company; // Removed unused global
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-percentage mr-2"></i>
                        Tax Settings
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Tax Settings Page</strong> - This page is under development.
                    </div>
                    
                    <form method="POST" action="/store/admin/settings/tax/update">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="tax_enabled" name="tax_enabled" <?= ($settings->get('tax_enabled') ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="tax_enabled">
                                Enable Tax Calculation
                            </label>
                            <small class="form-text text-muted d-block">
                                If disabled, tax will not be calculated for sales.
                            </small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="tax_label">Tax Label</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="tax_label" 
                                   name="tax_label" 
                                   value="<?= htmlspecialchars($settings->get('tax_label') ?? 'Tax') ?>" 
                                   placeholder="e.g. Sales Tax, VAT, GST">
                        </div>

                        <div class="form-group mb-3">
                            <label for="tax_rate">Default Tax Rate (%)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="tax_rate" 
                                   name="tax_rate" 
                                   value="<?= htmlspecialchars(($settings->get('tax_rate') ?? 0.07) * 100) ?>" 
                                   step="0.001"
                                   min="0"
                                   max="100">
                            <small class="form-text text-muted">
                                Enter the tax rate as a percentage (e.g., 7.5 for 7.5%)
                            </small>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="tax_inclusive" name="tax_inclusive" <?= ($settings->get('tax_inclusive') ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tax_inclusive">
                                Prices Include Tax
                            </label>
                            <small class="form-text text-muted d-block">
                                If checked, product prices will be treated as tax-inclusive.
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>
                                Save Tax Settings
                            </button>
                            <a href="/store/admin/settings" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to Settings
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/admin.php';
?>
