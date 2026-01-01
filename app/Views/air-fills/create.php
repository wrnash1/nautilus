<?php
$pageTitle = 'New Air Fill';
$activeMenu = 'air-fills';

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/store/air-fills">Air Fills</a></li>
            <li class="breadcrumb-item active">New Air Fill</li>
        </ol>
    </nav>

    <h1 class="h3">
        <i class="bi bi-wind"></i> Record New Air Fill
    </h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/store/air-fills" id="airFillForm">
                    <!-- Customer Selection -->
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">
                            Customer <span class="text-muted">(Optional for walk-ins)</span>
                        </label>
                        <select name="customer_id" id="customer_id" class="form-select">
                            <option value="">-- Walk-in Customer --</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>">
                                <?= htmlspecialchars($customer['name']) ?> - <?= htmlspecialchars($customer['email']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Select a customer to create a transaction automatically</small>
                    </div>

                    <div class="row">
                        <!-- Fill Type -->
                        <div class="col-md-6 mb-3">
                            <label for="fill_type" class="form-label">Fill Type <span class="text-danger">*</span></label>
                            <select name="fill_type" id="fill_type" class="form-select" required>
                                <option value="air" selected>Air</option>
                                <option value="nitrox">Nitrox (EAN)</option>
                                <option value="trimix">Trimix</option>
                                <option value="oxygen">Oxygen</option>
                            </select>
                        </div>

                        <!-- Nitrox Percentage (shown only for nitrox) -->
                        <div class="col-md-6 mb-3" id="nitrox_field" style="display: none;">
                            <label for="nitrox_percentage" class="form-label">Nitrox Percentage (O2%)</label>
                            <input type="number" name="nitrox_percentage" id="nitrox_percentage"
                                   class="form-control" min="21" max="40" value="32">
                            <small class="text-muted">Typical: 32% or 36%</small>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Fill Pressure -->
                        <div class="col-md-6 mb-3">
                            <label for="fill_pressure" class="form-label">Fill Pressure (PSI) <span class="text-danger">*</span></label>
                            <input type="number" name="fill_pressure" id="fill_pressure"
                                   class="form-control" value="3000" min="500" max="4500" required>
                            <small class="text-muted">Standard: 3000 PSI</small>
                        </div>

                        <!-- Cost -->
                        <div class="col-md-6 mb-3">
                            <label for="cost" class="form-label">Cost ($) <span class="text-danger">*</span></label>
                            <input type="number" name="cost" id="cost" class="form-control"
                                   value="8.00" min="0" step="0.01" required>
                            <small class="text-muted" id="suggested_price"></small>
                        </div>
                    </div>

                    <!-- Compressor Tracking -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="compressor_id" class="form-label">Compressor Used <span class="text-danger">*</span></label>
                            <select name="compressor_id" id="compressor_id" class="form-select" required>
                                <option value="">-- Select Compressor --</option>
                                <?php foreach ($compressors as $comp): ?>
                                    <option value="<?= $comp['id'] ?>"><?= htmlspecialchars($comp['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="run_time" class="form-label">Run Time (Minutes)</label>
                            <input type="number" name="run_time_minutes" id="run_time" class="form-control" min="0" placeholder="e.g. 15">
                            <small class="text-muted">Updates compressor hours automatically</small>
                        </div>
                    </div>

                    <hr>

                    <!-- Equipment Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tank Source</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="equipment_source" id="source_rental" value="rental" checked>
                                <label class="form-check-label" for="source_rental">Rental Tank</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="equipment_source" id="source_customer" value="customer">
                                <label class="form-check-label" for="source_customer">Customer Owned</label>
                            </div>
                        </div>
                    </div>

                    <!-- Rental Tank Select -->
                    <div class="mb-3" id="rental_equip_div">
                        <label for="equipment_id" class="form-label">Rental Tank</label>
                        <select name="equipment_id" id="equipment_id" class="form-select">
                            <option value="">-- Select Rental Tank --</option>
                            <?php foreach ($tanks as $tank): ?>
                            <option value="<?= $tank['id'] ?>">
                                <?= htmlspecialchars($tank['name']) ?> - <?= htmlspecialchars($tank['equipment_code']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Customer Tank Select -->
                    <div class="mb-3" id="customer_equip_div" style="display: none;">
                        <label for="customer_equipment_id" class="form-label">Customer Tank</label>
                        <select name="customer_equipment_id" id="customer_equipment_id" class="form-select">
                            <option value="">-- Select Customer First --</option>
                        </select>
                        <!-- Alert Box for Validation Status -->
                        <div id="tank_status_alert" style="display:none;" class="alert mt-2"></div>
                        <small class="text-muted">Requires customer selection above.</small>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"
                                  placeholder="Any additional notes about this fill..."></textarea>
                    </div>

                    <!-- Create Transaction Checkbox -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="create_transaction"
                                   id="create_transaction" checked>
                            <label class="form-check-label" for="create_transaction">
                                <strong>Create transaction and charge customer</strong>
                            </label>
                            <br>
                            <small class="text-muted">
                                If checked, a POS transaction will be created automatically (requires customer selection)
                            </small>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Record Air Fill
                        </button>
                        <a href="/store/air-fills" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar: Quick Reference -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Fill Type Guide</h6>
            </div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt>Air</dt>
                    <dd>Standard compressed air (21% O2)</dd>

                    <dt>Nitrox (EAN)</dt>
                    <dd>Enriched Air Nitrox (22-40% O2). Common: EAN32, EAN36</dd>

                    <dt>Trimix</dt>
                    <dd>Helium/Oxygen/Nitrogen mix for deep diving</dd>

                    <dt>Oxygen</dt>
                    <dd>100% oxygen for decompression</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-currency-dollar"></i> Standard Pricing</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td>Air Fill</td>
                            <td class="text-end"><strong>$8.00</strong></td>
                        </tr>
                        <tr>
                            <td>Nitrox Fill</td>
                            <td class="text-end"><strong>$12.00</strong></td>
                        </tr>
                        <tr>
                            <td>Trimix Fill</td>
                            <td class="text-end"><strong>$25.00</strong></td>
                        </tr>
                        <tr>
                            <td>Oxygen Fill</td>
                            <td class="text-end"><strong>$15.00</strong></td>
                        </tr>
                    </tbody>
                </table>
                <small class="text-muted">* Prices adjust based on pressure</small>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fillTypeSelect = document.getElementById('fill_type');
    const nitroxField = document.getElementById('nitrox_field');
    const nitroxInput = document.getElementById('nitrox_percentage');
    const pressureInput = document.getElementById('fill_pressure');
    const costInput = document.getElementById('cost');
    const suggestedPrice = document.getElementById('suggested_price');
    
    // New Elements
    const customerSelect = document.getElementById('customer_id');
    const equipSourceRadios = document.getElementsByName('equipment_source');
    const rentalDiv = document.getElementById('rental_equip_div');
    const customerDiv = document.getElementById('customer_equip_div');
    const customerEquipSelect = document.getElementById('customer_equipment_id');
    const tankStatusAlert = document.getElementById('tank_status_alert');

    // Show/hide nitrox percentage field
    fillTypeSelect.addEventListener('change', function() {
        if (this.value === 'nitrox') {
            nitroxField.style.display = 'block';
            nitroxInput.required = true;
        } else {
            nitroxField.style.display = 'none';
            nitroxInput.required = false;
        }
        updatePricing();
    });

    // Update pricing when pressure changes
    pressureInput.addEventListener('input', updatePricing);

    function updatePricing() {
        const fillType = fillTypeSelect.value;
        const pressure = parseInt(pressureInput.value) || 3000;

        fetch(`/store/air-fills/pricing?fill_type=${fillType}&pressure=${pressure}`)
            .then(response => response.json())
            .then(data => {
                costInput.value = data.adjusted_price.toFixed(2);
                suggestedPrice.textContent = `Suggested: $${data.adjusted_price.toFixed(2)}`;
            })
            .catch(err => console.error('Failed to fetch pricing:', err));
    }

    // Toggle Equipment Source
    function toggleEquipmentSource() {
        let source = 'rental';
        for(const radio of equipSourceRadios) {
            if(radio.checked) source = radio.value;
        }

        if(source === 'customer') {
            rentalDiv.style.display = 'none';
            customerDiv.style.display = 'block';
            // Trigger fetch if customer selected
            if(customerSelect.value) fetchCustomerTanks(customerSelect.value);
        } else {
            rentalDiv.style.display = 'block';
            customerDiv.style.display = 'none';
            tankStatusAlert.style.display = 'none';
        }
    }

    equipSourceRadios.forEach(radio => radio.addEventListener('change', toggleEquipmentSource));

    // Fetch Customer Tanks
    customerSelect.addEventListener('change', function() {
        if(this.value && document.querySelector('input[name="equipment_source"][value="customer"]').checked) {
            fetchCustomerTanks(this.value);
        } else {
            customerEquipSelect.innerHTML = '<option value="">-- Select Customer First --</option>';
        }
    });

    function fetchCustomerTanks(customerId) {
        customerEquipSelect.innerHTML = '<option value="">Loading...</option>';
        fetch(`/store/air-fills/customer-equipment?customer_id=${customerId}`)
            .then(res => res.json())
            .then(data => {
                customerEquipSelect.innerHTML = '<option value="">-- Select Customer Tank --</option>';
                if(data.length === 0) {
                    customerEquipSelect.innerHTML += '<option value="" disabled>No tanks found for customer</option>';
                }
                data.forEach(tank => {
                    const statusIcon = tank.status === 'Valid' ? '✅' : '❌';
                    const option = document.createElement('option');
                    option.value = tank.id;
                    option.textContent = `${statusIcon} ${tank.serial_number} (${tank.manufacturer})`;
                    option.dataset.status = tank.status;
                    option.dataset.vip = tank.last_vip_date;
                    option.dataset.hydro = tank.last_hydro_date;
                    customerEquipSelect.appendChild(option);
                });
            });
    }

    // Validate Selected Customer Tank
    customerEquipSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if(selected && selected.dataset.status === 'Expired') {
            tankStatusAlert.className = 'alert alert-danger mt-2';
            tankStatusAlert.style.display = 'block';
            tankStatusAlert.innerHTML = `
                <strong>Warning: Tank Expired!</strong><br>
                VIP: ${selected.dataset.vip} (Limit: 1yr)<br>
                Hydro: ${selected.dataset.hydro} (Limit: 5yrs)<br>
                <em>You cannot fill this tank until inspected.</em>
            `;
            // Optional: Disable submit button or clear selection
        } else if (selected && selected.value) {
            tankStatusAlert.className = 'alert alert-success mt-2';
            tankStatusAlert.style.display = 'block';
            tankStatusAlert.innerHTML = 'Tank is Valid for Fill.';
        } else {
            tankStatusAlert.style.display = 'none';
        }
    });

    // Validate transaction checkbox
    const createTransactionCheck = document.getElementById('create_transaction');

    document.getElementById('airFillForm').addEventListener('submit', function(e) {
        if (createTransactionCheck.checked && !customerSelect.value) {
            e.preventDefault();
            alert('Please select a customer to create a transaction, or uncheck "Create transaction"');
            customerSelect.focus();
            return;
        }

        // Prevent filling expired tanks
        const selectedTank = customerEquipSelect.options[customerEquipSelect.selectedIndex];
        if(document.querySelector('input[name="equipment_source"][value="customer"]').checked && 
           selectedTank && selectedTank.dataset.status === 'Expired') {
            e.preventDefault();
            alert('STOP: Cannot fill an expired tank. Please perform Visual or Hydro inspection first.');
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
