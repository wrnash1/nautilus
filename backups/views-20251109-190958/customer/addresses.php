<?php
$pageTitle = 'My Addresses';
ob_start();
?>

<h2>My Addresses</h2>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="list-group">
            <a href="/account" class="list-group-item list-group-item-action">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="/account/orders" class="list-group-item list-group-item-action">
                <i class="bi bi-box-seam"></i> My Orders
            </a>
            <a href="/account/profile" class="list-group-item list-group-item-action">
                <i class="bi bi-person"></i> Profile
            </a>
            <a href="/account/addresses" class="list-group-item list-group-item-action active">
                <i class="bi bi-geo-alt"></i> Addresses
            </a>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Saved Addresses</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                    <i class="bi bi-plus-circle"></i> Add Address
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($addresses)): ?>
                    <p class="text-muted text-center py-4">No saved addresses.</p>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($addresses as $address): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <?php if ($address['is_default']): ?>
                                        <span class="badge bg-primary mb-2">Default</span>
                                    <?php endif; ?>
                                    <p class="mb-1"><strong><?= ucfirst($address['address_type']) ?> Address</strong></p>
                                    <p class="mb-1"><?= htmlspecialchars($address['address_line1']) ?></p>
                                    <?php if (!empty($address['address_line2'])): ?>
                                        <p class="mb-1"><?= htmlspecialchars($address['address_line2']) ?></p>
                                    <?php endif; ?>
                                    <p class="mb-1">
                                        <?= htmlspecialchars($address['city']) ?>, 
                                        <?= htmlspecialchars($address['state']) ?> 
                                        <?= htmlspecialchars($address['postal_code']) ?>
                                    </p>
                                    <p class="mb-2"><?= htmlspecialchars($address['country']) ?></p>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" 
                                                onclick="editAddress(<?= htmlspecialchars(json_encode($address)) ?>)">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <form method="POST" action="/account/addresses/<?= $address['id'] ?>/delete" class="d-inline"
                                              onsubmit="return confirm('Delete this address?')">
                                            <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/account/addresses">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                    
                    <div class="mb-3">
                        <label for="address_type" class="form-label">Address Type</label>
                        <select class="form-select" id="address_type" name="address_type">
                            <option value="both">Billing & Shipping</option>
                            <option value="billing">Billing Only</option>
                            <option value="shipping">Shipping Only</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address_line1" class="form-label">Address Line 1 *</label>
                        <input type="text" class="form-control" id="address_line1" name="address_line1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address_line2" class="form-label">Address Line 2</label>
                        <input type="text" class="form-control" id="address_line2" name="address_line2">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City *</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="state" class="form-label">State *</label>
                            <input type="text" class="form-control" id="state" name="state" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="postal_code" class="form-label">Postal Code *</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" value="US">
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                        <label class="form-check-label" for="is_default">
                            Set as default address
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editAddressForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                    
                    <div class="mb-3">
                        <label for="edit_address_type" class="form-label">Address Type</label>
                        <select class="form-select" id="edit_address_type" name="address_type">
                            <option value="both">Billing & Shipping</option>
                            <option value="billing">Billing Only</option>
                            <option value="shipping">Shipping Only</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_address_line1" class="form-label">Address Line 1 *</label>
                        <input type="text" class="form-control" id="edit_address_line1" name="address_line1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_address_line2" class="form-label">Address Line 2</label>
                        <input type="text" class="form-control" id="edit_address_line2" name="address_line2">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_city" class="form-label">City *</label>
                            <input type="text" class="form-control" id="edit_city" name="city" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_state" class="form-label">State *</label>
                            <input type="text" class="form-control" id="edit_state" name="state" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_postal_code" class="form-label">Postal Code *</label>
                            <input type="text" class="form-control" id="edit_postal_code" name="postal_code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="edit_country" name="country">
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit_is_default" name="is_default">
                        <label class="form-check-label" for="edit_is_default">
                            Set as default address
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editAddress(address) {
    document.getElementById('editAddressForm').action = '/account/addresses/' + address.id;
    document.getElementById('edit_address_type').value = address.address_type;
    document.getElementById('edit_address_line1').value = address.address_line1;
    document.getElementById('edit_address_line2').value = address.address_line2 || '';
    document.getElementById('edit_city').value = address.city;
    document.getElementById('edit_state').value = address.state;
    document.getElementById('edit_postal_code').value = address.postal_code;
    document.getElementById('edit_country').value = address.country;
    document.getElementById('edit_is_default').checked = address.is_default == 1;
    
    var editModal = new bootstrap.Modal(document.getElementById('editAddressModal'));
    editModal.show();
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/customer.php';
?>
