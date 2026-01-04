<div class="mb-4">
    <h2><i class="bi bi-plus-circle"></i> Add Rental Equipment</h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/store/rentals/equipment">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Equipment Code <span class="text-danger">*</span></label>
                    <input type="text" name="equipment_code" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Size</label>
                    <input type="text" name="size" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Manufacturer</label>
                    <input type="text" name="manufacturer" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Serial Number</label>
                    <input type="text" name="serial_number" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Purchase Date</label>
                    <input type="date" name="purchase_date" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Purchase Cost</label>
                    <input type="number" step="0.01" name="purchase_cost" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Daily Rate <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="daily_rate" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Weekly Rate</label>
                    <input type="number" step="0.01" name="weekly_rate" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Weekend Rate</label>
                    <input type="number" step="0.01" name="weekend_rate" class="form-control"
                        placeholder="Fri-Sun rate">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="available">Available</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="damaged">Damaged</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Condition</label>
                    <select name="condition" class="form-select">
                        <option value="excellent">Excellent</option>
                        <option value="good" selected>Good</option>
                        <option value="fair">Fair</option>
                        <option value="poor">Poor</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Add Equipment
                </button>
                <a href="/store/rentals" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>