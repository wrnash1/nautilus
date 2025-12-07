<?php
$pageTitle = 'Close Cash Drawer';
$activeMenu = 'cash-drawer';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-lock-fill"></i> Close Cash Drawer Session</h1>
        <a href="/store/cash-drawer" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Session Info -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cash-stack"></i> <?= htmlspecialchars($session['drawer_name']) ?>
                    </h5>
                    <small>Session #<?= htmlspecialchars($session['session_number']) ?></small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Location:</strong> <?= htmlspecialchars($session['location']) ?>
                            </p>
                            <p class="mb-2">
                                <strong>Opened By:</strong> <?= htmlspecialchars($session['opened_by_name']) ?>
                            </p>
                            <p class="mb-2">
                                <strong>Opened At:</strong> <?= date('m/d/Y g:i A', strtotime($session['opened_at'])) ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Starting Balance:</strong>
                                <span class="fs-5"><?= formatCurrency($session['starting_balance']) ?></span>
                            </p>
                            <p class="mb-2">
                                <strong>Expected Balance:</strong>
                                <span class="fs-5 text-primary"><?= formatCurrency($expectedBalance) ?></span>
                            </p>
                            <p class="mb-2">
                                <strong>Duration:</strong>
                                <?php
                                $duration = time() - strtotime($session['opened_at']);
                                $hours = floor($duration / 3600);
                                $minutes = floor(($duration % 3600) / 60);
                                echo "{$hours}h {$minutes}m";
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Closing Form -->
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Count Cash to Close</h5>
                </div>
                <div class="card-body">
                    <form id="closeDrawerForm">
                        <input type="hidden" name="session_id" value="<?= $session['id'] ?>">

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong>Important:</strong> Count all cash in the drawer carefully.
                            Any difference between expected and actual will be recorded.
                        </div>

                        <!-- Ending Balance -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Ending Balance (Counted Cash)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control form-control-lg" id="ending_balance"
                                       name="ending_balance" step="0.01" min="0" required>
                            </div>
                            <small class="text-muted">Expected: <?= formatCurrency($expectedBalance) ?></small>
                        </div>

                        <hr>

                        <!-- Bills Section -->
                        <h5 class="mb-3"><i class="bi bi-cash"></i> Bills</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">$100 Bills</label>
                                <input type="number" class="form-control bill-input" name="ending_bills_100"
                                       data-value="100" min="0" value="0" required>
                                <small class="text-muted bill-total" data-denomination="100">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">$50 Bills</label>
                                <input type="number" class="form-control bill-input" name="ending_bills_50"
                                       data-value="50" min="0" value="0" required>
                                <small class="text-muted bill-total" data-denomination="50">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">$20 Bills</label>
                                <input type="number" class="form-control bill-input" name="ending_bills_20"
                                       data-value="20" min="0" value="0" required>
                                <small class="text-muted bill-total" data-denomination="20">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">$10 Bills</label>
                                <input type="number" class="form-control bill-input" name="ending_bills_10"
                                       data-value="10" min="0" value="0" required>
                                <small class="text-muted bill-total" data-denomination="10">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">$5 Bills</label>
                                <input type="number" class="form-control bill-input" name="ending_bills_5"
                                       data-value="5" min="0" value="0" required>
                                <small class="text-muted bill-total" data-denomination="5">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">$2 Bills</label>
                                <input type="number" class="form-control bill-input" name="ending_bills_2"
                                       data-value="2" min="0" value="0" required>
                                <small class="text-muted bill-total" data-denomination="2">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">$1 Bills</label>
                                <input type="number" class="form-control bill-input" name="ending_bills_1"
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
                                <input type="number" class="form-control coin-input" name="ending_coins_dollar"
                                       data-value="1.00" min="0" value="0" required>
                                <small class="text-muted coin-total" data-denomination="dollar">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Quarters</label>
                                <input type="number" class="form-control coin-input" name="ending_coins_quarter"
                                       data-value="0.25" min="0" value="0" required>
                                <small class="text-muted coin-total" data-denomination="quarter">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dimes</label>
                                <input type="number" class="form-control coin-input" name="ending_coins_dime"
                                       data-value="0.10" min="0" value="0" required>
                                <small class="text-muted coin-total" data-denomination="dime">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nickels</label>
                                <input type="number" class="form-control coin-input" name="ending_coins_nickel"
                                       data-value="0.05" min="0" value="0" required>
                                <small class="text-muted coin-total" data-denomination="nickel">= $0.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pennies</label>
                                <input type="number" class="form-control coin-input" name="ending_coins_penny"
                                       data-value="0.01" min="0" value="0" required>
                                <small class="text-muted coin-total" data-denomination="penny">= $0.00</small>
                            </div>
                        </div>

                        <hr>

                        <!-- Difference Reason (shown only if there's a difference) -->
                        <div id="differenceReasonSection" class="mb-4 d-none">
                            <label class="form-label fw-bold text-warning">
                                <i class="bi bi-exclamation-triangle-fill"></i> Explain the Difference
                            </label>
                            <textarea class="form-control" name="difference_reason" rows="3"
                                      placeholder="Required: Explain why the count doesn't match the expected balance..."></textarea>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label">Closing Notes (Optional)</label>
                            <textarea class="form-control" name="ending_notes" rows="3"
                                      placeholder="Any notes about the closing count or the shift..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-lg" id="submitBtn">
                                <i class="bi bi-lock-fill"></i> Close Cash Drawer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="col-lg-4">
            <!-- Count Summary -->
            <div class="card sticky-top mb-3" style="top: 20px;">
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

                    <hr>

                    <table class="table table-sm mb-0">
                        <tr>
                            <td><strong>Expected:</strong></td>
                            <td class="text-end"><?= formatCurrency($expectedBalance) ?></td>
                        </tr>
                        <tr id="differenceRow" class="d-none">
                            <td><strong>Difference:</strong></td>
                            <td class="text-end fw-bold" id="difference">$0.00</td>
                        </tr>
                    </table>

                    <div id="validationStatus" class="alert alert-secondary mt-3">
                        <small><i class="bi bi-info-circle"></i> Enter your count</small>
                    </div>
                </div>
            </div>

            <!-- Session Totals -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-graph-up"></i> Session Activity</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Starting Balance:</td>
                            <td class="text-end"><?= formatCurrency($session['starting_balance']) ?></td>
                        </tr>
                        <tr>
                            <td>Cash In:</td>
                            <td class="text-end text-success">+<?= formatCurrency($totals['total_in']) ?></td>
                        </tr>
                        <tr>
                            <td>Cash Out:</td>
                            <td class="text-end text-danger">-<?= formatCurrency($totals['total_out']) ?></td>
                        </tr>
                        <tr class="table-light fw-bold">
                            <td>Expected Balance:</td>
                            <td class="text-end"><?= formatCurrency($expectedBalance) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('closeDrawerForm');
    const endingBalanceInput = document.getElementById('ending_balance');
    const billInputs = document.querySelectorAll('.bill-input');
    const coinInputs = document.querySelectorAll('.coin-input');
    const validationStatus = document.getElementById('validationStatus');
    const differenceRow = document.getElementById('differenceRow');
    const differenceReasonSection = document.getElementById('differenceReasonSection');
    const submitBtn = document.getElementById('submitBtn');
    const expectedBalance = <?= $expectedBalance ?>;

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
            const denomination = input.name.replace('ending_coins_', '');
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

        // Update ending balance to match count
        endingBalanceInput.value = grandTotal.toFixed(2);

        // Calculate difference
        const difference = grandTotal - expectedBalance;
        const absDifference = Math.abs(difference);

        if (absDifference >= 0.01) {
            differenceRow.classList.remove('d-none');
            const differenceEl = document.getElementById('difference');
            differenceEl.textContent = (difference >= 0 ? '+' : '') + '$' + difference.toFixed(2);

            if (difference > 0) {
                differenceEl.className = 'text-end fw-bold text-success';
                validationStatus.className = 'alert alert-warning mt-3';
                validationStatus.innerHTML = '<i class="bi bi-arrow-up-circle-fill"></i> <strong>Overage:</strong> $' + absDifference.toFixed(2) + ' over expected';
            } else {
                differenceEl.className = 'text-end fw-bold text-danger';
                validationStatus.className = 'alert alert-danger mt-3';
                validationStatus.innerHTML = '<i class="bi bi-arrow-down-circle-fill"></i> <strong>Shortage:</strong> $' + absDifference.toFixed(2) + ' under expected';
            }

            // Show reason field if difference is significant
            if (absDifference > 1.00) {
                differenceReasonSection.classList.remove('d-none');
            } else {
                differenceReasonSection.classList.add('d-none');
            }
        } else {
            differenceRow.classList.add('d-none');
            differenceReasonSection.classList.add('d-none');
            validationStatus.className = 'alert alert-success mt-3';
            validationStatus.innerHTML = '<i class="bi bi-check-circle-fill"></i> <strong>Balanced!</strong> Count matches expected amount.';
        }

        submitBtn.disabled = false;
    }

    // Add event listeners
    billInputs.forEach(input => input.addEventListener('input', calculateTotals));
    coinInputs.forEach(input => input.addEventListener('input', calculateTotals));

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const difference = parseFloat(endingBalanceInput.value) - expectedBalance;
        const absDifference = Math.abs(difference);

        // Check if reason is required
        if (absDifference > 1.00) {
            const reason = form.querySelector('[name="difference_reason"]').value.trim();
            if (!reason) {
                alert('Please explain the reason for the cash difference.');
                return;
            }
        }

        // Confirm closing
        let confirmMsg = 'Are you sure you want to close this cash drawer session?';
        if (absDifference >= 0.01) {
            confirmMsg += '\n\nThere is a difference of $' + absDifference.toFixed(2) + ' (' + (difference > 0 ? 'overage' : 'shortage') + ')';
        }

        if (!confirm(confirmMsg)) {
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Closing...';

        try {
            const formData = new FormData(form);

            const response = await fetch('/store/cash-drawer/session/<?= $session['id'] ?>/close', {
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
                    <strong>Success!</strong> Cash drawer closed. Status: ${result.status}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                form.prepend(alert);

                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = '/store/cash-drawer';
                }, 2000);
            } else {
                throw new Error(result.error || 'Failed to close drawer');
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
            submitBtn.innerHTML = '<i class="bi bi-lock-fill"></i> Close Cash Drawer';
        }
    });

    // Initial calculation
    calculateTotals();
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
