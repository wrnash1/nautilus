<?php
/**
 * Email Settings View
 * Path: app/Views/admin/settings/email.php
 */

$pageTitle = 'Email Settings';
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
                        <i class="fas fa-envelope mr-2"></i>
                        Email Settings
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Email Settings Page</strong> - This page is under development.
                    </div>
                    
                    <p class="text-muted mb-4">Configure email settings for notifications, receipts, and customer communications.</p>
                    
                    <form method="POST" action="/store/admin/settings/email/update">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">SMTP Configuration</h5>
                                <div class="form-group mb-3">
                                    <label for="smtp_host">SMTP Host</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="smtp_host" 
                                           name="smtp_host" 
                                           value="<?= htmlspecialchars($settings->get('smtp_host') ?? '') ?>" 
                                           placeholder="smtp.example.com">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="smtp_port">SMTP Port</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="smtp_port" 
                                           name="smtp_port" 
                                           value="<?= htmlspecialchars($settings->get('smtp_port') ?? '587') ?>" 
                                           placeholder="587">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="smtp_encryption">Encryption</label>
                                    <select class="form-control" id="smtp_encryption" name="smtp_encryption">
                                        <option value="tls" <?= ($settings->get('smtp_encryption') == 'tls') ? 'selected' : '' ?>>TLS</option>
                                        <option value="ssl" <?= ($settings->get('smtp_encryption') == 'ssl') ? 'selected' : '' ?>>SSL</option>
                                        <option value="none" <?= ($settings->get('smtp_encryption') == 'none') ? 'selected' : '' ?>>None</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5 class="mb-3">Authentication</h5>
                                <div class="form-group mb-3">
                                    <label for="smtp_username">SMTP Username</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="smtp_username" 
                                           name="smtp_username" 
                                           value="<?= htmlspecialchars($settings->get('smtp_username') ?? '') ?>">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="smtp_password">SMTP Password</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="smtp_password" 
                                           name="smtp_password" 
                                           placeholder="Leave blank to keep existing password">
                                </div>
                            </div>
                        </div>

                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Sender Identity</h5>
                                <div class="form-group mb-3">
                                    <label for="from_name">From Name</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="from_name" 
                                           name="from_name" 
                                           value="<?= htmlspecialchars($settings->get('from_name') ?? 'Nautilus Dive Shop') ?>">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="from_email">From Email</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="from_email" 
                                           name="from_email" 
                                           value="<?= htmlspecialchars($settings->get('from_email') ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>
                                Save Email Settings
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
