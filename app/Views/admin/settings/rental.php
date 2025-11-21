<?php
/**
 * Rental Settings View
 * Path: app/Views/admin/settings/rental.php
 */

$pageTitle = 'Rental Settings';
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
                        <i class="fas fa-tools mr-2"></i>
                        Rental Settings
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Rental Settings Page</strong> - This page is under development.
                    </div>
                    
                    <p class="text-muted">Configure rental equipment settings, pricing, and policies.</p>
                    
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
