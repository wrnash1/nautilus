<?php
$pageTitle = 'Edit Customer';
$activeMenu = 'customers';
$user = currentUser();

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/customers">Customers</a></li>
            <li class="breadcrumb-item"><a
                    href="/customers/<?= $customer['id'] ?>"><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></a>
            </li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
    <h2><i class="bi bi-pencil"></i> Edit Customer</h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/customers/<?= $customer['id'] ?>" id="customerForm"
                    enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <div class="mb-3">
                        <label class="form-label">Customer Type <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="customer_type" id="typeB2C"
                                    value="B2C" <?= $customer['customer_type'] === 'B2C' ? 'checked' : '' ?>
                                    onchange="toggleCustomerType()">
                                <label class="form-check-label" for="typeB2C">B2C (Individual)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="customer_type" id="typeB2B"
                                    value="B2B" <?= $customer['customer_type'] === 'B2B' ? 'checked' : '' ?>
                                    onchange="toggleCustomerType()">
                                <label class="form-check-label" for="typeB2B">B2B (Business)</label>
                            </div>
                        </div>
                    </div>

                    <div id="b2bFields"
                        style="display: <?= $customer['customer_type'] === 'B2B' ? 'block' : 'none' ?>;">
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="company_name" name="company_name"
                                value="<?= htmlspecialchars($customer['company_name'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                value="<?= htmlspecialchars($customer['first_name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                value="<?= htmlspecialchars($customer['last_name']) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?= htmlspecialchars($customer['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mobile" class="form-label">Mobile</label>
                            <input type="tel" class="form-control" id="mobile" name="mobile"
                                value="<?= htmlspecialchars($customer['mobile'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3" id="birthDateField">
                            <label for="birth_date" class="form-label">Date of Birth <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date"
                                value="<?= htmlspecialchars($customer['birth_date'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div id="b2cFields"
                        style="display: <?= $customer['customer_type'] === 'B2C' ? 'block' : 'none' ?>;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                                <input type="text" class="form-control" id="emergency_contact_name"
                                    name="emergency_contact_name"
                                    value="<?= htmlspecialchars($customer['emergency_contact_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                                <input type="tel" class="form-control" id="emergency_contact_phone"
                                    name="emergency_contact_phone"
                                    value="<?= htmlspecialchars($customer['emergency_contact_phone'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div id="b2bFieldsExtra"
                        style="display: <?= $customer['customer_type'] === 'B2B' ? 'block' : 'none' ?>;">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="credit_limit" class="form-label">Credit Limit</label>
                                <input type="number" class="form-control" id="credit_limit" name="credit_limit"
                                    step="0.01" value="<?= htmlspecialchars($customer['credit_limit'] ?? 0) ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="credit_terms" class="form-label">Credit Terms</label>
                                <select class="form-select" id="credit_terms" name="credit_terms">
                                    <option value="">None</option>
                                    <option value="Net 15" <?= ($customer['credit_terms'] ?? '') === 'Net 15' ? 'selected' : '' ?>>Net 15</option>
                                    <option value="Net 30" <?= ($customer['credit_terms'] ?? '') === 'Net 30' ? 'selected' : '' ?>>Net 30</option>
                                    <option value="Net 45" <?= ($customer['credit_terms'] ?? '') === 'Net 45' ? 'selected' : '' ?>>Net 45</option>
                                    <option value="Net 60" <?= ($customer['credit_terms'] ?? '') === 'Net 60' ? 'selected' : '' ?>>Net 60</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="tax_exempt" name="tax_exempt"
                                        <?= !empty($customer['tax_exempt']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="tax_exempt">Tax Exempt</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3" id="taxExemptNumberField"
                            style="display: <?= !empty($customer['tax_exempt']) ? 'block' : 'none' ?>;">
                            <label for="tax_exempt_number" class="form-label">Tax Exempt Number</label>
                            <input type="text" class="form-control" id="tax_exempt_number" name="tax_exempt_number"
                                value="<?= htmlspecialchars($customer['tax_exempt_number'] ?? '') ?>">
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3">Billing Address</h5>

                    <div class="mb-3">
                        <label for="address_line1" class="form-label">Address Line 1</label>
                        <input type="text" class="form-control" id="address_line1" name="address_line1"
                            value="<?= htmlspecialchars($address['address_line1'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="address_line2" class="form-label">Address Line 2</label>
                        <input type="text" class="form-control" id="address_line2" name="address_line2"
                            value="<?= htmlspecialchars($address['address_line2'] ?? '') ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city"
                                value="<?= htmlspecialchars($address['city'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                            <select class="form-select" id="state" name="state" required>
                                <option value="">Select State</option>
                                <?php
                                $states = [
                                    'AL' => 'Alabama',
                                    'AK' => 'Alaska',
                                    'AZ' => 'Arizona',
                                    'AR' => 'Arkansas',
                                    'CA' => 'California',
                                    'CO' => 'Colorado',
                                    'CT' => 'Connecticut',
                                    'DE' => 'Delaware',
                                    'FL' => 'Florida',
                                    'GA' => 'Georgia',
                                    'HI' => 'Hawaii',
                                    'ID' => 'Idaho',
                                    'IL' => 'Illinois',
                                    'IN' => 'Indiana',
                                    'IA' => 'Iowa',
                                    'KS' => 'Kansas',
                                    'KY' => 'Kentucky',
                                    'LA' => 'Louisiana',
                                    'ME' => 'Maine',
                                    'MD' => 'Maryland',
                                    'MA' => 'Massachusetts',
                                    'MI' => 'Michigan',
                                    'MN' => 'Minnesota',
                                    'MS' => 'Mississippi',
                                    'MO' => 'Missouri',
                                    'MT' => 'Montana',
                                    'NE' => 'Nebraska',
                                    'NV' => 'Nevada',
                                    'NH' => 'New Hampshire',
                                    'NJ' => 'New Jersey',
                                    'NM' => 'New Mexico',
                                    'NY' => 'New York',
                                    'NC' => 'North Carolina',
                                    'ND' => 'North Dakota',
                                    'OH' => 'Ohio',
                                    'OK' => 'Oklahoma',
                                    'OR' => 'Oregon',
                                    'PA' => 'Pennsylvania',
                                    'RI' => 'Rhode Island',
                                    'SC' => 'South Carolina',
                                    'SD' => 'South Dakota',
                                    'TN' => 'Tennessee',
                                    'TX' => 'Texas',
                                    'UT' => 'Utah',
                                    'VT' => 'Vermont',
                                    'VA' => 'Virginia',
                                    'WA' => 'Washington',
                                    'WV' => 'West Virginia',
                                    'WI' => 'Wisconsin',
                                    'WY' => 'Wyoming',
                                    'DC' => 'District of Columbia'
                                ];
                                $currentState = $address['state'] ?? '';
                                foreach ($states as $code => $name):
                                    ?>
                                    <option value="<?= $code ?>" <?= $currentState === $code ? 'selected' : '' ?>><?= $name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="postal_code" class="form-label">Postal Code <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code"
                                value="<?= htmlspecialchars($address['postal_code'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes"
                            rows="3"><?= htmlspecialchars($customer['notes'] ?? '') ?></textarea>
                    </div>

                    <!-- Customer Photo Upload -->
                    <div class="mb-3">
                        <label for="photo" class="form-label">Customer Photo</label>
                        <?php if (!empty($customer['photo_path'])): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($customer['photo_path']) ?>" alt="Current photo"
                                    class="rounded border" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                <div class="form-text">Current photo</div>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                        <div class="form-text">Upload a new photo (JPG, PNG, GIF - Max 5MB). Leave empty to keep current
                            photo.</div>
                    </div>

                    <!-- Certifications Section -->
                    <h5 class="mt-4 mb-3"><i class="bi bi-award"></i> Diving Certifications</h5>

                    <?php if (!empty($certifications)): ?>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Agency</th>
                                        <th>Level</th>
                                        <th>Cert #</th>
                                        <th>Issue Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certifications as $cert): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($cert['agency_name'] ?? $cert['agency_code'] ?? '-') ?>
                                            </td>
                                            <td><?= htmlspecialchars($cert['certification_level'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($cert['certification_number'] ?? '-') ?></td>
                                            <td><?= $cert['issue_date'] ? date('M j, Y', strtotime($cert['issue_date'])) : '-' ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteCertification(<?= $cert['id'] ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light mb-3">
                            <i class="bi bi-info-circle"></i> No certifications on file.
                        </div>
                    <?php endif; ?>

                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Add Certification</h6>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm">Agency</label>
                                    <select class="form-select form-select-sm" id="cert_agency_id">
                                        <option value="">Select Agency</option>
                                        <?php foreach ($certificationAgencies ?? [] as $agency): ?>
                                            <option value="<?= $agency['id'] ?>"><?= htmlspecialchars($agency['name']) ?>
                                                (<?= htmlspecialchars($agency['code']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm">Level</label>
                                    <input type="text" class="form-control form-control-sm" id="cert_level"
                                        placeholder="e.g., Open Water, Advanced">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm">Cert Number</label>
                                    <input type="text" class="form-control form-control-sm" id="cert_number"
                                        placeholder="Certification #">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm">Issue Date</label>
                                    <input type="date" class="form-control form-control-sm" id="cert_issue_date">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm">Expiration Date</label>
                                    <input type="date" class="form-control form-control-sm" id="cert_expiration_date">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-sm btn-success w-100"
                                        onclick="addCertification()">
                                        <i class="bi bi-plus"></i> Add Certification
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Waivers Link -->
                    <div class="mb-4">
                        <a href="/customers/<?= $customer['id'] ?>/waivers" class="btn btn-outline-secondary">
                            <i class="bi bi-file-earmark-text"></i> View/Manage Waivers
                        </a>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/customers/<?= $customer['id'] ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleCustomerType() {
        const isB2B = document.getElementById('typeB2B').checked;
        document.getElementById('b2bFields').style.display = isB2B ? 'block' : 'none';
        document.getElementById('b2bFieldsExtra').style.display = isB2B ? 'block' : 'none';
        document.getElementById('b2cFields').style.display = isB2B ? 'none' : 'block';

        if (isB2B) {
            document.getElementById('company_name').required = true;
        } else {
            document.getElementById('company_name').required = false;
        }
    }

    document.getElementById('tax_exempt').addEventListener('change', function () {
        document.getElementById('taxExemptNumberField').style.display = this.checked ? 'block' : 'none';
    });

    function addCertification() {
        const agencyId = document.getElementById('cert_agency_id').value;
        const level = document.getElementById('cert_level').value;
        const certNumber = document.getElementById('cert_number').value;
        const issueDate = document.getElementById('cert_issue_date').value;
        const expirationDate = document.getElementById('cert_expiration_date').value;

        if (!agencyId || !level) {
            alert('Please select an agency and enter a certification level.');
            return;
        }

        const formData = new FormData();
        formData.append('certification_agency_id', agencyId);
        formData.append('certification_level', level);
        formData.append('certification_number', certNumber);
        formData.append('issue_date', issueDate);
        formData.append('expiration_date', expirationDate);

        fetch('/customers/<?= $customer['id'] ?>/certifications', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Failed to add certification');
                }
            })
            .catch(error => {
                alert('Error adding certification: ' + error);
            });
    }

    function deleteCertification(certId) {
        if (!confirm('Are you sure you want to delete this certification?')) {
            return;
        }

        fetch('/customers/<?= $customer['id'] ?>/certifications/' + certId, {
            method: 'DELETE'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Failed to delete certification');
                }
            })
            .catch(error => {
                alert('Error deleting certification: ' + error);
            });
    }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>