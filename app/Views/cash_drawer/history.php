<?php
$pageTitle = 'Cash Drawer History';
$activeMenu = 'cash-drawer';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-clock-history"></i> Cash Drawer Session History</h1>
        <a href="/store/cash-drawer" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Filter Options -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/store/cash-drawer/history" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <select name="range" class="form-select" onchange="this.form.submit()">
                        <option value="today" <?= ($_GET['range'] ?? 'today') === 'today' ? 'selected' : '' ?>>Today</option>
                        <option value="week" <?= ($_GET['range'] ?? '') === 'week' ? 'selected' : '' ?>>This Week</option>
                        <option value="month" <?= ($_GET['range'] ?? '') === 'month' ? 'selected' : '' ?>>This Month</option>
                        <option value="all" <?= ($_GET['range'] ?? '') === 'all' ? 'selected' : '' ?>>All Time</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="balanced" <?= ($_GET['status'] ?? '') === 'balanced' ? 'selected' : '' ?>>Balanced</option>
                        <option value="over" <?= ($_GET['status'] ?? '') === 'over' ? 'selected' : '' ?>>Over</option>
                        <option value="short" <?= ($_GET['status'] ?? '') === 'short' ? 'selected' : '' ?>>Short</option>
                        <option value="open" <?= ($_GET['status'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Session Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Sessions</h6>
                    <h3 class="mb-0"><?= count($sessions) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Balanced Sessions</h6>
                    <h3 class="mb-0 text-success">
                        <?= count(array_filter($sessions, fn($s) => $s['status'] === 'balanced')) ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Overages</h6>
                    <h3 class="mb-0 text-warning">
                        <?= count(array_filter($sessions, fn($s) => $s['status'] === 'over')) ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Shortages</h6>
                    <h3 class="mb-0 text-danger">
                        <?= count(array_filter($sessions, fn($s) => $s['status'] === 'short')) ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list"></i> All Sessions</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($sessions)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No sessions found.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Session #</th>
                            <th>Drawer</th>
                            <th>Opened By</th>
                            <th>Date</th>
                            <th>Duration</th>
                            <th>Starting</th>
                            <th>Ending</th>
                            <th>Expected</th>
                            <th>Difference</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($session['session_number']) ?></code></td>
                            <td>
                                <strong><?= htmlspecialchars($session['drawer_name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($session['drawer_location']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($session['opened_by_name']) ?></td>
                            <td>
                                <?= date('m/d/Y', strtotime($session['opened_at'])) ?><br>
                                <small class="text-muted"><?= date('g:i A', strtotime($session['opened_at'])) ?></small>
                            </td>
                            <td>
                                <?php
                                if ($session['status'] === 'open') {
                                    echo $session['hours_open'] . ' hrs';
                                } else {
                                    $start = strtotime($session['opened_at']);
                                    $end = strtotime($session['closed_at']);
                                    $hours = round(($end - $start) / 3600, 1);
                                    echo $hours . ' hrs';
                                }
                                ?>
                            </td>
                            <td><?= formatCurrency($session['starting_balance']) ?></td>
                            <td>
                                <?php if ($session['status'] === 'open'): ?>
                                <span class="text-muted">-</span>
                                <?php else: ?>
                                <?= formatCurrency($session['ending_balance']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($session['status'] === 'open'): ?>
                                <?= formatCurrency($session['expected_current_balance']) ?>
                                <?php else: ?>
                                <?= formatCurrency($session['expected_balance']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($session['status'] === 'open'): ?>
                                <span class="text-muted">-</span>
                                <?php else: ?>
                                <?php
                                $diff = $session['difference'];
                                $class = abs($diff) < 0.01 ? 'text-success' : ($diff > 0 ? 'text-warning' : 'text-danger');
                                ?>
                                <span class="<?= $class ?>">
                                    <?= $diff >= 0 ? '+' : '' ?><?= formatCurrency($diff) ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $badges = [
                                    'open' => 'bg-info',
                                    'balanced' => 'bg-success',
                                    'over' => 'bg-warning text-dark',
                                    'short' => 'bg-danger',
                                    'closed' => 'bg-secondary'
                                ];
                                $badgeClass = $badges[$session['status']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= ucfirst($session['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="/store/cash-drawer/session/<?= $session['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
