<?php
$pageTitle = 'Edit Air Fill #' . $airFill['id'];
$activeMenu = 'air-fills';

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/store/air-fills">Air Fills</a></li>
            <li class="breadcrumb-item"><a href="/store/air-fills/<?= $airFill['id'] ?>">Air Fill #<?= $airFill['id'] ?></a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <h1 class="h3">
        <i class="bi bi-pencil"></i> Edit Air Fill #<?= $airFill['id'] ?>
    </h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/store/air-fills/<?= $airFill['id'] ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <!-- Customer Selection -->
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">
                            Customer <span class="text-muted">(Optional for walk-ins)</span>
                        </label>
                        <select name="customer_id" id="customer_id" class="form-select">
                            <option value="">-- Walk-in Customer --</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>"
                                <?= $customer['id'] == $airFill['customer_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($customer['name']) ?> - <?= htmlspecialchars($customer['email']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <!-- Fill Type -->
                        <div class="col-md-6 mb-3">
                            <label for="fill_type" class="form-label">Fill Type <span class="text-danger">*</span></label>
                            <select name="fill_type" id="fill_type" class="form-select" required>
                                <option value="air" <?= $airFill['fill_type'] === 'air' ? 'selected' : '' ?>>Air</option>
                                <option value="nitrox" <?= $airFill['fill_type'] === 'nitrox' ? 'selected' : '' ?>>Nitrox (EAN)</option>
                                <option value="trimix" <?= $airFill['fill_type'] === 'trimix' ? 'selected' : '' ?>>Trimix</option>
                                <option value="oxygen" <?= $airFill['fill_type'] === 'oxygen' ? 'selected' : '' ?>>Oxygen</option>
                            </select>
                        </div>

                        <!-- Nitrox Percentage -->
                        <div class="col-md-6 mb-3" id="nitrox_field"
                             style="display: <?= $airFill['fill_type'] === 'nitrox' ? 'block' : 'none' ?>">
                            <label for="nitrox_percentage" class="form-label">Nitrox Percentage (O2%)</label>
                            <input type="number" name="nitrox_percentage" id="nitrox_percentage"
                                   class="form-control" min="21" max="40"
                                   value="<?= $airFill['nitrox_percentage'] ?? 32 ?>">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Fill Pressure -->
                        <div class="col-md-6 mb-3">
                            <label for="fill_pressure" class="form-label">Fill Pressure (PSI) <span class="text-danger">*</span></label>
                            <input type="number" name="fill_pressure" id="fill_pressure"
                                   class="form-control" min="500" max="4500" required
                                   value="<?= $airFill['fill_pressure'] ?>">
                        </div>

                        <!-- Cost -->
                        <div class="col-md-6 mb-3">
                            <label for="cost" class="form-label">Cost ($) <span class="text-danger">*</span></label>
                            <input type="number" name="cost" id="cost" class="form-control"
                                   min="0" step="0.01" required
                                   value="<?= $airFill['cost'] ?>">
                        </div>
                    </div>

                    <!-- Equipment/Tank -->
                    <div class="mb-3">
                        <label for="equipment_id" class="form-label">
                            Tank/Equipment <span class="text-muted">(Optional)</span>
                        </label>
                        <select name="equipment_id" id="equipment_id" class="form-select">
                            <option value="">-- Select Tank (Optional) --</option>
                            <?php foreach ($tanks as $tank): ?>
                            <option value="<?= $tank['id'] ?>"
                                <?= $tank['id'] == $airFill['equipment_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tank['name']) ?> - <?= htmlspecialchars($tank['equipment_code']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"><?= htmlspecialchars($airFill['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Air Fill
                        </button>
                        <a href="/store/air-fills/<?= $airFill['id'] ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Important</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">
                    <strong>Note:</strong> You can only edit air fills that haven't been linked to a transaction.
                    Once a fill is paid, it becomes part of the financial record and cannot be modified.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fillTypeSelect = document.getElementById('fill_type');
    const nitroxField = document.getElementById('nitrox_field');
    const nitroxInput = document.getElementById('nitrox_percentage');

    fillTypeSelect.addEventListener('change', function() {
        if (this.value === 'nitrox') {
            nitroxField.style.display = 'block';
            nitroxInput.required = true;
        } else {
            nitroxField.style.display = 'none';
            nitroxInput.required = false;
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
