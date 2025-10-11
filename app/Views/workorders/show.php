<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tools"></i> <?= htmlspecialchars($workOrder['work_order_number']) ?></h2>
    <div>
        <?php if (hasPermission('workorders.edit')): ?>
        <a href="/workorders/<?= $workOrder['id'] ?>/edit" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <?php endif; ?>
        <a href="/workorders" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Work Order Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Customer:</strong><br>
                        <?= htmlspecialchars($workOrder['customer_name'] ?? 'Walk-in') ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        <?php
                        $statusColors = [
                            'pending' => 'warning',
                            'in_progress' => 'primary',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $color = $statusColors[$workOrder['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?>"><?= ucfirst(str_replace('_', ' ', $workOrder['status'])) ?></span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Equipment Type:</strong><br>
                        <?= htmlspecialchars($workOrder['equipment_type']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Brand:</strong><br>
                        <?= htmlspecialchars($workOrder['equipment_brand'] ?? 'N/A') ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Model:</strong><br>
                        <?= htmlspecialchars($workOrder['equipment_model'] ?? 'N/A') ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Serial Number:</strong><br>
                        <?= htmlspecialchars($workOrder['serial_number'] ?? 'N/A') ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Issue Description:</strong><br>
                        <?= nl2br(htmlspecialchars($workOrder['issue_description'])) ?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <strong>Priority:</strong><br>
                        <?php
                        $priorityColors = [
                            'low' => 'secondary',
                            'normal' => 'info',
                            'high' => 'warning',
                            'urgent' => 'danger'
                        ];
                        $color = $priorityColors[$workOrder['priority']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?>"><?= ucfirst($workOrder['priority']) ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Created:</strong><br>
                        <?= date('M j, Y g:i A', strtotime($workOrder['created_at'])) ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Notes</h5>
            </div>
            <div class="card-body">
                <?php if (empty($notes)): ?>
                <p class="text-muted">No notes yet</p>
                <?php else: ?>
                    <?php foreach ($notes as $note): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">
                            <?= htmlspecialchars($note['author_name']) ?> - 
                            <?= date('M j, Y g:i A', strtotime($note['created_at'])) ?>
                        </small>
                        <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($note['note'])) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <?php if (hasPermission('workorders.edit') && $workOrder['status'] !== 'completed'): ?>
                <form method="POST" action="/workorders/<?= $workOrder['id'] ?>/status" class="mb-3">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <select name="status" class="form-select mb-2">
                        <option value="pending" <?= $workOrder['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="in_progress" <?= $workOrder['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= $workOrder['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $workOrder['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                    <button type="submit" class="btn btn-primary w-100">Update Status</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
