<?php
$pageTitle = 'Record Maintenance';
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
                    <li class="breadcrumb-item active">Record Maintenance</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-tools me-2"></i>Record Maintenance</h2>
        <a href="/store/maintenance" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/store/maintenance/record">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="mb-4">
                            <label class="form-label">Equipment <span class="text-danger">*</span></label>
                            <select name="equipment_id" class="form-select" required>
                                <option value="">-- Select Equipment --</option>
                                <?php
                                // Fetch equipment for dropdown
                                $db = \App\Core\Database::getInstance()->getConnection();
                                $stmt = $db->query("SELECT id, name, equipment_code FROM rental_equipment WHERE status != 'retired' ORDER BY name");
                                $equipment = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                                foreach ($equipment as $eq):
                                ?>
                                    <option value="<?= $eq['id'] ?>" <?= ($_GET['equipment_id'] ?? '') == $eq['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($eq['name']) ?> (<?= htmlspecialchars($eq['equipment_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                                <select name="maintenance_type" class="form-select" required>
                                    <option value="">-- Select Type --</option>
                                    <?php foreach ($maintenanceTypes as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $type)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Maintenance Date <span class="text-danger">*</span></label>
                                <input type="date" name="maintenance_date" class="form-control"
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="Describe the maintenance work performed..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Parts Replaced</label>
                            <textarea name="parts_replaced" class="form-control" rows="2"
                                      placeholder="List any parts that were replaced..."></textarea>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="cost" class="form-control" step="0.01" min="0" value="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Next Service Date</label>
                                <input type="date" name="next_service_date" class="form-control">
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/store/maintenance" class="btn btn-outline-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i>Record Maintenance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>Maintenance Tips</h5>
                    <ul class="mb-0">
                        <li>Record all maintenance immediately after completion</li>
                        <li>Include detailed notes for future reference</li>
                        <li>Track costs for budgeting purposes</li>
                        <li>Set next service dates to stay on schedule</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
