<?php
$pageTitle = 'Quick Fill';
$activeMenu = 'air-fills';

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-lightning-fill"></i> Quick Fill Entry</h4>
                <small>Rapid tank fill recording for busy operations</small>
            </div>
            <div class="card-body">
                <form id="quickFillForm">
                    <!-- Customer Search -->
                    <div class="mb-3">
                        <label for="customer_search" class="form-label">Customer</label>
                        <input type="text" id="customer_search" class="form-control form-control-lg"
                               placeholder="Search customer..." autocomplete="off">
                        <input type="hidden" id="customer_id" name="customer_id">
                        <div id="customer_results" class="list-group mt-1" style="display: none;"></div>
                    </div>

                    <!-- Fill Type Buttons -->
                    <div class="mb-3">
                        <label class="form-label">Fill Type</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="fill_type" id="type_air" value="air" checked>
                            <label class="btn btn-outline-primary" for="type_air">
                                <i class="bi bi-wind"></i> Air
                            </label>

                            <input type="radio" class="btn-check" name="fill_type" id="type_nitrox" value="nitrox">
                            <label class="btn btn-outline-success" for="type_nitrox">
                                <i class="bi bi-wind"></i> Nitrox
                            </label>

                            <input type="radio" class="btn-check" name="fill_type" id="type_trimix" value="trimix">
                            <label class="btn btn-outline-warning" for="type_trimix">
                                <i class="bi bi-wind"></i> Trimix
                            </label>
                        </div>
                    </div>

                    <!-- Nitrox Percentage (conditional) -->
                    <div class="mb-3" id="nitrox_percent_group" style="display: none;">
                        <label for="nitrox_percentage" class="form-label">Nitrox %</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="nitrox_percentage" id="ean32" value="32" checked>
                            <label class="btn btn-outline-success" for="ean32">EAN32</label>

                            <input type="radio" class="btn-check" name="nitrox_percentage" id="ean36" value="36">
                            <label class="btn btn-outline-success" for="ean36">EAN36</label>

                            <input type="radio" class="btn-check" name="nitrox_percentage" id="custom" value="">
                            <label class="btn btn-outline-success" for="custom">Custom</label>
                        </div>
                        <input type="number" id="custom_nitrox" class="form-control mt-2" placeholder="Custom %"
                               min="21" max="40" style="display: none;">
                    </div>

                    <!-- Pressure Buttons -->
                    <div class="mb-3">
                        <label class="form-label">Pressure (PSI)</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="fill_pressure" id="psi_3000" value="3000" checked>
                            <label class="btn btn-outline-info" for="psi_3000">3000</label>

                            <input type="radio" class="btn-check" name="fill_pressure" id="psi_3300" value="3300">
                            <label class="btn btn-outline-info" for="psi_3300">3300</label>

                            <input type="radio" class="btn-check" name="fill_pressure" id="psi_3500" value="3500">
                            <label class="btn btn-outline-info" for="psi_3500">3500</label>
                        </div>
                    </div>

                    <!-- Cost (auto-calculated) -->
                    <div class="mb-4">
                        <label for="cost" class="form-label">Cost</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">$</span>
                            <input type="number" name="cost" id="cost" class="form-control"
                                   value="8.00" step="0.01" required>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle"></i> Record Fill
                        </button>
                        <a href="/store/air-fills" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                    </div>
                </form>

                <!-- Recent Fills -->
                <div class="mt-4 pt-4 border-top">
                    <h6 class="text-muted">Recent Fills</h6>
                    <ul class="list-unstyled" id="recent_fills">
                        <li class="text-muted small">No fills recorded yet in this session</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('quickFillForm');
    const customerSearch = document.getElementById('customer_search');
    const customerResults = document.getElementById('customer_results');
    const customerIdInput = document.getElementById('customer_id');
    const fillTypeInputs = document.querySelectorAll('input[name="fill_type"]');
    const nitroxGroup = document.getElementById('nitrox_percent_group');
    const costInput = document.getElementById('cost');
    let recentFills = [];

    // Customer search
    let searchTimeout;
    customerSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            customerResults.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`/api/customers/search?q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(customers => {
                    if (customers.length === 0) {
                        customerResults.innerHTML = '<div class="list-group-item">No customers found</div>';
                    } else {
                        customerResults.innerHTML = customers.map(c =>
                            `<button type="button" class="list-group-item list-group-item-action" data-id="${c.id}" data-name="${c.name}">
                                <strong>${c.name}</strong><br>
                                <small class="text-muted">${c.email || ''}</small>
                            </button>`
                        ).join('');
                    }
                    customerResults.style.display = 'block';
                })
                .catch(err => console.error('Search error:', err));
        }, 300);
    });

    // Customer selection
    customerResults.addEventListener('click', function(e) {
        const btn = e.target.closest('.list-group-item');
        if (btn) {
            customerIdInput.value = btn.dataset.id;
            customerSearch.value = btn.dataset.name;
            customerResults.style.display = 'none';
        }
    });

    // Show/hide nitrox percentage
    fillTypeInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'nitrox') {
                nitroxGroup.style.display = 'block';
            } else {
                nitroxGroup.style.display = 'none';
            }
            updatePricing();
        });
    });

    // Custom nitrox percentage
    document.querySelectorAll('input[name="nitrox_percentage"]').forEach(input => {
        input.addEventListener('change', function() {
            const customInput = document.getElementById('custom_nitrox');
            if (this.value === '') {
                customInput.style.display = 'block';
                customInput.focus();
            } else {
                customInput.style.display = 'none';
            }
        });
    });

    // Update pricing
    document.querySelectorAll('input[name="fill_pressure"]').forEach(input => {
        input.addEventListener('change', updatePricing);
    });

    function updatePricing() {
        const fillType = document.querySelector('input[name="fill_type"]:checked').value;
        const pressure = document.querySelector('input[name="fill_pressure"]:checked').value;

        fetch(`/air-fills/pricing?fill_type=${fillType}&pressure=${pressure}`)
            .then(r => r.json())
            .then(data => {
                costInput.value = data.adjusted_price.toFixed(2);
            });
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!customerIdInput.value) {
            alert('Please select a customer');
            customerSearch.focus();
            return;
        }

        const formData = new FormData(form);

        // Get custom nitrox if selected
        const nitroxCustom = document.getElementById('custom');
        if (nitroxCustom.checked) {
            formData.set('nitrox_percentage', document.getElementById('custom_nitrox').value);
        }

        formData.set('customer_id', customerIdInput.value);

        fetch('/air-fills/quick-fill', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Add to recent fills
                const fillType = document.querySelector('input[name="fill_type"]:checked').value;
                const cost = costInput.value;
                const time = new Date().toLocaleTimeString();

                recentFills.unshift(`${customerSearch.value} - ${fillType.toUpperCase()} - $${cost} (${time})`);
                if (recentFills.length > 5) recentFills.pop();

                document.getElementById('recent_fills').innerHTML =
                    recentFills.map(f => `<li class="small">${f}</li>`).join('');

                // Show success
                alert('✅ Air fill recorded successfully!');

                // Reset form but keep customer
                form.reset();
                document.getElementById('type_air').checked = true;
                document.getElementById('psi_3000').checked = true;
                nitroxGroup.style.display = 'none';
                updatePricing();
                customerSearch.focus();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(err => {
            alert('Failed to record fill: ' + err.message);
        });
    });
});
</script>

<style>
.btn-check:checked + .btn {
    font-weight: bold;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
