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
                <form method="POST" action="/store/customers" id="customerForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
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
                            <label for="birth_date" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date">
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
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
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
