<?php
$pageTitle = $title ?? 'Upcoming Layaway Payments';
$activeMenu = 'financial';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-bell me-2"></i>Upcoming Layaway Payments
        </h1>
        <a href="/store/layaway" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Show payments due within</label>
                    <select name="days" class="form-select" onchange="this.form.submit()">
                        <option value="7" <?= ($daysAhead ?? 7) == 7 ? 'selected' : '' ?>>7 days</option>
                        <option value="14" <?= ($daysAhead ?? 7) == 14 ? 'selected' : '' ?>>14 days</option>
                        <option value="30" <?= ($daysAhead ?? 7) == 30 ? 'selected' : '' ?>>30 days</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($payments ?? [])): ?>
                <div class="text-center py-5">
                    <i class="bi bi-check-circle display-1 text-success"></i>
                    <h4 class="mt-3">No upcoming payments</h4>
                    <p class="text-muted">There are no layaway payments due in the next <?= $daysAhead ?? 7 ?> days.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Due Date</th>
                                <th>Agreement #</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th class="text-end">Amount Due</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <?php
                                $dueDate = strtotime($payment['due_date']);
                                $today = strtotime('today');
                                $isOverdue = $dueDate < $today;
                                $isDueToday = $dueDate == $today;
                                $rowClass = $isOverdue ? 'table-danger' : ($isDueToday ? 'table-warning' : '');
                                ?>
                                <tr class="<?= $rowClass ?>">
                                    <td>
                                        <strong><?= date('M j, Y', $dueDate) ?></strong>
                                        <?php if ($isOverdue): ?>
                                            <span class="badge bg-danger">Overdue</span>
                                        <?php elseif ($isDueToday): ?>
                                            <span class="badge bg-warning text-dark">Due Today</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/store/layaway/<?= $payment['agreement_id'] ?? '' ?>">
                                            <?= htmlspecialchars($payment['agreement_number'] ?? 'N/A') ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? '')) ?></td>
                                    <td>
                                        <?php if ($payment['phone'] ?? null): ?>
                                            <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($payment['phone']) ?><br>
                                        <?php endif; ?>
                                        <?php if ($payment['email'] ?? null): ?>
                                            <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($payment['email']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <strong>$<?= number_format($payment['amount_due'] ?? 0, 2) ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = ['pending' => 'warning', 'late' => 'danger', 'partial' => 'info'];
                                        $color = $statusColors[$payment['payment_status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $color ?>">
                                            <?= ucfirst($payment['payment_status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/store/layaway/<?= $payment['agreement_id'] ?? '' ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-cash me-1"></i>Process
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Tip:</strong> Contact customers with overdue payments to arrange payment collection.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
