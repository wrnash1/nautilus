<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tools"></i> Edit Work Order</h2>
    <a href="/store/workorders/<?= $workOrder['id'] ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/store/workorders/<?= $workOrder['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="equipment_type" class="form-label">Equipment Type *</label>
                    <input type="text" class="form-control" id="equipment_type" name="equipment_type"
                        value="<?= htmlspecialchars($workOrder['equipment_type']) ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="equipment_brand" class="form-label">Brand</label>
                    <input type="text" class="form-control" id="equipment_brand" name="equipment_brand"
                        value="<?= htmlspecialchars($workOrder['equipment_brand'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="equipment_model" class="form-label">Model</label>
                    <input type="text" class="form-control" id="equipment_model" name="equipment_model"
                        value="<?= htmlspecialchars($workOrder['equipment_model'] ?? '') ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="serial_number" class="form-label">Serial Number</label>
                    <input type="text" class="form-control" id="serial_number" name="serial_number"
                        value="<?= htmlspecialchars($workOrder['serial_number'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="issue_description" class="form-label">Issue Description *</label>
                <textarea class="form-control" id="issue_description" name="issue_description" rows="4"
                    required><?= htmlspecialchars($workOrder['issue_description']) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="priority" class="form-label">Priority *</label>
                <select class="form-select" id="priority" name="priority" required>
                    <option value="low" <?= $workOrder['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="normal" <?= $workOrder['priority'] === 'normal' ? 'selected' : '' ?>>Normal</option>
                    <option value="high" <?= $workOrder['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                    <option value="urgent" <?= $workOrder['priority'] === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                </select>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Work Order
                </button>
                <a href="/store/workorders/<?= $workOrder['id'] ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>