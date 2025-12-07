<?php
/**
 * Email Settings View
 * Path: app/Views/admin/settings/email.php
 */

$pageTitle = 'Email Settings';
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
                        <i class="fas fa-envelope mr-2"></i>
                        Email Settings
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Email Settings Page</strong> - This page is under development.
                    </div>
                    
                    <p class="text-muted">Configure email settings for notifications, receipts, and customer communications.</p>
                    
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
