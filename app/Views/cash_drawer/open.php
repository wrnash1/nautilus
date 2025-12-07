<?php
$pageTitle = 'Open Cash Drawer';
$activeMenu = 'cash-drawer';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-unlock-fill"></i> Open Cash Drawer</h1>
        <a href="/store/cash-drawer" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cash-stack"></i> <?= htmlspecialchars($drawer['name']) ?>
                    </h5>
                    <small><?= htmlspecialchars($drawer['location'] ?? 'N/A') ?></small>
                </div>
                <div class="card-body">
                    <form id="openDrawerForm">
                        <input type="hidden" name="drawer_id" value="<?= $drawer['id'] ?>">

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Count the cash in the drawer before opening.</strong>
                            Enter the exact count of bills and coins. The total must match your starting balance.
                        </div>

                        <!-- Starting Balance -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Starting Balance</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control form-control-lg" id="starting_balance"
                                       name="starting_balance" step="0.01" min="0" required
                                       value="<?= number_format($drawer['starting_float'], 2, '.', '') ?>">
                            </div>
                            <small class="text-muted">Suggested starting float: <?= formatCurrency($drawer['starting_float']) ?></small>
                        </div>

                        <hr>

                        <!-- Bills Section -->
                        <h5 class="mb-3"><i class="bi bi-cash"></i> Bills</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">$100 Bills</label>
                                <input type="number" class="form-control bill-input" name="bills_100"
                                       data-value="100" min="0" value="0" required>
                                <small class="text-muted bill-total" data-denomination="100">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">$50 Bills</label>
                                <input type="number" class="form-control bill-input" name="bills_50"
                                       data-value="50" min="0" value="0" required>
                                <small class="text-muted bill-total" data-denomination="50">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">$20 Bills</label>
                                <input type="number" class="form-control bill-input" name="bills_20"
                                       data-value="20" min="0" value="0" required>
                                <small class="text-muted bill-total" data-denomination="20">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">$10 Bills</label>
                                <input type="number" class="form-control bill-input" name="bills_10"
                                       data-value="10" min="0" value="0" required>
                                <small class="text-muted bill-total" data-denomination="10">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">$5 Bills</label>
                                <input type="number" class="form-control bill-input" name="bills_5"
                                       data-value="5" min="0" value="0" required>
                                <small class="text-muted bill-total" data-denomination="5">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">$2 Bills</label>
                                <input type="number" class="form-control bill-input" name="bills_2"
                                       data-value="2" min="0" value="0" required>
                                <small class="text-muted bill-total" data-denomination="2">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">$1 Bills</label>
                                <input type="number" class="form-control bill-input" name="bills_1"
                                       data-value="1" min="0" value="0" required>
                                <small class="text-muted bill-total" data-denomination="1">= $0.00</small>
                            </div>
                        </div>

                        <hr>

                        <!-- Coins Section -->
                        <h5 class="mb-3"><i class="bi bi-coin"></i> Coins</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Dollar Coins</label>
                                <input type="number" class="form-control coin-input" name="coins_dollar"
                                       data-value="1.00" min="0" value="0" required>
                                <small class="text-muted coin-total" data-denomination="dollar">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Quarters</label>
                                <input type="number" class="form-control coin-input" name="coins_quarter"
                                       data-value="0.25" min="0" value="0" required>
                                <small class="text-muted coin-total" data-denomination="quarter">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dimes</label>
                                <input type="number" class="form-control coin-input" name="coins_dime"
                                       data-value="0.10" min="0" value="0" required>
                                <small class="text-muted coin-total" data-denomination="dime">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nickels</label>
                                <input type="number" class="form-control coin-input" name="coins_nickel"
                                       data-value="0.05" min="0" value="0" required>
                                <small class="text-muted coin-total" data-denomination="nickel">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pennies</label>
                                <input type="number" class="form-control coin-input" name="coins_penny"
                                       data-value="0.01" min="0" value="0" required>
                                <small class="text-muted coin-total" data-denomination="penny">= $0.00</small>
                            </div>
                        </div>

                        <hr>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label">Opening Notes (Optional)</label>
                            <textarea class="form-control" name="starting_notes" rows="3"
                                      placeholder="Any notes about the opening count..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-unlock-fill"></i> Open Cash Drawer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-calculator"></i> Count Summary</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-3">
                        <tr>
                            <td><strong>Bills Total:</strong></td>
                            <td class="text-end" id="billsTotal">$0.00</td>
                        </tr>
                        <tr>
                            <td><strong>Coins Total:</strong></td>
                            <td class="text-end" id="coinsTotal">$0.00</td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Counted Total:</strong></td>
                            <td class="text-end fw-bold fs-5" id="countedTotal">$0.00</td>
                        </tr>
                    </table>

                    <div id="validationStatus" class="alert alert-secondary d-none">
                        <small><i class="bi bi-info-circle"></i> Enter your count to validate</small>
                    </div>
                </div>

                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="bi bi-lightbulb"></i> <strong>Tip:</strong> Count all bills first, then coins.
                        The system will verify your count matches the starting balance.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('openDrawerForm');
    const startingBalanceInput = document.getElementById('starting_balance');
    const billInputs = document.querySelectorAll('.bill-input');
    const coinInputs = document.querySelectorAll('.coin-input');
    const validationStatus = document.getElementById('validationStatus');
    const submitBtn = document.getElementById('submitBtn');

    // Calculate totals
    function calculateTotals() {
        let billsTotal = 0;
        let coinsTotal = 0;

        // Calculate bills
        billInputs.forEach(input => {
            const count = parseInt(input.value) || 0;
            const value = parseFloat(input.dataset.value);
            const total = count * value;
            billsTotal += total;

            // Update individual total
            const denomination = input.dataset.value;
            const totalDisplay = document.querySelector(`.bill-total[data-denomination="${denomination}"]`);
            if (totalDisplay) {
                totalDisplay.textContent = '= $' + total.toFixed(2);
            }
        });

        // Calculate coins
        coinInputs.forEach(input => {
            const count = parseInt(input.value) || 0;
            const value = parseFloat(input.dataset.value);
            const total = count * value;
            coinsTotal += total;

            // Update individual total
            const denomination = input.name.replace('coins_', '');
            const totalDisplay = document.querySelector(`.coin-total[data-denomination="${denomination}"]`);
            if (totalDisplay) {
                totalDisplay.textContent = '= $' + total.toFixed(2);
            }
        });

        const grandTotal = billsTotal + coinsTotal;

        // Update summary
        document.getElementById('billsTotal').textContent = '$' + billsTotal.toFixed(2);
        document.getElementById('coinsTotal').textContent = '$' + coinsTotal.toFixed(2);
        document.getElementById('countedTotal').textContent = '$' + grandTotal.toFixed(2);

        // Validate against starting balance
        const startingBalance = parseFloat(startingBalanceInput.value) || 0;
        const difference = Math.abs(grandTotal - startingBalance);

        if (difference < 0.01) {
            validationStatus.className = 'alert alert-success';
            validationStatus.innerHTML = '<i class="bi bi-check-circle-fill"></i> <strong>Perfect!</strong> Count matches starting balance.';
            validationStatus.classList.remove('d-none');
            submitBtn.disabled = false;
        } else if (grandTotal > 0) {
            validationStatus.className = 'alert alert-warning';
            validationStatus.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> <strong>Mismatch:</strong> Count ($' + grandTotal.toFixed(2) + ') does not match starting balance ($' + startingBalance.toFixed(2) + ')';
            validationStatus.classList.remove('d-none');
            submitBtn.disabled = true;
        } else {
            validationStatus.className = 'alert alert-secondary';
            validationStatus.innerHTML = '<small><i class="bi bi-info-circle"></i> Enter your count to validate</small>';
            validationStatus.classList.remove('d-none');
            submitBtn.disabled = false;
        }
    }

    // Add event listeners
    billInputs.forEach(input => input.addEventListener('input', calculateTotals));
    coinInputs.forEach(input => input.addEventListener('input', calculateTotals));
    startingBalanceInput.addEventListener('input', calculateTotals);

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Opening...';

        try {
            const formData = new FormData(form);

            const response = await fetch('/store/cash-drawer/open', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>Success!</strong> Cash drawer opened successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                form.prepend(alert);

                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = '/store/cash-drawer';
                }, 2000);
            } else {
                throw new Error(result.error || 'Failed to open drawer');
            }
        } catch (error) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>Error!</strong> ${error.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            form.prepend(alert);

            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-unlock-fill"></i> Open Cash Drawer';
        }
    });

    // Initial calculation
    calculateTotals();
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
