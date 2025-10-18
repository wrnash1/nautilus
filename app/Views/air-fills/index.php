<?php
$pageTitle = 'Air Fills';
$activeMenu = 'air-fills';

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-wind"></i> Air Fills Management
        </h1>
        <p class="text-muted mb-0">Track tank fills and air services</p>
    </div>
    <div>
        <?php if (hasPermission('air_fills.create')): ?>
        <a href="/air-fills/quick-fill" class="btn btn-success me-2">
            <i class="bi bi-lightning-fill"></i> Quick Fill
        </a>
        <a href="/air-fills/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Air Fill
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Today's Fills</p>
                        <h3 class="mb-0"><?= $stats['today_count'] ?></h3>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-wind" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Today's Revenue</p>
                        <h3 class="mb-0"><?= formatCurrency($stats['today_revenue']) ?></h3>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Total Fills</p>
                        <h3 class="mb-0"><?= number_format($stats['total_fills']) ?></h3>
                    </div>
                    <div class="text-info">
                        <i class="bi bi-bar-chart" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Total Revenue</p>
                        <h3 class="mb-0"><?= formatCurrency($stats['total_revenue']) ?></h3>
                    </div>
                    <div class="text-warning">
                        <i class="bi bi-cash-stack" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/air-fills" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Customer name or email..."
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label">Fill Type</label>
                <select name="fill_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="air" <?= ($_GET['fill_type'] ?? '') === 'air' ? 'selected' : '' ?>>Air</option>
                    <option value="nitrox" <?= ($_GET['fill_type'] ?? '') === 'nitrox' ? 'selected' : '' ?>>Nitrox</option>
                    <option value="trimix" <?= ($_GET['fill_type'] ?? '') === 'trimix' ? 'selected' : '' ?>>Trimix</option>
                    <option value="oxygen" <?= ($_GET['fill_type'] ?? '') === 'oxygen' ? 'selected' : '' ?>>Oxygen</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control"
                       value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control"
                       value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
            </div>

            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="/air-fills" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
                <a href="/air-fills/export?<?= http_build_query($_GET) ?>" class="btn btn-success">
                    <i class="bi bi-download"></i> Export
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Air Fills Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Date & Time</th>
                        <th>Customer</th>
                        <th>Fill Type</th>
                        <th>Pressure</th>
                        <th>Cost</th>
                        <th>Filled By</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($airFills)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-2">No air fills found</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($airFills as $fill): ?>
                        <tr>
                            <td><strong>#<?= $fill['id'] ?></strong></td>
                            <td><?= date('M d, Y g:i A', strtotime($fill['created_at'])) ?></td>
                            <td>
                                <?php if ($fill['customer_name']): ?>
                                    <a href="/customers/<?= $fill['customer_id'] ?>">
                                        <?= htmlspecialchars($fill['customer_name']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Walk-in</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?=
                                    $fill['fill_type'] === 'air' ? 'primary' :
                                    ($fill['fill_type'] === 'nitrox' ? 'success' :
                                    ($fill['fill_type'] === 'trimix' ? 'warning' : 'info'))
                                ?>">
                                    <?= strtoupper($fill['fill_type']) ?>
                                </span>
                                <?php if ($fill['fill_type'] === 'nitrox' && $fill['nitrox_percentage']): ?>
                                    <small class="text-muted">(<?= $fill['nitrox_percentage'] ?>%)</small>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($fill['fill_pressure']) ?> PSI</td>
                            <td><strong><?= formatCurrency($fill['cost']) ?></strong></td>
                            <td><?= htmlspecialchars($fill['filled_by_name']) ?></td>
                            <td>
                                <?php if ($fill['transaction_id']): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/air-fills/<?= $fill['id'] ?>" class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (hasPermission('air_fills.update') && !$fill['transaction_id']): ?>
                                    <a href="/air-fills/<?= $fill['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('air_fills.delete') && !$fill['transaction_id']): ?>
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="confirmDelete(<?= $fill['id'] ?>)" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="card-footer">
        <nav>
            <ul class="pagination mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                        Previous
                    </a>
                </li>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>

                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                        Next
                    </a>
                </li>
            </ul>
        </nav>
        <small class="text-muted">
            Showing <?= count($airFills) ?> of <?= number_format($totalRecords) ?> air fills
        </small>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Form -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="_method" value="DELETE">
</form>

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this air fill? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = '/air-fills/' + id;
        form.submit();
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
