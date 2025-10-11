<?php
$pageTitle = $customer['first_name'] . ' ' . $customer['last_name'];
$activeMenu = 'customers';
$user = currentUser();

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/customers">Customers</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="bi bi-person-circle"></i>
            <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
            <span class="badge bg-<?= $customer['customer_type'] === 'B2B' ? 'primary' : 'secondary' ?> ms-2">
                <?= $customer['customer_type'] ?>
            </span>
        </h2>
        <div>
            <?php if (hasPermission('customers.edit')): ?>
            <a href="/customers/<?= $customer['id'] ?>/edit" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-4" id="customerTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button">
            <i class="bi bi-person"></i> Profile
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="addresses-tab" data-bs-toggle="tab" data-bs-target="#addresses" type="button">
            <i class="bi bi-geo-alt"></i> Addresses (<?= count($addresses) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions" type="button">
            <i class="bi bi-receipt"></i> Transactions (<?= count($transactions) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="certifications-tab" data-bs-toggle="tab" data-bs-target="#certifications" type="button">
            <i class="bi bi-award"></i> Certifications (<?= count($certifications) ?>)
        </button>
    </li>
</ul>

<div class="tab-content" id="customerTabContent">
    <div class="tab-pane fade show active" id="profile" role="tabpanel">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header"><strong>Contact Information</strong></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Email:</th>
                                <td><?= htmlspecialchars($customer['email'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td><?= htmlspecialchars($customer['phone'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Mobile:</th>
                                <td><?= htmlspecialchars($customer['mobile'] ?? '-') ?></td>
                            </tr>
                            <?php if ($customer['customer_type'] === 'B2B'): ?>
                            <tr>
                                <th>Company:</th>
                                <td><?= htmlspecialchars($customer['company_name'] ?? '-') ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
                
                <?php if ($customer['customer_type'] === 'B2C' && (!empty($customer['emergency_contact_name']) || !empty($customer['emergency_contact_phone']))): ?>
                <div class="card mb-3">
                    <div class="card-header"><strong>Emergency Contact</strong></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Name:</th>
                                <td><?= htmlspecialchars($customer['emergency_contact_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td><?= htmlspecialchars($customer['emergency_contact_phone'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-6">
                
                <?php if ($customer['customer_type'] === 'B2B'): ?>
                <div class="card mb-3">
                    <div class="card-header"><strong>Business Details</strong></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Credit Limit:</th>
                                <td><?= formatCurrency($customer['credit_limit'] ?? 0) ?></td>
                            </tr>
                            <tr>
                                <th>Credit Terms:</th>
                                <td><?= htmlspecialchars($customer['credit_terms'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Tax Exempt:</th>
                                <td>
                                    <?php if (!empty($customer['tax_exempt'])): ?>
                                        <span class="badge bg-success">Yes</span>
                                        <?php if (!empty($customer['tax_exempt_number'])): ?>
                                            <br><small><?= htmlspecialchars($customer['tax_exempt_number']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($customer['notes'])): ?>
                <div class="card mb-3">
                    <div class="card-header"><strong>Notes</strong></div>
                    <div class="card-body">
                        <?= nl2br(htmlspecialchars($customer['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="tab-pane fade" id="addresses" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Addresses</h5>
            <?php if (hasPermission('customers.edit')): ?>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                <i class="bi bi-plus-circle"></i> Add Address
            </button>
            <?php endif; ?>
        </div>
        
        <?php if (empty($addresses)): ?>
        <p class="text-muted text-center py-4">No addresses found.</p>
        <?php else: ?>
        <div class="row">
            <?php foreach ($addresses as $addr): ?>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-<?= $addr['address_type'] === 'billing' ? 'primary' : 'success' ?>">
                                    <?= ucfirst($addr['address_type']) ?>
                                </span>
                                <?php if (!empty($addr['is_default'])): ?>
                                <span class="badge bg-warning text-dark">Default</span>
                                <?php endif; ?>
                            </div>
                            <?php if (hasPermission('customers.edit')): ?>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="editAddress(<?= $addr['id'] ?>, <?= htmlspecialchars(json_encode($addr)) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="/customers/<?= $customer['id'] ?>/addresses/<?= $addr['id'] ?>/delete" 
                                      class="d-inline" onsubmit="return confirm('Delete this address?')">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                        <address class="mb-0">
                            <?= htmlspecialchars($addr['address_line1']) ?><br>
                            <?php if (!empty($addr['address_line2'])): ?>
                                <?= htmlspecialchars($addr['address_line2']) ?><br>
                            <?php endif; ?>
                            <?= htmlspecialchars($addr['city'] ?? '') ?>, 
                            <?= htmlspecialchars($addr['state'] ?? '') ?> 
                            <?= htmlspecialchars($addr['postal_code'] ?? '') ?><br>
                            <?= htmlspecialchars($addr['country'] ?? 'US') ?>
                        </address>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="tab-pane fade" id="transactions" role="tabpanel">
        <?php if (empty($transactions)): ?>
        <p class="text-muted text-center py-4">No transactions found.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction #</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($transaction['created_at'])) ?></td>
                        <td><?= htmlspecialchars($transaction['transaction_number'] ?? $transaction['id']) ?></td>
                        <td><?= htmlspecialchars($transaction['payment_method'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-<?= $transaction['status'] === 'completed' ? 'success' : 'warning' ?>">
                                <?= ucfirst($transaction['status']) ?>
                            </span>
                        </td>
                        <td><?= formatCurrency($transaction['total']) ?></td>
                        <td>
                            <a href="/pos/receipt/<?= $transaction['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-receipt"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="tab-pane fade" id="certifications" role="tabpanel">
        <?php if (empty($certifications)): ?>
        <p class="text-muted text-center py-4">No certifications found.</p>
        <?php else: ?>
        <div class="row">
            <?php foreach ($certifications as $cert): ?>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h6><?= htmlspecialchars($cert['certification_level'] ?? 'Certification') ?></h6>
                        <p class="mb-1"><small class="text-muted"><?= htmlspecialchars($cert['agency_name'] ?? 'Unknown Agency') ?></small></p>
                        <p class="mb-1"><small>Number: <?= htmlspecialchars($cert['certification_number'] ?? '-') ?></small></p>
                        <p class="mb-0"><small>Date: <?= !empty($cert['issue_date']) ? date('M d, Y', strtotime($cert['issue_date'])) : 'N/A' ?></small></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/customers/<?= $customer['id'] ?>/addresses">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Address Type</label>
                        <select name="address_type" class="form-select" required>
                            <option value="billing">Billing</option>
                            <option value="shipping">Shipping</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                        <input type="text" name="address_line1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" name="address_line2" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Zip</label>
                            <input type="text" name="postal_code" class="form-control">
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_default" class="form-check-input" id="addIsDefault">
                        <label class="form-check-label" for="addIsDefault">Set as default address</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editAddressForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Address Type</label>
                        <select name="address_type" id="editAddressType" class="form-select" required>
                            <option value="billing">Billing</option>
                            <option value="shipping">Shipping</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                        <input type="text" name="address_line1" id="editAddressLine1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" name="address_line2" id="editAddressLine2" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" id="editCity" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" id="editState" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Zip</label>
                            <input type="text" name="postal_code" id="editPostalCode" class="form-control">
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_default" class="form-check-input" id="editIsDefault">
                        <label class="form-check-label" for="editIsDefault">Set as default address</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$customerId = $customer['id'];
$additionalJs = <<<JS
<script>
function editAddress(addressId, address) {
    document.getElementById('editAddressForm').action = `/customers/{$customerId}/addresses/\${addressId}`;
    document.getElementById('editAddressType').value = address.address_type;
    document.getElementById('editAddressLine1').value = address.address_line1 || '';
    document.getElementById('editAddressLine2').value = address.address_line2 || '';
    document.getElementById('editCity').value = address.city || '';
    document.getElementById('editState').value = address.state || '';
    document.getElementById('editPostalCode').value = address.postal_code || '';
    document.getElementById('editIsDefault').checked = address.is_default == 1;
    
    new bootstrap.Modal(document.getElementById('editAddressModal')).show();
}
</script>
JS;

require __DIR__ . '/../layouts/app.php';
?>
