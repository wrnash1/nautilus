<?php
$pageTitle = 'Edit Vendor';
$activeMenu = 'vendors';

ob_start();
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/vendors">Vendors</a></li>
        <li class="breadcrumb-item active">Edit Vendor</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-building"></i> Edit Vendor</h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/vendors/<?= $vendor['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <h5 class="mb-3">Company Information</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Company Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($vendor['name']) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="contact_name" class="form-label">Contact Name</label>
                        <input type="text" class="form-control" id="contact_name" name="contact_name" 
                               value="<?= htmlspecialchars($vendor['contact_name'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($vendor['email'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?= htmlspecialchars($vendor['phone'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="website" class="form-label">Website</label>
                <input type="url" class="form-control" id="website" name="website" 
                       value="<?= htmlspecialchars($vendor['website'] ?? '') ?>">
            </div>
            
            <h5 class="mb-3 mt-4">Address</h5>
            <div class="mb-3">
                <label for="address_line1" class="form-label">Address Line 1</label>
                <input type="text" class="form-control" id="address_line1" name="address_line1" 
                       value="<?= htmlspecialchars($vendor['address_line1'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="address_line2" class="form-label">Address Line 2</label>
                <input type="text" class="form-control" id="address_line2" name="address_line2" 
                       value="<?= htmlspecialchars($vendor['address_line2'] ?? '') ?>">
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control" id="city" name="city" 
                               value="<?= htmlspecialchars($vendor['city'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="state" class="form-label">State</label>
                        <input type="text" class="form-control" id="state" name="state" 
                               value="<?= htmlspecialchars($vendor['state'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="postal_code" class="form-label">Postal Code</label>
                        <input type="text" class="form-control" id="postal_code" name="postal_code" 
                               value="<?= htmlspecialchars($vendor['postal_code'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="country" class="form-label">Country</label>
                <input type="text" class="form-control" id="country" name="country" 
                       value="<?= htmlspecialchars($vendor['country'] ?? 'US') ?>">
            </div>
            
            <h5 class="mb-3 mt-4">Payment Information</h5>
            <div class="mb-3">
                <label for="payment_terms" class="form-label">Payment Terms</label>
                <input type="text" class="form-control" id="payment_terms" name="payment_terms" 
                       value="<?= htmlspecialchars($vendor['payment_terms'] ?? '') ?>" placeholder="e.g., Net 30">
            </div>
            
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($vendor['notes'] ?? '') ?></textarea>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                           <?= $vendor['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Vendor
                </button>
                <a href="/vendors/<?= $vendor['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
