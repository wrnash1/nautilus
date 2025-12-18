<?php
$pageTitle = 'Add Customer';
$activeMenu = 'customers';
$user = currentUser();

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/store/customers">Customers</a></li>
            <li class="breadcrumb-item active">Add Customer</li>
        </ol>
    </nav>
    <h2><i class="bi bi-person-plus"></i> Add Customer</h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/store/customers" id="customerForm" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <?php if (!empty($_GET['return_to'])): ?>
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($_GET['return_to']) ?>">
            <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Customer Type <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="customer_type" 
                                       id="typeB2C" value="B2C" checked onchange="toggleCustomerType()">
                                <label class="form-check-label" for="typeB2C">B2C (Individual)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="customer_type" 
                                       id="typeB2B" value="B2B" onchange="toggleCustomerType()">
                                <label class="form-check-label" for="typeB2B">B2B (Business)</label>
                            </div>
                        </div>
                    </div>
                    
                    <div id="b2bFields" style="display: none;">
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="company_name" name="company_name">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mobile" class="form-label">Mobile</label>
                            <input type="tel" class="form-control" id="mobile" name="mobile">
                        </div>
                        <div class="col-md-6 mb-3" id="birthDateField">
                            <label for="birth_date" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date" required>
                        </div>
                    </div>
                    
                    <div id="b2cFields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                                <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                                <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone">
                            </div>
                        </div>
                    </div>
                    
                    <div id="b2bFieldsExtra" style="display: none;">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="credit_limit" class="form-label">Credit Limit</label>
                                <input type="number" class="form-control" id="credit_limit" name="credit_limit" step="0.01" value="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="credit_terms" class="form-label">Credit Terms</label>
                                <select class="form-select" id="credit_terms" name="credit_terms">
                                    <option value="">None</option>
                                    <option value="Net 15">Net 15</option>
                                    <option value="Net 30">Net 30</option>
                                    <option value="Net 45">Net 45</option>
                                    <option value="Net 60">Net 60</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="tax_exempt" name="tax_exempt">
                                    <label class="form-check-label" for="tax_exempt">Tax Exempt</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3" id="taxExemptNumberField" style="display: none;">
                            <label for="tax_exempt_number" class="form-label">Tax Exempt Number</label>
                            <input type="text" class="form-control" id="tax_exempt_number" name="tax_exempt_number">
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Billing Address</h5>
                    
                    <div class="mb-3">
                        <label for="address_line1" class="form-label">Address Line 1</label>
                        <input type="text" class="form-control" id="address_line1" name="address_line1">
                    </div>
                    
                    <div class="mb-3">
                        <label for="address_line2" class="form-label">Address Line 2</label>
                        <input type="text" class="form-control" id="address_line2" name="address_line2">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                            <select class="form-select" id="state" name="state" required>
                                <option value="">Select State</option>
                                <?php
                                $states = [
                                    'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
                                    'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
                                    'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
                                    'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
                                    'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
                                    'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
                                    'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
                                    'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
                                    'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
                                    'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
                                    'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
                                    'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
                                    'WI' => 'Wisconsin', 'WY' => 'Wyoming', 'DC' => 'District of Columbia'
                                ];
                                foreach ($states as $code => $name):
                                ?>
                                <option value="<?= $code ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="postal_code" class="form-label">Postal Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>

                    <!-- Customer Photo Upload -->
                    <div class="mb-3">
                        <label for="photo" class="form-label">Customer Photo</label>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                        <div class="form-text">Upload customer photo (JPG, PNG, GIF - Max 5MB). Optional.</div>
                    </div>

                    <h5 class="mt-4 mb-3">Primary Certification (Optional)</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="certification_agency_id" class="form-label">Agency</label>
                            <select class="form-select" id="certification_agency_id" name="certification_agency_id">
                                <option value="">Select Agency</option>
                                <?php if (!empty($certificationAgencies)): ?>
                                    <?php foreach ($certificationAgencies as $agency): ?>
                                        <option value="<?= $agency['id'] ?>"><?= htmlspecialchars($agency['name']) ?> (<?= htmlspecialchars($agency['code']) ?>)</option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="certification_level" class="form-label">Level</label>
                            <input type="text" class="form-control" id="certification_level" name="certification_level" placeholder="e.g. Open Water Diver">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="certification_number" class="form-label">Certification #</label>
                            <input type="text" class="form-control" id="certification_number" name="certification_number">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="certification_issue_date" class="form-label">Issue Date</label>
                            <input type="date" class="form-control" id="certification_issue_date" name="certification_issue_date">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="/store/customers" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Create Customer
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

document.getElementById('tax_exempt').addEventListener('change', function() {
    document.getElementById('taxExemptNumberField').style.display = this.checked ? 'block' : 'none';
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
