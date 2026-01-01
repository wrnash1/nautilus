<?php
$pageTitle = 'Schedule Maintenance';
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
                    <li class="breadcrumb-item active">Schedule Maintenance</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar-plus me-2"></i>Schedule Maintenance</h2>
        <a href="/store/maintenance" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/store/maintenance/schedule" id="scheduleForm">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="mb-4">
                            <label class="form-label">Equipment <span class="text-danger">*</span></label>
                            <select name="equipment_id" class="form-select" required>
                                <option value="">-- Select Equipment --</option>
                                <?php
                                $db = \App\Core\Database::getInstance()->getConnection();
                                $stmt = $db->query("SELECT id, name, equipment_code FROM rental_equipment WHERE status != 'retired' ORDER BY name");
                                $equipment = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                                foreach ($equipment as $eq):
                                ?>
                                    <option value="<?= $eq['id'] ?>">
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
                                <label class="form-label">Scheduled Date <span class="text-danger">*</span></label>
                                <input type="date" name="scheduled_date" class="form-control"
                                       min="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Assign To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">-- Unassigned --</option>
                                <?php
                                $stmt = $db->query("SELECT id, first_name, last_name FROM users WHERE is_active = 1 ORDER BY first_name, last_name");
                                $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                                foreach ($users as $user):
                                ?>
                                    <option value="<?= $user['id'] ?>">
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Add any special instructions or notes..."></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/store/maintenance" class="btn btn-outline-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-calendar-plus me-1"></i>Schedule Maintenance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-lightbulb me-2"></i>Scheduling Tips</h5>
                    <p class="small mb-0">
                        Schedule maintenance during off-peak times to minimize equipment downtime.
                        Regular preventive maintenance extends equipment life and prevents costly emergency repairs.
                    </p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Maintenance Types</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2"><strong>Inspection:</strong> Safety and operational checks</li>
                        <li class="mb-2"><strong>Service:</strong> Routine preventive maintenance</li>
                        <li class="mb-2"><strong>Repair:</strong> Fix broken or malfunctioning equipment</li>
                        <li class="mb-2"><strong>Annual Inspection:</strong> Yearly certification checks</li>
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
