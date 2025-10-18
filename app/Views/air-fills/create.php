<?php
$pageTitle = 'New Air Fill';
$activeMenu = 'air-fills';

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/air-fills">Air Fills</a></li>
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
                <form method="POST" action="/air-fills" id="airFillForm">
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

                    <!-- Equipment/Tank (Optional) -->
                    <div class="mb-3">
                        <label for="equipment_id" class="form-label">
                            Tank/Equipment <span class="text-muted">(Optional)</span>
                        </label>
                        <select name="equipment_id" id="equipment_id" class="form-select">
                            <option value="">-- Select Tank (Optional) --</option>
                            <?php foreach ($tanks as $tank): ?>
                            <option value="<?= $tank['id'] ?>">
                                <?= htmlspecialchars($tank['name']) ?> - <?= htmlspecialchars($tank['equipment_code']) ?>
                                <?php if ($tank['category_name']): ?>
                                    (<?= htmlspecialchars($tank['category_name']) ?>)
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Track which tank was filled (if using rental equipment)</small>
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
                        <a href="/air-fills" class="btn btn-secondary">
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

    // Show/hide nitrox percentage field
    fillTypeSelect.addEventListener('change', function() {
        if (this.value === 'nitrox') {
            nitroxField.style.display = 'block';
            nitroxInput.required = true;
        } else {
            nitroxField.style.display = 'none';
            nitroxInput.required = false;
        }

        // Update pricing
        updatePricing();
    });

    // Update pricing when pressure changes
    pressureInput.addEventListener('input', updatePricing);

    function updatePricing() {
        const fillType = fillTypeSelect.value;
        const pressure = parseInt(pressureInput.value) || 3000;

        fetch(`/air-fills/pricing?fill_type=${fillType}&pressure=${pressure}`)
            .then(response => response.json())
            .then(data => {
                costInput.value = data.adjusted_price.toFixed(2);
                suggestedPrice.textContent = `Suggested: $${data.adjusted_price.toFixed(2)}`;
            })
            .catch(err => console.error('Failed to fetch pricing:', err));
    }

    // Validate transaction checkbox
    const createTransactionCheck = document.getElementById('create_transaction');
    const customerSelect = document.getElementById('customer_id');

    document.getElementById('airFillForm').addEventListener('submit', function(e) {
        if (createTransactionCheck.checked && !customerSelect.value) {
            e.preventDefault();
            alert('Please select a customer to create a transaction, or uncheck "Create transaction"');
            customerSelect.focus();
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
