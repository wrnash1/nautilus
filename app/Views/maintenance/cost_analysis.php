<?php
$pageTitle = 'Maintenance Cost Analysis';
$activeMenu = 'maintenance';

ob_start();
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/maintenance">Maintenance</a></li>
                    <li class="breadcrumb-item active">Cost Analysis</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-graph-up me-2"></i>Maintenance Cost Analysis</h2>
        <a href="/store/maintenance" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control"
                           value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control"
                           value="<?= htmlspecialchars($endDate) ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-1"></i>Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">$<?= number_format($analysis['total_cost'] ?? 0, 2) ?></h3>
                    <small>Total Cost</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $analysis['total_records'] ?? 0 ?></h3>
                    <small>Maintenance Records</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">$<?= number_format($analysis['average_cost'] ?? 0, 2) ?></h3>
                    <small>Average Cost</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $analysis['equipment_serviced'] ?? 0 ?></h3>
                    <small>Equipment Serviced</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Cost by Type -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Cost by Maintenance Type</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($analysis['by_type'])): ?>
                        <p class="text-muted text-center">No data for selected period</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-end">Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($analysis['by_type'] as $type): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= ucfirst(str_replace('_', ' ', $type['maintenance_type'])) ?>
                                            </span>
                                        </td>
                                        <td class="text-center"><?= $type['count'] ?></td>
                                        <td class="text-end">$<?= number_format($type['total_cost'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Equipment by Cost -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Top Equipment by Maintenance Cost</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($analysis['by_equipment'])): ?>
                        <p class="text-muted text-center">No data for selected period</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Equipment</th>
                                    <th class="text-center">Services</th>
                                    <th class="text-end">Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($analysis['by_equipment'], 0, 10) as $eq): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($eq['equipment_name']) ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($eq['equipment_code']) ?></small>
                                        </td>
                                        <td class="text-center"><?= $eq['count'] ?></td>
                                        <td class="text-end"><strong>$<?= number_format($eq['total_cost'], 2) ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trend -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Monthly Maintenance Trend</h5>
        </div>
        <div class="card-body">
            <?php if (empty($analysis['monthly_trend'])): ?>
                <p class="text-muted text-center">No data for selected period</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-center">Services</th>
                            <th class="text-end">Cost</th>
                            <th>Visual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $maxCost = max(array_column($analysis['monthly_trend'], 'total_cost'));
                        foreach ($analysis['monthly_trend'] as $month):
                            $percentage = $maxCost > 0 ? ($month['total_cost'] / $maxCost) * 100 : 0;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($month['month']) ?></td>
                                <td class="text-center"><?= $month['count'] ?></td>
                                <td class="text-end">$<?= number_format($month['total_cost'], 2) ?></td>
                                <td style="width: 40%">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-primary" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
