<?php
$pageTitle = 'My Profile';
ob_start();
?>

<h2>My Profile</h2>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="list-group">
            <a href="/account" class="list-group-item list-group-item-action">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="/account/orders" class="list-group-item list-group-item-action">
                <i class="bi bi-box-seam"></i> My Orders
            </a>
            <a href="/account/profile" class="list-group-item list-group-item-action active">
                <i class="bi bi-person"></i> Profile
            </a>
            <a href="/account/addresses" class="list-group-item list-group-item-action">
                <i class="bi bi-geo-alt"></i> Addresses
            </a>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/account/profile">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?= htmlspecialchars($customer['first_name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?= htmlspecialchars($customer['last_name']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($customer['email']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/account" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/customer.php';
?>
