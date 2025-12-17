<?php
$pageTitle = 'Settings';
$company = getCompanyInfo();
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-gear"></i> Settings</h1>
            </div>

            <!-- Settings Tabs -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#general">
                        <i class="bi bi-building"></i> General
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#branding">
                        <i class="bi bi-palette"></i> Branding
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/store/admin/settings/tax">
                        <i class="bi bi-calculator"></i> Tax
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/store/admin/settings/email">
                        <i class="bi bi-envelope"></i> Email
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/store/admin/settings/payment">
                        <i class="bi bi-credit-card"></i> Payment
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/store/admin/settings/integrations">
                        <i class="bi bi-plugin"></i> Integrations
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- General Settings -->
                <div class="tab-pane fade show active" id="general">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Company Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="/store/admin/settings/update">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="business_name" class="form-label">Business Name *</label>
                                        <input type="text" class="form-control" id="business_name" name="business_name" 
                                               value="<?= htmlspecialchars($company['name']) ?>" required>
                                        <small class="text-muted">This will appear throughout the application and on the public website</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="business_email" class="form-label">Business Email *</label>
                                        <input type="email" class="form-control" id="business_email" name="business_email" 
                                               value="<?= htmlspecialchars($company['email']) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="business_phone" class="form-label">Business Phone *</label>
                                        <input type="tel" class="form-control" id="business_phone" name="business_phone" 
                                               value="<?= htmlspecialchars($company['phone']) ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="business_address" class="form-label">Street Address *</label>
                                        <input type="text" class="form-control" id="business_address" name="business_address" 
                                               value="<?= htmlspecialchars($company['address']) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="business_city" class="form-label">City *</label>
                                        <input type="text" class="form-control" id="business_city" name="business_city" 
                                               value="<?= htmlspecialchars($company['city']) ?>" required>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="business_state" class="form-label">State/Province *</label>
                                        <input type="text" class="form-control" id="business_state" name="business_state" 
                                               value="<?= htmlspecialchars($company['state']) ?>" required>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="business_zip" class="form-label">ZIP/Postal Code *</label>
                                        <input type="text" class="form-control" id="business_zip" name="business_zip" 
                                               value="<?= htmlspecialchars($company['zip']) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="business_country" class="form-label">Country *</label>
                                        <select class="form-select" id="business_country" name="business_country" required>
                                            <option value="US" <?= $company['country'] === 'US' ? 'selected' : '' ?>>United States</option>
                                            <option value="CA" <?= $company['country'] === 'CA' ? 'selected' : '' ?>>Canada</option>
                                            <option value="MX" <?= $company['country'] === 'MX' ? 'selected' : '' ?>>Mexico</option>
                                            <option value="UK" <?= $company['country'] === 'UK' ? 'selected' : '' ?>>United Kingdom</option>
                                            <option value="AU" <?= $company['country'] === 'AU' ? 'selected' : '' ?>>Australia</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Branding Settings -->
                <div class="tab-pane fade" id="branding">
                    <div class="row">
                        <!-- Logo Upload -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Logo & Branding</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="/store/admin/settings/upload-logo" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        
                                        <div class="mb-3">
                                            <label for="logo" class="form-label">Main Logo</label>
                                            <?php if ($company['logo']): ?>
                                            <div class="mb-2">
                                                <img src="<?= htmlspecialchars($company['logo']) ?>" alt="Current Logo" style="max-height: 100px;">
                                            </div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                            <small class="text-muted">Recommended: 400x100px, PNG with transparent background</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="logo_small" class="form-label">Small Logo (Navbar)</label>
                                            <?php if ($company['logo_small']): ?>
                                            <div class="mb-2">
                                                <img src="<?= htmlspecialchars($company['logo_small']) ?>" alt="Current Small Logo" style="max-height: 40px;">
                                            </div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" id="logo_small" name="logo_small" accept="image/*">
                                            <small class="text-muted">Recommended: 100x40px, PNG with transparent background</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="favicon" class="form-label">Favicon</label>
                                            <?php if ($company['favicon']): ?>
                                            <div class="mb-2">
                                                <img src="<?= htmlspecialchars($company['favicon']) ?>" alt="Current Favicon" style="max-height: 32px;">
                                            </div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" id="favicon" name="favicon" accept="image/x-icon,image/png">
                                            <small class="text-muted">Recommended: 32x32px, ICO or PNG format</small>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-upload"></i> Upload
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Color Scheme -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Color Scheme</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="/store/admin/settings/update">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        
                                        <!-- Copy all company info fields as hidden to preserve them -->
                                        <input type="hidden" name="business_name" value="<?= htmlspecialchars($company['name']) ?>">
                                        <input type="hidden" name="business_email" value="<?= htmlspecialchars($company['email']) ?>">
                                        <input type="hidden" name="business_phone" value="<?= htmlspecialchars($company['phone']) ?>">
                                        <input type="hidden" name="business_address" value="<?= htmlspecialchars($company['address']) ?>">
                                        <input type="hidden" name="business_city" value="<?= htmlspecialchars($company['city']) ?>">
                                        <input type="hidden" name="business_state" value="<?= htmlspecialchars($company['state']) ?>">
                                        <input type="hidden" name="business_zip" value="<?= htmlspecialchars($company['zip']) ?>">
                                        <input type="hidden" name="business_country" value="<?= htmlspecialchars($company['country']) ?>">
                                        
                                        <div class="mb-3">
                                            <label for="brand_primary_color" class="form-label">Primary Color</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" id="brand_primary_color" 
                                                       name="brand_primary_color" value="<?= htmlspecialchars($company['primary_color']) ?>">
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($company['primary_color']) ?>" readonly>
                                            </div>
                                            <small class="text-muted">Used for buttons, links, and accents</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="brand_secondary_color" class="form-label">Secondary Color</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" id="brand_secondary_color" 
                                                       name="brand_secondary_color" value="<?= htmlspecialchars($company['secondary_color']) ?>">
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($company['secondary_color']) ?>" readonly>
                                            </div>
                                            <small class="text-muted">Used for gradients and secondary elements</small>
                                        </div>
                                        
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i> Color changes will be reflected on the public website and admin interface.
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Save Colors
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update color text inputs when color picker changes
document.getElementById('brand_primary_color')?.addEventListener('input', function(e) {
    this.nextElementSibling.value = e.target.value;
});

document.getElementById('brand_secondary_color')?.addEventListener('input', function(e) {
    this.nextElementSibling.value = e.target.value;
});
</script>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/admin.php';
?>
