<?php
/**
 * Air Fills Settings View
 * Path: app/Views/admin/settings/air-fills.php
 */

$pageTitle = 'Air Fills Settings';
require BASE_PATH . '/app/Views/layouts/app.php';
?>

<?php function pageContent() { 
    global $company;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-wind mr-2"></i>
                        Air Fills Settings
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Air Fills Settings Page</strong> - This page is under development.
                    </div>
                    
                    <p class="text-muted">Configure air fill pricing, tank types, and fill station settings.</p>
                    
                    <div class="mt-3">
                        <a href="/store/admin/settings" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php } ?>
