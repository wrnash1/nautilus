<?php
$pageTitle = 'Session Details';
$activeMenu = 'cash-drawer';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-receipt"></i> Session #<?= htmlspecialchars($session['session_number']) ?></h1>
        <div>
            <a href="/store/cash-drawer/history" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to History
            </a>
            <?php if ($session['status'] === 'open'): ?>
            <a href="/store/cash-drawer/session/<?= $session['id'] ?>/close" class="btn btn-danger">
                <i class="bi bi-lock-fill"></i> Close Session
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <!-- Session Overview -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-<?= $session['status'] === 'open' ? 'info' : ($session['status'] === 'balanced' ? 'success' : ($session['status'] === 'over' ? 'warning' : 'danger')) ?> text-white">
                    <h5 class="mb-0">
                        Session Overview
                        <span class="badge bg-light text-dark float-end">
                            <?= ucfirst($session['status']) ?>
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Drawer:</th>
                                    <td>
                                        <strong><?= htmlspecialchars($session['drawer_name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($session['drawer_location']) ?></small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Opened By:</th>
                                    <td><?= htmlspecialchars($session['opened_by_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Opened At:</th>
                                    <td><?= date('M d, Y g:i A', strtotime($session['opened_at'])) ?></td>
                                </tr>
                                <?php if ($session['status'] !== 'open'): ?>
                                <tr>
                                    <th>Closed By:</th>
                                    <td><?= htmlspecialchars($session['closed_by_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Closed At:</th>
                                    <td><?= date('M d, Y g:i A', strtotime($session['closed_at'])) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>Duration:</th>
                                    <td>
                                        <?php
                                        $start = strtotime($session['opened_at']);
                                        $end = $session['status'] === 'open' ? time() : strtotime($session['closed_at']);
                                        $hours = floor(($end - $start) / 3600);
                                        $minutes = floor((($end - $start) % 3600) / 60);
                                        echo "{$hours}h {$minutes}m";
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Starting Balance:</th>
                                    <td><strong><?= formatCurrency($session['starting_balance']) ?></strong></td>
                                </tr>
                                <?php if ($session['status'] !== 'open'): ?>
                                <tr>
                                    <th>Ending Balance:</th>
                                    <td><strong><?= formatCurrency($session['ending_balance']) ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Expected Balance:</th>
                                    <td><strong class="text-primary"><?= formatCurrency($session['expected_balance']) ?></strong></td>
                                </tr>
                                <tr class="table-light">
                                    <th>Difference:</th>
                                    <td>
                                        <?php
                                        $diff = $session['difference'];
                                        $class = abs($diff) < 0.01 ? 'text-success' : ($diff > 0 ? 'text-warning' : 'text-danger');
                                        ?>
                                        <strong class="<?= $class ?> fs-5">
                                            <?= $diff >= 0 ? '+' : '' ?><?= formatCurrency($diff) ?>
                                        </strong>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <?php if (!empty($session['starting_notes']) || !empty($session['ending_notes']) || !empty($session['difference_reason'])): ?>
                    <hr>
                    <div class="row">
                        <?php if (!empty($session['starting_notes'])): ?>
                        <div class="col-md-6">
                            <h6><i class="bi bi-sticky"></i> Opening Notes:</h6>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($session['starting_notes'])) ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($session['ending_notes'])): ?>
                        <div class="col-md-6">
                            <h6><i class="bi bi-sticky-fill"></i> Closing Notes:</h6>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($session['ending_notes'])) ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($session['difference_reason'])): ?>
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <strong><i class="bi bi-exclamation-triangle-fill"></i> Difference Explanation:</strong><br>
                                <?= nl2br(htmlspecialchars($session['difference_reason'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bill and Coin Breakdown -->
            <?php if ($session['status'] !== 'open'): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Cash Count Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">Opening Count</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Denomination</th>
                                        <th class="text-end">Count</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $denominations = [
                                        ['label' => '$100', 'count' => $session['starting_bills_100'], 'value' => 100],
                                        ['label' => '$50', 'count' => $session['starting_bills_50'], 'value' => 50],
                                        ['label' => '$20', 'count' => $session['starting_bills_20'], 'value' => 20],
                                        ['label' => '$10', 'count' => $session['starting_bills_10'], 'value' => 10],
                                        ['label' => '$5', 'count' => $session['starting_bills_5'], 'value' => 5],
                                        ['label' => '$2', 'count' => $session['starting_bills_2'], 'value' => 2],
                                        ['label' => '$1', 'count' => $session['starting_bills_1'], 'value' => 1],
                                        ['label' => 'Dollar coins', 'count' => $session['starting_coins_dollar'], 'value' => 1],
                                        ['label' => 'Quarters', 'count' => $session['starting_coins_quarter'], 'value' => 0.25],
                                        ['label' => 'Dimes', 'count' => $session['starting_coins_dime'], 'value' => 0.10],
                                        ['label' => 'Nickels', 'count' => $session['starting_coins_nickel'], 'value' => 0.05],
                                        ['label' => 'Pennies', 'count' => $session['starting_coins_penny'], 'value' => 0.01],
                                    ];
                                    foreach ($denominations as $denom):
                                        if ($denom['count'] > 0):
                                    ?>
                                    <tr>
                                        <td><?= $denom['label'] ?></td>
                                        <td class="text-end"><?= $denom['count'] ?></td>
                                        <td class="text-end"><?= formatCurrency($denom['count'] * $denom['value']) ?></td>
                                    </tr>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-danger">Closing Count</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Denomination</th>
                                        <th class="text-end">Count</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $denominations = [
                                        ['label' => '$100', 'count' => $session['ending_bills_100'], 'value' => 100],
                                        ['label' => '$50', 'count' => $session['ending_bills_50'], 'value' => 50],
                                        ['label' => '$20', 'count' => $session['ending_bills_20'], 'value' => 20],
                                        ['label' => '$10', 'count' => $session['ending_bills_10'], 'value' => 10],
                                        ['label' => '$5', 'count' => $session['ending_bills_5'], 'value' => 5],
                                        ['label' => '$2', 'count' => $session['ending_bills_2'], 'value' => 2],
                                        ['label' => '$1', 'count' => $session['ending_bills_1'], 'value' => 1],
                                        ['label' => 'Dollar coins', 'count' => $session['ending_coins_dollar'], 'value' => 1],
                                        ['label' => 'Quarters', 'count' => $session['ending_coins_quarter'], 'value' => 0.25],
                                        ['label' => 'Dimes', 'count' => $session['ending_coins_dime'], 'value' => 0.10],
                                        ['label' => 'Nickels', 'count' => $session['ending_coins_nickel'], 'value' => 0.05],
                                        ['label' => 'Pennies', 'count' => $session['ending_coins_penny'], 'value' => 0.01],
                                    ];
                                    foreach ($denominations as $denom):
                                        if ($denom['count'] > 0):
                                    ?>
                                    <tr>
                                        <td><?= $denom['label'] ?></td>
                                        <td class="text-end"><?= $denom['count'] ?></td>
                                        <td class="text-end"><?= formatCurrency($denom['count'] * $denom['value']) ?></td>
                                    </tr>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Transactions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Transactions (<?= count($transactions) ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($transactions)): ?>
                    <div class="text-center py-4">
                        <p class="text-muted">No transactions recorded for this session.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Payment Method</th>
                                    <th>Description</th>
                                    <th>Created By</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $txn): ?>
                                <tr>
                                    <td><?= date('g:i A', strtotime($txn['created_at'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= in_array($txn['transaction_type'], ['sale', 'deposit', 'till_payback']) ? 'success' : 'danger' ?>">
                                            <?= ucfirst(str_replace('_', ' ', $txn['transaction_type'])) ?>
                                        </span>
                                    </td>
                                    <td><?= ucfirst($txn['payment_method']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($txn['description']) ?>
                                        <?php if (!empty($txn['notes'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($txn['notes']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($txn['created_by_name']) ?></td>
                                    <td class="text-end">
                                        <strong class="<?= in_array($txn['transaction_type'], ['sale', 'deposit', 'till_payback']) ? 'text-success' : 'text-danger' ?>">
                                            <?= in_array($txn['transaction_type'], ['sale', 'deposit', 'till_payback']) ? '+' : '-' ?>
                                            <?= formatCurrency($txn['amount']) ?>
                                        </strong>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Session Stats Sidebar -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-graph-up"></i> Session Summary</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Total Sales:</td>
                            <td class="text-end"><strong><?= formatCurrency($session['total_sales'] ?? 0) ?></strong></td>
                        </tr>
                        <tr>
                            <td>Total Refunds:</td>
                            <td class="text-end"><strong><?= formatCurrency($session['total_refunds'] ?? 0) ?></strong></td>
                        </tr>
                        <tr>
                            <td>Total Deposits:</td>
                            <td class="text-end"><strong><?= formatCurrency($session['total_deposits'] ?? 0) ?></strong></td>
                        </tr>
                        <tr>
                            <td>Total Withdrawals:</td>
                            <td class="text-end"><strong><?= formatCurrency($session['total_withdrawals'] ?? 0) ?></strong></td>
                        </tr>
                        <tr class="table-light">
                            <td><strong>Net Change:</strong></td>
                            <td class="text-end">
                                <?php
                                $netChange = ($session['total_sales'] ?? 0) + ($session['total_deposits'] ?? 0)
                                           - ($session['total_refunds'] ?? 0) - ($session['total_withdrawals'] ?? 0);
                                ?>
                                <strong class="<?= $netChange >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $netChange >= 0 ? '+' : '' ?><?= formatCurrency($netChange) ?>
                                </strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if ($session['status'] !== 'open'): ?>
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-printer"></i> Actions</h6>
                </div>
                <div class="card-body">
                    <button class="btn btn-outline-primary w-100 mb-2" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print Report
                    </button>
                    <a href="/store/cash-drawer/session/<?= $session['id'] ?>/export" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-download"></i> Export CSV
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
