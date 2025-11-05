<?php
// This view content will be rendered inside layouts/app.php
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-upc-scan"></i> Serial Number Tracking</h1>
        <div>
            <a href="/inventory/serial-numbers/scan" class="btn btn-info me-2">
                <i class="bi bi-camera-fill"></i> Scan Barcode
            </a>
            <a href="/inventory/serial-numbers/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Serial Number
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill"></i>
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <?= htmlspecialchars($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); endif; ?>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Serial Number</label>
                    <input type="text" name="serial" class="form-control" placeholder="Search serial..."
                           value="<?= htmlspecialchars($_GET['serial'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="available" <?= ($_GET['status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="sold" <?= ($_GET['status'] ?? '') === 'sold' ? 'selected' : '' ?>>Sold</option>
                        <option value="rented" <?= ($_GET['status'] ?? '') === 'rented' ? 'selected' : '' ?>>Rented</option>
                        <option value="reserved" <?= ($_GET['status'] ?? '') === 'reserved' ? 'selected' : '' ?>>Reserved</option>
                        <option value="service" <?= ($_GET['status'] ?? '') === 'service' ? 'selected' : '' ?>>In Service</option>
                        <option value="damaged" <?= ($_GET['status'] ?? '') === 'damaged' ? 'selected' : '' ?>>Damaged</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Service Due</label>
                    <select name="service_due" class="form-select">
                        <option value="">Any Time</option>
                        <option value="overdue" <?= ($_GET['service_due'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                        <option value="30days" <?= ($_GET['service_due'] ?? '') === '30days' ? 'selected' : '' ?>>Within 30 Days</option>
                        <option value="90days" <?= ($_GET['service_due'] ?? '') === '90days' ? 'selected' : '' ?>>Within 90 Days</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="/inventory/serial-numbers" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Serial Numbers Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list"></i> All Serial Numbers</h5>
            <span class="badge bg-primary"><?= count($serialNumbers ?? []) ?> items</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($serialNumbers)): ?>
            <div class="text-center py-5">
                <i class="bi bi-upc-scan text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3">No serial numbers found.</p>
                <a href="/inventory/serial-numbers/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add First Serial Number
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Serial Number</th>
                            <th>Product</th>
                            <th>Status</th>
                            <th>Condition</th>
                            <th>Location</th>
                            <th>Next Service</th>
                            <th width="150" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($serialNumbers as $sn): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($sn['serial_number']) ?></strong>
                                <?php if ($sn['barcode']): ?>
                                <br><small class="text-muted">
                                    <i class="bi bi-upc"></i> <?= htmlspecialchars($sn['barcode']) ?>
                                </small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($sn['product_name'] ?? 'Unknown') ?></td>
                            <td>
                                <?php
                                $statusColors = [
                                    'available' => 'success',
                                    'sold' => 'secondary',
                                    'rented' => 'info',
                                    'reserved' => 'warning',
                                    'service' => 'primary',
                                    'damaged' => 'danger',
                                    'lost' => 'dark'
                                ];
                                $color = $statusColors[$sn['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $color ?>">
                                    <?= ucfirst($sn['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($sn['condition_rating']): ?>
                                <div class="d-flex align-items-center">
                                    <div class="progress" style="width: 60px; height: 8px;">
                                        <div class="progress-bar bg-<?= $sn['condition_rating'] >= 7 ? 'success' : ($sn['condition_rating'] >= 4 ? 'warning' : 'danger') ?>"
                                             style="width: <?= $sn['condition_rating'] * 10 ?>%"></div>
                                    </div>
                                    <small class="ms-2"><?= $sn['condition_rating'] ?>/10</small>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($sn['location']): ?>
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($sn['location']) ?>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($sn['next_service_due']): ?>
                                    <?php
                                    $dueDate = strtotime($sn['next_service_due']);
                                    $today = time();
                                    $isOverdue = $dueDate < $today;
                                    $isDueSoon = $dueDate < strtotime('+30 days');
                                    ?>
                                    <span class="badge bg-<?= $isOverdue ? 'danger' : ($isDueSoon ? 'warning' : 'secondary') ?>">
                                        <?= date('M j, Y', $dueDate) ?>
                                        <?php if ($isOverdue): ?>
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="/inventory/serial-numbers/<?= $sn['id'] ?>" class="btn btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="/inventory/serial-numbers/<?= $sn['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-info" onclick="printLabel(<?= $sn['id'] ?>)" title="Print Label">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Stats -->
    <?php if (!empty($serialNumbers)): ?>
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success"><?= count(array_filter($serialNumbers, fn($s) => $s['status'] === 'available')) ?></h3>
                    <p class="text-muted mb-0">Available</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info"><?= count(array_filter($serialNumbers, fn($s) => $s['status'] === 'rented')) ?></h3>
                    <p class="text-muted mb-0">Currently Rented</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?= count(array_filter($serialNumbers, fn($s) => $s['status'] === 'service')) ?></h3>
                    <p class="text-muted mb-0">In Service</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger">
                        <?php
                        $overdue = array_filter($serialNumbers, fn($s) =>
                            $s['next_service_due'] && strtotime($s['next_service_due']) < time()
                        );
                        echo count($overdue);
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Service Overdue</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function printLabel(serialId) {
    window.open('/inventory/serial-numbers/' + serialId + '/print-label', '_blank', 'width=400,height=300');
}
</script>
