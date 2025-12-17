<?php
/**
 * Integrations Settings View
 * Path: app/Views/admin/settings/integrations.php
 */

$pageTitle = 'Integration Settings';
ob_start();
global $company;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plug mr-2"></i>
                        Integration Settings
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Integrations Page</strong> - This page is under development.
                    </div>
                    
                    <h4>Available Integrations</h4>
                    <p class="text-muted">Connect your dive shop with third-party services.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-credit-card mr-2"></i>
                                        Payment Processors
                                    </h5>
                                    <p class="card-text">Connect Stripe, Square, or PayPal for payment processing.</p>
                                    <span class="badge badge-warning">Coming Soon</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-envelope mr-2"></i>
                                        Email Services
                                    </h5>
                                    <p class="card-text">Integrate with Mailchimp, SendGrid, or SMTP.</p>
                                    <span class="badge badge-warning">Coming Soon</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-calculator mr-2"></i>
                                        Accounting
                                    </h5>
                                    <p class="card-text">Sync with QuickBooks, Xero, or Wave.</p>
                                    <span class="badge badge-warning">Coming Soon</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-shipping-fast mr-2"></i>
                                        Shipping
                                    </h5>
                                    <p class="card-text">Connect with UPS, FedEx, or USPS for shipping labels.</p>
                                    <span class="badge badge-warning">Coming Soon</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
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

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/admin.php';
?>
