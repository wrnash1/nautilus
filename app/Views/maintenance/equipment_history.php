<?php
$pageTitle = 'Equipment Maintenance History';
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
                    <li class="breadcrumb-item active">Equipment History</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-clock-history me-2"></i>Maintenance History</h2>
            <p class="text-muted mb-0">
                <?= htmlspecialchars($equipment['name']) ?>
                <code>(<?= htmlspecialchars($equipment['equipment_code']) ?>)</code>
            </p>
        </div>
        <div>
            <a href="/store/maintenance/create?equipment_id=<?= $equipment['id'] ?>" class="btn btn-success me-2">
                <i class="bi bi-plus-lg me-1"></i>Record Maintenance
            </a>
            <a href="/store/maintenance" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Maintenance Records</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($history)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-tools display-1 text-muted"></i>
                            <p class="mt-3 text-muted">No maintenance records for this equipment</p>
                            <a href="/store/maintenance/create?equipment_id=<?= $equipment['id'] ?>" class="btn btn-primary">
                                Record First Maintenance
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($history as $record): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">
                                                    <?php
                                                        $typeClass = match($record['maintenance_type']) {
                                                            'inspection' => 'bg-primary',
                                                            'service' => 'bg-success',
                                                            'repair' => 'bg-warning text-dark',
                                                            'annual_inspection' => 'bg-info',
                                                            default => 'bg-secondary'
                                                        };
                                                    ?>
                                                    <span class="badge <?= $typeClass ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $record['maintenance_type'])) ?>
                                                    </span>
                                                </h6>
                                                <small class="text-muted">
                                                    <?= date('F j, Y', strtotime($record['performed_date'])) ?>
                                                    <?php if (!empty($record['performed_by_name'])): ?>
                                                        &bull; By <?= htmlspecialchars($record['performed_by_name']) ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <?php if ($record['is_passed']): ?>
                                                    <span class="badge bg-success"><i class="bi bi-check"></i> Passed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"><i class="bi bi-x"></i> Failed</span>
                                                <?php endif; ?>
                                                <?php if ($record['cost'] > 0): ?>
                                                    <div class="mt-1">
                                                        <strong>$<?= number_format($record['cost'], 2) ?></strong>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($record['notes'])): ?>
                                            <p class="mt-2 mb-1"><?= nl2br(htmlspecialchars($record['notes'])) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($record['parts_replaced'])): ?>
                                            <div class="mt-2">
                                                <small class="text-muted">Parts replaced:</small><br>
                                                <small><?= htmlspecialchars($record['parts_replaced']) ?></small>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($record['next_maintenance_date'])): ?>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    Next due: <?= date('M j, Y', strtotime($record['next_maintenance_date'])) ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Equipment Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Equipment Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <th class="text-muted">Name:</th>
                            <td><?= htmlspecialchars($equipment['name']) ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Code:</th>
                            <td><code><?= htmlspecialchars($equipment['equipment_code']) ?></code></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Type:</th>
                            <td><?= htmlspecialchars($equipment['equipment_type'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Status:</th>
                            <td>
                                <?php
                                    $statusClass = match($equipment['status'] ?? 'available') {
                                        'available' => 'bg-success',
                                        'rented' => 'bg-warning text-dark',
                                        'maintenance' => 'bg-info',
                                        'retired' => 'bg-secondary',
                                        default => 'bg-secondary'
                                    };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst($equipment['status'] ?? 'N/A') ?></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Summary -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Maintenance Summary</h6>
                </div>
                <div class="card-body">
                    <?php
                    $totalCost = array_sum(array_column($history, 'cost'));
                    $totalRecords = count($history);
                    ?>
                    <div class="row text-center">
                        <div class="col-6">
                            <h3 class="mb-0"><?= $totalRecords ?></h3>
                            <small class="text-muted">Total Records</small>
                        </div>
                        <div class="col-6">
                            <h3 class="mb-0">$<?= number_format($totalCost, 0) ?></h3>
                            <small class="text-muted">Total Cost</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
