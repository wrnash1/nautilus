<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-gear"></i> Edit Equipment</h2>
    <a href="/rentals/equipment/<?= $equipment['id'] ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/rentals/equipment/<?= $equipment['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="equipment_code" class="form-label">Equipment Code *</label>
                    <input type="text" class="form-control" id="equipment_code" name="equipment_code" value="<?= htmlspecialchars($equipment['equipment_code']) ?>" required readonly>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="category_id" class="form-label">Category *</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $equipment['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Equipment Name *</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($equipment['name']) ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="size" class="form-label">Size</label>
                    <input type="text" class="form-control" id="size" name="size" value="<?= htmlspecialchars($equipment['size'] ?? '') ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="manufacturer" class="form-label">Manufacturer</label>
                    <input type="text" class="form-control" id="manufacturer" name="manufacturer" value="<?= htmlspecialchars($equipment['manufacturer'] ?? '') ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="model" class="form-label">Model</label>
                    <input type="text" class="form-control" id="model" name="model" value="<?= htmlspecialchars($equipment['model'] ?? '') ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="serial_number" class="form-label">Serial Number</label>
                    <input type="text" class="form-control" id="serial_number" name="serial_number" value="<?= htmlspecialchars($equipment['serial_number'] ?? '') ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="purchase_date" class="form-label">Purchase Date</label>
                    <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="<?= $equipment['purchase_date'] ?? '' ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="purchase_cost" class="form-label">Purchase Cost</label>
                    <input type="number" step="0.01" class="form-control" id="purchase_cost" name="purchase_cost" value="<?= $equipment['purchase_cost'] ?? '' ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="daily_rate" class="form-label">Daily Rate *</label>
                    <input type="number" step="0.01" class="form-control" id="daily_rate" name="daily_rate" value="<?= $equipment['daily_rate'] ?>" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="weekly_rate" class="form-label">Weekly Rate</label>
                    <input type="number" step="0.01" class="form-control" id="weekly_rate" name="weekly_rate" value="<?= $equipment['weekly_rate'] ?? '' ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status *</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="available" <?= $equipment['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="rented" <?= $equipment['status'] === 'rented' ? 'selected' : '' ?>>Rented</option>
                        <option value="maintenance" <?= $equipment['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        <option value="damaged" <?= $equipment['status'] === 'damaged' ? 'selected' : '' ?>>Damaged</option>
                        <option value="retired" <?= $equipment['status'] === 'retired' ? 'selected' : '' ?>>Retired</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="condition" class="form-label">Condition *</label>
                    <select class="form-select" id="condition" name="condition" required>
                        <option value="excellent" <?= $equipment['condition'] === 'excellent' ? 'selected' : '' ?>>Excellent</option>
                        <option value="good" <?= $equipment['condition'] === 'good' ? 'selected' : '' ?>>Good</option>
                        <option value="fair" <?= $equipment['condition'] === 'fair' ? 'selected' : '' ?>>Fair</option>
                        <option value="poor" <?= $equipment['condition'] === 'poor' ? 'selected' : '' ?>>Poor</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($equipment['notes'] ?? '') ?></textarea>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Equipment
                </button>
                <a href="/rentals/equipment/<?= $equipment['id'] ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
