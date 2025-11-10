<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tools"></i> Work Orders</h2>
    <div>
        <?php if (hasPermission('workorders.create')): ?>
        <a href="/workorders/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Work Order
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/workorders" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="in_progress" <?= ($_GET['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="low" <?= ($_GET['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="normal" <?= ($_GET['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
                    <option value="high" <?= ($_GET['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                    <option value="urgent" <?= ($_GET['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                </select>
            </div>
            <div class="col-md-2">
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
                        <th>WO #</th>
                        <th>Customer</th>
                        <th>Equipment</th>
                        <th>Issue</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($workOrders)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">No work orders found</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($workOrders as $wo): ?>
                        <tr>
                            <td><?= htmlspecialchars($wo['work_order_number']) ?></td>
                            <td><?= htmlspecialchars($wo['customer_name'] ?? 'Walk-in') ?></td>
                            <td><?= htmlspecialchars($wo['equipment_type']) ?></td>
                            <td><?= htmlspecialchars(substr($wo['issue_description'], 0, 50)) ?>...</td>
                            <td>
                                <?php
                                $priorityColors = [
                                    'low' => 'secondary',
                                    'normal' => 'info',
                                    'high' => 'warning',
                                    'urgent' => 'danger'
                                ];
                                $color = $priorityColors[$wo['priority']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $color ?>"><?= ucfirst($wo['priority']) ?></span>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'in_progress' => 'primary',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $color = $statusColors[$wo['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $color ?>"><?= ucfirst(str_replace('_', ' ', $wo['status'])) ?></span>
                            </td>
                            <td><?= date('M j, Y', strtotime($wo['created_at'])) ?></td>
                            <td>
                                <a href="/workorders/<?= $wo['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
