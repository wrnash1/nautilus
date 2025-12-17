<?php
/**
 * Tax Settings View
 * Path: app/Views/admin/settings/tax.php
 */

$pageTitle = 'Tax Settings';
ob_start();
global $company, $taxRate;
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
                    
                    <form method="POST" action="/store/admin/settings/update">
                        <?= csrf_field() ?>
                        
                        <div class="form-group">
                            <label for="tax_rate">Default Tax Rate (%)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="tax_rate" 
                                   name="tax_rate" 
                                   value="<?= htmlspecialchars($taxRate * 100) ?>" 
                                   step="0.01"
                                   min="0"
                                   max="100">
                            <small class="form-text text-muted">
                                Enter the tax rate as a percentage (e.g., 7.5 for 7.5%)
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
