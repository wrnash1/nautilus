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
    <div class="d-flex justify-content-between align-items-start">
        <div class="d-flex align-items-start">
            <!-- Customer Photo -->
            <div class="me-3">
                <?php if (!empty($customer['photo_path'])): ?>
                    <img src="<?= htmlspecialchars($customer['photo_path']) ?>"
                         alt="<?= htmlspecialchars($customer['first_name']) ?>"
                         class="rounded-circle border border-3 border-primary"
                         style="width: 100px; height: 100px; object-fit: cover;">
                <?php else: ?>
                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white"
                         style="width: 100px; height: 100px; font-size: 2.5rem;">
                        <?= strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Customer Info -->
            <div>
                <h2 class="mb-2">
                    <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                    <span class="badge bg-<?= $customer['customer_type'] === 'B2B' ? 'primary' : 'secondary' ?> ms-2">
                        <?= $customer['customer_type'] ?>
                    </span>
                </h2>

                <!-- Highest Certification Badge -->
                <?php if (!empty($highestCert)): ?>
                <div class="mb-2">
                    <span class="badge" style="background-color: <?= htmlspecialchars($highestCert['primary_color'] ?? '#0066CC') ?>; font-size: 0.9rem; padding: 0.5rem 0.75rem;">
                        <?php if (!empty($highestCert['logo_path'])): ?>
                            <img src="<?= htmlspecialchars($highestCert['logo_path']) ?>"
                                 alt="<?= htmlspecialchars($highestCert['agency_name']) ?>"
                                 style="height: 16px; vertical-align: middle; margin-right: 5px;">
                        <?php endif; ?>
                        <?= htmlspecialchars($highestCert['certification_name']) ?>
                        <?php if ($highestCert['verification_status'] === 'verified'): ?>
                            <i class="bi bi-patch-check-fill ms-1"></i>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>

                <p class="text-muted mb-1">
                    <i class="bi bi-envelope"></i> <?= htmlspecialchars($customer['email'] ?? 'No email') ?>
                    <?php if (!empty($customer['phone'])): ?>
                        | <i class="bi bi-telephone"></i> <?= htmlspecialchars($customer['phone']) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

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
        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button">
            <i class="bi bi-phone"></i> Contact Info
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="addresses-tab" data-bs-toggle="tab" data-bs-target="#addresses" type="button">
            <i class="bi bi-geo-alt"></i> Addresses (<?= count($addresses) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="travel-tab" data-bs-toggle="tab" data-bs-target="#travel" type="button">
            <i class="bi bi-airplane"></i> Travel Info
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="certifications-tab" data-bs-toggle="tab" data-bs-target="#certifications" type="button">
            <i class="bi bi-award"></i> Certifications (<?= count($certifications) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions" type="button">
            <i class="bi bi-receipt"></i> Transactions (<?= count($transactions) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tags-tab" data-bs-toggle="tab" data-bs-target="#tags" type="button">
            <i class="bi bi-tags"></i> Tags
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
        <div class="text-center py-5">
            <i class="bi bi-award" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">No certifications found.</p>
            <?php if (hasPermission('customers.edit')): ?>
            <button class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Add Certification
            </button>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($certifications as $cert): ?>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 shadow-sm cert-card"
                     style="border-left: 4px solid <?= htmlspecialchars($cert['primary_color'] ?? '#0066CC') ?>;">
                    <div class="card-body">
                        <!-- Agency Logo and Badge -->
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div>
                                <?php if (!empty($cert['logo_path'])): ?>
                                    <img src="<?= htmlspecialchars($cert['logo_path']) ?>"
                                         alt="<?= htmlspecialchars($cert['agency_abbreviation']) ?>"
                                         style="height: 40px; max-width: 120px; object-fit: contain;">
                                <?php else: ?>
                                    <span class="badge" style="background-color: <?= htmlspecialchars($cert['primary_color'] ?? '#0066CC') ?>; font-size: 0.9rem;">
                                        <?= htmlspecialchars($cert['agency_abbreviation'] ?? 'N/A') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php
                                $statusColors = [
                                    'verified' => 'success',
                                    'pending' => 'warning',
                                    'expired' => 'danger',
                                    'invalid' => 'secondary'
                                ];
                                $statusColor = $statusColors[$cert['verification_status']] ?? 'secondary';
                                $statusIcons = [
                                    'verified' => 'patch-check-fill',
                                    'pending' => 'hourglass-split',
                                    'expired' => 'exclamation-triangle-fill',
                                    'invalid' => 'x-circle-fill'
                                ];
                                $statusIcon = $statusIcons[$cert['verification_status']] ?? 'question-circle';
                                ?>
                                <span class="badge bg-<?= $statusColor ?>">
                                    <i class="bi bi-<?= $statusIcon ?>"></i>
                                    <?= ucfirst($cert['verification_status'] ?? 'Unknown') ?>
                                </span>
                            </div>
                        </div>

                        <!-- Certification Name -->
                        <h5 class="card-title mb-2">
                            <?= htmlspecialchars($cert['certification_name'] ?? 'Certification') ?>
                        </h5>

                        <!-- Level Badge -->
                        <?php if (!empty($cert['certification_level'])): ?>
                        <div class="mb-2">
                            <span class="badge bg-light text-dark border">
                                Level <?= $cert['certification_level'] ?>
                                <?php if (!empty($cert['certification_code'])): ?>
                                    | <?= htmlspecialchars($cert['certification_code']) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <!-- Details -->
                        <div class="mt-3">
                            <?php if (!empty($cert['certification_number'])): ?>
                            <p class="mb-1 small">
                                <strong><i class="bi bi-hash"></i> Cert #:</strong>
                                <span class="text-monospace"><?= htmlspecialchars($cert['certification_number']) ?></span>
                            </p>
                            <?php endif; ?>

                            <?php if (!empty($cert['issue_date'])): ?>
                            <p class="mb-1 small">
                                <strong><i class="bi bi-calendar-check"></i> Issued:</strong>
                                <?= date('M d, Y', strtotime($cert['issue_date'])) ?>
                            </p>
                            <?php endif; ?>

                            <?php if (!empty($cert['expiry_date'])): ?>
                            <p class="mb-1 small">
                                <strong><i class="bi bi-calendar-x"></i> Expires:</strong>
                                <?php
                                $expiryDate = strtotime($cert['expiry_date']);
                                $today = time();
                                $daysUntilExpiry = floor(($expiryDate - $today) / 86400);
                                $expiryClass = $daysUntilExpiry < 0 ? 'text-danger' : ($daysUntilExpiry < 90 ? 'text-warning' : 'text-success');
                                ?>
                                <span class="<?= $expiryClass ?>">
                                    <?= date('M d, Y', $expiryDate) ?>
                                    <?php if ($daysUntilExpiry < 0): ?>
                                        (Expired)
                                    <?php elseif ($daysUntilExpiry < 90): ?>
                                        (<?= $daysUntilExpiry ?> days left)
                                    <?php endif; ?>
                                </span>
                            </p>
                            <?php endif; ?>

                            <?php if (!empty($cert['instructor_name'])): ?>
                            <p class="mb-1 small">
                                <strong><i class="bi bi-person"></i> Instructor:</strong>
                                <?= htmlspecialchars($cert['instructor_name']) ?>
                            </p>
                            <?php endif; ?>

                            <?php if (!empty($cert['verified_at']) && $cert['verification_status'] === 'verified'): ?>
                            <p class="mb-0 small text-success">
                                <i class="bi bi-shield-check"></i>
                                Verified <?= date('M d, Y', strtotime($cert['verified_at'])) ?>
                            </p>
                            <?php endif; ?>
                        </div>

                        <!-- C-Card Images -->
                        <?php if (!empty($cert['c_card_front_path']) || !empty($cert['c_card_back_path'])): ?>
                        <div class="mt-3 pt-3 border-top">
                            <p class="small text-muted mb-2"><i class="bi bi-card-image"></i> C-Card Images:</p>
                            <div class="d-flex gap-2">
                                <?php if (!empty($cert['c_card_front_path'])): ?>
                                <a href="<?= htmlspecialchars($cert['c_card_front_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Front
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($cert['c_card_back_path'])): ?>
                                <a href="<?= htmlspecialchars($cert['c_card_back_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Back
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Summary Stats -->
        <div class="mt-4 p-3 bg-light rounded">
            <div class="row text-center">
                <div class="col-md-3">
                    <h4 class="mb-0"><?= count($certifications) ?></h4>
                    <small class="text-muted">Total Certifications</small>
                </div>
                <div class="col-md-3">
                    <h4 class="mb-0">
                        <?= count(array_filter($certifications, fn($c) => $c['verification_status'] === 'verified')) ?>
                    </h4>
                    <small class="text-muted">Verified</small>
                </div>
                <div class="col-md-3">
                    <h4 class="mb-0">
                        <?= !empty($highestCert) ? $highestCert['certification_level'] : 0 ?>
                    </h4>
                    <small class="text-muted">Highest Level</small>
                </div>
                <div class="col-md-3">
                    <h4 class="mb-0"><?= count(array_unique(array_column($certifications, 'agency_id'))) ?></h4>
                    <small class="text-muted">Agencies</small>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="tab-pane fade" id="contact" role="tabpanel">
        <!-- Phones -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-telephone"></i> Phone Numbers</h5>
                <?php if (hasPermission('customers.edit')): ?>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPhoneModal">
                    <i class="bi bi-plus-circle"></i> Add Phone
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php
                // Fetch phones - in real implementation, controller would pass this
                $phones = [];
                ?>
                <?php if (empty($phones)): ?>
                <p class="text-muted">No phone numbers on file.</p>
                <?php else: ?>
                <div class="list-group">
                    <?php foreach ($phones as $phone): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong><?= htmlspecialchars($phone['phone_number']) ?></strong>
                                <span class="badge bg-primary"><?= ucfirst($phone['phone_type']) ?></span>
                                <?php if ($phone['is_primary']): ?><span class="badge bg-success">Primary</span><?php endif; ?>
                            </div>
                            <?php if (hasPermission('customers.edit')): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletePhone(<?= $phone['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Emails -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-envelope"></i> Email Addresses</h5>
                <?php if (hasPermission('customers.edit')): ?>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEmailModal">
                    <i class="bi bi-plus-circle"></i> Add Email
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php
                // Fetch emails - in real implementation, controller would pass this
                $emails = [];
                ?>
                <?php if (empty($emails)): ?>
                <p class="text-muted">No email addresses on file.</p>
                <?php else: ?>
                <div class="list-group">
                    <?php foreach ($emails as $email): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong><?= htmlspecialchars($email['email_address']) ?></strong>
                                <span class="badge bg-primary"><?= ucfirst($email['email_type']) ?></span>
                                <?php if ($email['is_primary']): ?><span class="badge bg-success">Primary</span><?php endif; ?>
                            </div>
                            <?php if (hasPermission('customers.edit')): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteEmail(<?= $email['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Emergency Contacts -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Emergency Contacts</h5>
                <?php if (hasPermission('customers.edit')): ?>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addContactModal">
                    <i class="bi bi-plus-circle"></i> Add Contact
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php
                // Fetch contacts - in real implementation, controller would pass this
                $contacts = [];
                ?>
                <?php if (empty($contacts)): ?>
                <p class="text-muted">No emergency contacts on file.</p>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($contacts as $contact): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6><?= htmlspecialchars($contact['contact_name']) ?></h6>
                                <p class="mb-1"><small><strong>Relationship:</strong> <?= htmlspecialchars($contact['relationship']) ?></small></p>
                                <p class="mb-1"><small><strong>Phone:</strong> <?= htmlspecialchars($contact['contact_phone']) ?></small></p>
                                <?php if ($contact['is_primary']): ?><span class="badge bg-warning">Primary</span><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="travel" role="tabpanel">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header"><strong>Passport Information</strong></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Passport Number:</th>
                                <td><?= htmlspecialchars($customer['passport_number'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Expiration Date:</th>
                                <td>
                                    <?php if (!empty($customer['passport_expiration'])): ?>
                                        <?= date('M d, Y', strtotime($customer['passport_expiration'])) ?>
                                        <?php
                                        $daysUntilExpiry = floor((strtotime($customer['passport_expiration']) - time()) / 86400);
                                        if ($daysUntilExpiry < 0): ?>
                                            <span class="badge bg-danger">Expired</span>
                                        <?php elseif ($daysUntilExpiry < 180): ?>
                                            <span class="badge bg-warning">Expires Soon</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><strong>Physical Information</strong></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Height:</th>
                                <td><?= !empty($customer['height']) ? $customer['height'] . ' cm' : '-' ?></td>
                            </tr>
                            <tr>
                                <th>Weight:</th>
                                <td><?= !empty($customer['weight']) ? $customer['weight'] . ' kg' : '-' ?></td>
                            </tr>
                            <tr>
                                <th>Shoe Size:</th>
                                <td><?= htmlspecialchars($customer['shoe_size'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Wetsuit Size:</th>
                                <td><?= htmlspecialchars($customer['wetsuit_size'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header"><strong>Medical Information</strong></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Allergies:</label>
                            <p class="text-muted"><?= !empty($customer['allergies']) ? nl2br(htmlspecialchars($customer['allergies'])) : 'None reported' ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Medications:</label>
                            <p class="text-muted"><?= !empty($customer['medications']) ? nl2br(htmlspecialchars($customer['medications'])) : 'None reported' ?></p>
                        </div>
                        <div>
                            <label class="form-label fw-bold">Medical Notes:</label>
                            <p class="text-muted"><?= !empty($customer['medical_notes']) ? nl2br(htmlspecialchars($customer['medical_notes'])) : 'None' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (hasPermission('customers.edit')): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            To update travel and medical information, <a href="/store/customers/<?= $customer['id'] ?>/edit">edit the customer profile</a>.
        </div>
        <?php endif; ?>
    </div>

    <div class="tab-pane fade" id="tags" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-tags"></i> Customer Tags</h5>
                <?php if (hasPermission('customers.edit')): ?>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignTagModal">
                    <i class="bi bi-plus-circle"></i> Assign Tag
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php
                // Fetch customer tags - in real implementation, controller would pass this
                $customerTags = [];
                ?>
                <?php if (empty($customerTags)): ?>
                <p class="text-muted">No tags assigned to this customer.</p>
                <?php else: ?>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($customerTags as $tag): ?>
                    <div class="position-relative">
                        <span class="badge" style="background-color: <?= htmlspecialchars($tag['color']) ?>; color: white; font-size: 1rem; padding: 8px 12px;">
                            <?php if ($tag['icon']): ?>
                            <i class="<?= htmlspecialchars($tag['icon']) ?>"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($tag['name']) ?>
                        </span>
                        <?php if (hasPermission('customers.edit')): ?>
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 start-100 translate-middle badge rounded-pill"
                                onclick="removeTag(<?= $tag['id'] ?>)" style="padding: 2px 6px;">
                            <i class="bi bi-x"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4">
                    <?php foreach ($customerTags as $tag): ?>
                    <?php if (!empty($tag['notes'])): ?>
                    <div class="alert alert-light">
                        <strong><?= htmlspecialchars($tag['name']) ?>:</strong>
                        <?= htmlspecialchars($tag['notes']) ?>
                        <br><small class="text-muted">Assigned by <?= htmlspecialchars($tag['assigned_by_name']) ?> on <?= date('M d, Y', strtotime($tag['assigned_at'])) ?></small>
                    </div>
                    <?php endif; ?>
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
