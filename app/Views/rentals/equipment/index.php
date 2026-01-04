<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-gear"></i> Rental Equipment</h2>
    <div>
        <?php if (hasPermission('rentals.create')): ?>
        <a href="/rentals/equipment/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Equipment
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/rentals" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($_GET['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="available" <?= ($_GET['status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="rented" <?= ($_GET['status'] ?? '') === 'rented' ? 'selected' : '' ?>>Rented</option>
                    <option value="maintenance" <?= ($_GET['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    <option value="damaged" <?= ($_GET['status'] ?? '') === 'damaged' ? 'selected' : '' ?>>Damaged</option>
                    <option value="retired" <?= ($_GET['status'] ?? '') === 'retired' ? 'selected' : '' ?>>Retired</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Size</th>
                        <th>Daily Rate</th>
                        <th>Status</th>
                        <th>Condition</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($equipment)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">No equipment found</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($equipment as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['equipment_code']) ?></td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['category_name']) ?></td>
                            <td><?= htmlspecialchars($item['size'] ?? 'N/A') ?></td>
                            <td><?= formatCurrency($item['daily_rate']) ?></td>
                            <td>
                                <?php
                                $statusColors = [
                                    'available' => 'success',
                                    'rented' => 'primary',
                                    'maintenance' => 'warning',
                                    'damaged' => 'danger',
                                    'retired' => 'secondary'
                                ];
                                $color = $statusColors[$item['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $color ?>"><?= ucfirst($item['status']) ?></span>
                            </td>
                            <td>
                                <?php
                                $conditionColors = [
                                    'excellent' => 'success',
                                    'good' => 'info',
                                    'fair' => 'warning',
                                    'poor' => 'danger'
                                ];
                                $color = $conditionColors[$item['condition']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $color ?>"><?= ucfirst($item['condition']) ?></span>
                            </td>
                            <td>
                                <a href="/rentals/equipment/<?= $item['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (hasPermission('rentals.edit')): ?>
                                <a href="/rentals/equipment/<?= $item['id'] ?>/edit" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
