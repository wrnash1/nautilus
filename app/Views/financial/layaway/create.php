<?php
$pageTitle = $title ?? 'Create Layaway Agreement';
$activeMenu = 'financial';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-calendar2-plus me-2"></i>Create Layaway Agreement
        </h1>
        <a href="/store/layaway" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form action="/store/layaway" method="POST" id="layawayForm">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="items" id="itemsJson" value="[]">
        <input type="hidden" name="total_amount" id="totalAmount" value="0">

        <div class="row">
            <div class="col-lg-8">
                <!-- Customer Selection -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Customer</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($customer ?? null): ?>
                            <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <?= strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></h5>
                                    <small class="text-muted"><?= htmlspecialchars($customer['email'] ?? '') ?></small>
                                </div>
                                <a href="/store/layaway/create" class="btn btn-sm btn-outline-secondary ms-auto">Change Customer</a>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <label class="form-label">Search Customer <span class="text-danger">*</span></label>
                                <input type="text" id="customerSearch" class="form-control" placeholder="Search by name, email, or phone...">
                                <input type="hidden" name="customer_id" id="customerId" required>
                            </div>
                            <div id="customerResults" class="list-group"></div>
                            <div id="selectedCustomer" class="mt-3" style="display: none;">
                                <div class="alert alert-info d-flex align-items-center">
                                    <div>
                                        <strong id="selectedCustomerName"></strong><br>
                                        <small id="selectedCustomerEmail"></small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="clearCustomer()">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Products -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Products</h5>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="bi bi-plus-lg me-1"></i>Add Product
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="productList">
                            <div class="text-center py-4 text-muted" id="noProducts">
                                <i class="bi bi-box-seam display-4"></i>
                                <p class="mt-2">No products added yet. Click "Add Product" to begin.</p>
                            </div>
                        </div>
                        <table class="table" id="productsTable" style="display: none;">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody"></tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th colspan="3" class="text-end">Subtotal:</th>
                                    <th id="subtotalDisplay">$0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Layaway Plan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Layaway Plan</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($plans ?? [])): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                No layaway plans available. <a href="/store/layaway/plans">Create one first</a>.
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <label class="form-label">Select Plan <span class="text-danger">*</span></label>
                                <select name="plan_id" id="planId" class="form-select" required onchange="updateEstimate()">
                                    <option value="">Choose a plan...</option>
                                    <?php foreach ($plans as $plan): ?>
                                        <option value="<?= $plan['id'] ?>"
                                                data-payments="<?= $plan['number_of_payments'] ?>"
                                                data-frequency="<?= $plan['payment_frequency'] ?>"
                                                data-down-pct="<?= $plan['down_payment_percentage'] ?>"
                                                data-down-min="<?= $plan['down_payment_minimum'] ?>"
                                                data-fee="<?= $plan['layaway_fee'] ?>"
                                                data-fee-type="<?= $plan['layaway_fee_type'] ?>"
                                                data-min-purchase="<?= $plan['min_purchase_amount'] ?>">
                                            <?= htmlspecialchars($plan['plan_name']) ?>
                                            (<?= $plan['number_of_payments'] ?> <?= $plan['payment_frequency'] ?> payments)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <!-- Payment Estimate -->
                        <div id="paymentEstimate" style="display: none;">
                            <hr>
                            <h6 class="text-muted mb-3">Payment Estimate</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong id="estSubtotal">$0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Layaway Fee:</span>
                                <span id="estFee">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 text-primary">
                                <strong>Total Due:</strong>
                                <strong id="estTotal">$0.00</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Down Payment:</span>
                                <strong id="estDown">$0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span id="estPaymentCount">0 Payments of:</span>
                                <strong id="estPayment">$0.00</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                        <i class="bi bi-check-lg me-1"></i>Create Agreement
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Search Product</label>
                    <input type="text" id="productSearch" class="form-control" placeholder="Search by name or SKU...">
                </div>
                <div id="productSearchResults" class="list-group" style="max-height: 300px; overflow-y: auto;"></div>
            </div>
        </div>
    </div>
</div>

<script>
let products = [];

// Customer search
document.getElementById('customerSearch')?.addEventListener('input', function() {
    const query = this.value;
    if (query.length < 2) {
        document.getElementById('customerResults').innerHTML = '';
        return;
    }

    fetch('/store/customers/search?q=' + encodeURIComponent(query))
        .then(r => r.json())
        .then(data => {
            const results = document.getElementById('customerResults');
            results.innerHTML = '';
            (data.customers || []).forEach(c => {
                const item = document.createElement('a');
                item.href = '#';
                item.className = 'list-group-item list-group-item-action';
                item.innerHTML = `<strong>${c.first_name} ${c.last_name}</strong><br><small>${c.email || ''}</small>`;
                item.onclick = (e) => {
                    e.preventDefault();
                    selectCustomer(c);
                };
                results.appendChild(item);
            });
        });
});

function selectCustomer(customer) {
    document.getElementById('customerId').value = customer.id;
    document.getElementById('selectedCustomerName').textContent = customer.first_name + ' ' + customer.last_name;
    document.getElementById('selectedCustomerEmail').textContent = customer.email || '';
    document.getElementById('selectedCustomer').style.display = 'block';
    document.getElementById('customerSearch').style.display = 'none';
    document.getElementById('customerResults').innerHTML = '';
    updateSubmitButton();
}

function clearCustomer() {
    document.getElementById('customerId').value = '';
    document.getElementById('selectedCustomer').style.display = 'none';
    document.getElementById('customerSearch').style.display = 'block';
    document.getElementById('customerSearch').value = '';
    updateSubmitButton();
}

// Product search
document.getElementById('productSearch')?.addEventListener('input', function() {
    const query = this.value;
    if (query.length < 2) {
        document.getElementById('productSearchResults').innerHTML = '';
        return;
    }

    fetch('/store/products/search?q=' + encodeURIComponent(query))
        .then(r => r.json())
        .then(data => {
            const results = document.getElementById('productSearchResults');
            results.innerHTML = '';
            (data.products || data || []).forEach(p => {
                const item = document.createElement('a');
                item.href = '#';
                item.className = 'list-group-item list-group-item-action d-flex justify-content-between';
                item.innerHTML = `
                    <div>
                        <strong>${p.name}</strong><br>
                        <small class="text-muted">${p.sku || ''}</small>
                    </div>
                    <div class="text-end">
                        <strong>$${parseFloat(p.retail_price || p.price || 0).toFixed(2)}</strong>
                    </div>
                `;
                item.onclick = (e) => {
                    e.preventDefault();
                    addProduct(p);
                };
                results.appendChild(item);
            });
        });
});

function addProduct(product) {
    const existing = products.find(p => p.id === product.id);
    if (existing) {
        existing.quantity++;
    } else {
        products.push({
            id: product.id,
            name: product.name,
            sku: product.sku || '',
            price: parseFloat(product.retail_price || product.price || 0),
            quantity: 1
        });
    }
    renderProducts();
    bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide();
    document.getElementById('productSearch').value = '';
    document.getElementById('productSearchResults').innerHTML = '';
}

function removeProduct(index) {
    products.splice(index, 1);
    renderProducts();
}

function updateQuantity(index, qty) {
    if (qty < 1) qty = 1;
    products[index].quantity = qty;
    renderProducts();
}

function renderProducts() {
    const tbody = document.getElementById('productTableBody');
    const table = document.getElementById('productsTable');
    const noProducts = document.getElementById('noProducts');

    if (products.length === 0) {
        table.style.display = 'none';
        noProducts.style.display = 'block';
    } else {
        table.style.display = 'table';
        noProducts.style.display = 'none';
    }

    let subtotal = 0;
    tbody.innerHTML = '';

    products.forEach((p, i) => {
        const lineTotal = p.price * p.quantity;
        subtotal += lineTotal;
        tbody.innerHTML += `
            <tr>
                <td>
                    <strong>${p.name}</strong><br>
                    <small class="text-muted">${p.sku}</small>
                </td>
                <td>$${p.price.toFixed(2)}</td>
                <td>
                    <input type="number" class="form-control form-control-sm" style="width: 70px;"
                           value="${p.quantity}" min="1" onchange="updateQuantity(${i}, this.value)">
                </td>
                <td>$${lineTotal.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProduct(${i})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    document.getElementById('subtotalDisplay').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('totalAmount').value = subtotal;
    document.getElementById('itemsJson').value = JSON.stringify(products);

    updateEstimate();
    updateSubmitButton();
}

function updateEstimate() {
    const planSelect = document.getElementById('planId');
    const option = planSelect.options[planSelect.selectedIndex];
    const subtotal = products.reduce((sum, p) => sum + (p.price * p.quantity), 0);

    if (!option || !option.value || subtotal <= 0) {
        document.getElementById('paymentEstimate').style.display = 'none';
        return;
    }

    const payments = parseInt(option.dataset.payments);
    const downPct = parseFloat(option.dataset.downPct);
    const downMin = parseFloat(option.dataset.downMin);
    const fee = parseFloat(option.dataset.fee);
    const feeType = option.dataset.feeType;
    const minPurchase = parseFloat(option.dataset.minPurchase);

    if (subtotal < minPurchase) {
        document.getElementById('paymentEstimate').style.display = 'none';
        alert('Minimum purchase amount is $' + minPurchase.toFixed(2));
        return;
    }

    const layawayFee = feeType === 'percentage' ? (subtotal * fee / 100) : fee;
    const total = subtotal + layawayFee;
    const downPayment = Math.max(subtotal * downPct / 100, downMin);
    const balance = total - downPayment;
    const paymentAmount = balance / payments;

    document.getElementById('estSubtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('estFee').textContent = '$' + layawayFee.toFixed(2);
    document.getElementById('estTotal').textContent = '$' + total.toFixed(2);
    document.getElementById('estDown').textContent = '$' + downPayment.toFixed(2);
    document.getElementById('estPaymentCount').textContent = payments + ' payments of:';
    document.getElementById('estPayment').textContent = '$' + paymentAmount.toFixed(2);

    document.getElementById('paymentEstimate').style.display = 'block';
}

function updateSubmitButton() {
    const customerId = document.getElementById('customerId')?.value || '<?= $customer['id'] ?? '' ?>';
    const planId = document.getElementById('planId')?.value;
    const hasProducts = products.length > 0;

    document.getElementById('submitBtn').disabled = !(customerId && planId && hasProducts);
}

// Initialize
document.getElementById('planId')?.addEventListener('change', updateSubmitButton);
</script>
