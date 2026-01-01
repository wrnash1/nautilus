<?php
$pageTitle = 'Equipment Maintenance';
$activeMenu = 'maintenance';

ob_start();
?>

<style>
.maintenance-header {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
}

.maintenance-header h1 {
    margin: 0 0 10px 0;
    font-size: 28px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-card.alert {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-left: 4px solid #dc2626;
}

.stat-card.warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-left: 4px solid #f59e0b;
}

.stat-label {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 8px;
    text-transform: uppercase;
    font-weight: 600;
}

.stat-value {
    font-size: 36px;
    font-weight: bold;
    color: #1e293b;
}

.stat-meta {
    font-size: 13px;
    color: #94a3b8;
    margin-top: 8px;
}

.action-buttons {
    display: flex;
    gap: 12px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.btn-action {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.2s;
}

.btn-action:hover {
    transform: translateY(-2px);
}

.btn-action.schedule {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.section-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    overflow: hidden;
}

.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-body {
    padding: 24px;
}

.equipment-list {
    display: grid;
    gap: 16px;
}

.equipment-item {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s;
}

.equipment-item:hover {
    border-color: #f59e0b;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
}

.equipment-item.overdue {
    border-color: #dc2626;
    background: #fef2f2;
}

.equipment-item.due-soon {
    border-color: #f59e0b;
    background: #fffbeb;
}

.equipment-info {
    flex: 1;
}

.equipment-name {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 4px;
}

.equipment-meta {
    font-size: 13px;
    color: #64748b;
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.urgency-badge {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.urgency-badge.overdue {
    background: #dc2626;
    color: white;
}

.urgency-badge.due-soon {
    background: #f59e0b;
    color: white;
}

.urgency-badge.scheduled {
    background: #10b981;
    color: white;
}

.equipment-actions {
    display: flex;
    gap: 8px;
}

.btn-small {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-record {
    background: #10b981;
    color: white;
}

.btn-record:hover {
    background: #059669;
}

.btn-history {
    background: #3b82f6;
    color: white;
}

.btn-history:hover {
    background: #2563eb;
}

.schedule-table {
    width: 100%;
    border-collapse: collapse;
}

.schedule-table th {
    background: #f8fafc;
    padding: 12px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    border-bottom: 2px solid #e2e8f0;
}

.schedule-table td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 14px;
}

.schedule-table tr:hover {
    background: #f8fafc;
}

.status-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.scheduled {
    background: #dbeafe;
    color: #1e40af;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #94a3b8;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.3;
}
</style>

<div class="maintenance-header">
    <h1><i class="bi bi-tools"></i> Equipment Maintenance</h1>
    <p>Track equipment maintenance, inspections, and service history</p>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card alert">
        <div class="stat-label">Overdue Inspections</div>
        <div class="stat-value"><?= $statistics['overdue'] ?></div>
        <div class="stat-meta">Require immediate attention</div>
    </div>

    <div class="stat-card warning">
        <div class="stat-label">Due Soon</div>
        <div class="stat-value"><?= $statistics['due_soon'] ?></div>
        <div class="stat-meta">Within 7 days</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">In Maintenance</div>
        <div class="stat-value"><?= $statistics['in_maintenance'] ?></div>
        <div class="stat-meta">Currently being serviced</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Scheduled</div>
        <div class="stat-value"><?= $statistics['scheduled'] ?></div>
        <div class="stat-meta">Upcoming maintenance</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">This Month</div>
        <div class="stat-value"><?= $statistics['maintenance_this_month'] ?></div>
        <div class="stat-meta">Services completed</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Monthly Cost</div>
        <div class="stat-value">$<?= number_format($statistics['cost_this_month'], 0) ?></div>
        <div class="stat-meta">Total maintenance cost</div>
    </div>
</div>

<!-- Action Buttons -->
<div class="action-buttons">
    <a href="/maintenance/create" class="btn-action">
        <i class="bi bi-plus-circle"></i>
        Record Maintenance
    </a>
    <a href="/maintenance/schedule" class="btn-action schedule">
        <i class="bi bi-calendar-plus"></i>
        Schedule Maintenance
    </a>
    <a href="/maintenance/cost-analysis" class="btn-action" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
        <i class="bi bi-graph-up"></i>
        Cost Analysis
    </a>
</div>

<!-- Equipment Needing Maintenance -->
<div class="section-card">
    <div class="card-header">
        <h3>
            <i class="bi bi-exclamation-triangle"></i>
            Equipment Requiring Attention
        </h3>
        <span class="text-muted"><?= count($equipmentNeeding) ?> items</span>
    </div>
    <div class="card-body">
        <?php if (empty($equipmentNeeding)): ?>
            <div class="empty-state">
                <i class="bi bi-check-circle"></i>
                <p>All equipment is up to date with maintenance schedules!</p>
            </div>
        <?php else: ?>
            <div class="equipment-list">
                <?php foreach ($equipmentNeeding as $equipment): ?>
                    <div class="equipment-item <?= $equipment['urgency'] ?>">
                        <div class="equipment-info">
                            <div class="equipment-name">
                                <?= htmlspecialchars($equipment['name']) ?>
                                <span style="color: #94a3b8; font-weight: normal; font-size: 14px;">
                                    (<?= htmlspecialchars($equipment['equipment_code']) ?>)
                                </span>
                            </div>
                            <div class="equipment-meta">
                                <span><i class="bi bi-tag"></i> <?= htmlspecialchars($equipment['equipment_type'] ?? 'N/A') ?></span>
                                <span><i class="bi bi-calendar"></i> Due: <?= date('M d, Y', strtotime($equipment['next_inspection_due'])) ?></span>
                                <?php
                                $daysUntil = floor((strtotime($equipment['next_inspection_due']) - time()) / 86400);
                                ?>
                                <span>
                                    <?php if ($daysUntil < 0): ?>
                                        <strong style="color: #dc2626;"><?= abs($daysUntil) ?> days overdue</strong>
                                    <?php else: ?>
                                        <?= $daysUntil ?> days remaining
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="equipment-actions">
                            <span class="urgency-badge <?= $equipment['urgency'] ?>">
                                <?= str_replace('_', ' ', $equipment['urgency']) ?>
                            </span>
                            <button class="btn-small btn-record" onclick="recordMaintenance(<?= $equipment['id'] ?>)">
                                <i class="bi bi-wrench"></i> Record
                            </button>
                            <button class="btn-small btn-history" onclick="viewHistory(<?= $equipment['id'] ?>)">
                                <i class="bi bi-clock-history"></i> History
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Scheduled Maintenance -->
<div class="section-card">
    <div class="card-header">
        <h3>
            <i class="bi bi-calendar-check"></i>
            Scheduled Maintenance
        </h3>
        <span class="text-muted"><?= count($scheduledMaintenance) ?> upcoming</span>
    </div>
    <div class="card-body">
        <?php if (empty($scheduledMaintenance)): ?>
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <p>No maintenance scheduled. Schedule maintenance to stay on top of equipment care.</p>
            </div>
        <?php else: ?>
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Equipment</th>
                        <th>Type</th>
                        <th>Scheduled Date</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scheduledMaintenance as $schedule): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($schedule['equipment_name']) ?></strong>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($schedule['equipment_code']) ?></small>
                            </td>
                            <td><?= ucfirst(str_replace('_', ' ', $schedule['maintenance_type'])) ?></td>
                            <td>
                                <?= date('M d, Y', strtotime($schedule['scheduled_date'])) ?>
                                <br>
                                <small class="text-muted">
                                    <?php
                                    $daysUntil = floor((strtotime($schedule['scheduled_date']) - time()) / 86400);
                                    if ($daysUntil == 0) {
                                        echo 'Today';
                                    } elseif ($daysUntil == 1) {
                                        echo 'Tomorrow';
                                    } elseif ($daysUntil < 0) {
                                        echo abs($daysUntil) . ' days ago';
                                    } else {
                                        echo 'in ' . $daysUntil . ' days';
                                    }
                                    ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($schedule['first_name']): ?>
                                    <?= htmlspecialchars($schedule['first_name'] . ' ' . $schedule['last_name']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?= $schedule['status'] ?>">
                                    <?= ucfirst($schedule['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-small btn-record" onclick="completeMaintenance(<?= $schedule['id'] ?>)">
                                    <i class="bi bi-check"></i> Complete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Upcoming Inspections (Next 14 Days) -->
<?php if (!empty($upcomingInspections)): ?>
<div class="section-card">
    <div class="card-header">
        <h3>
            <i class="bi bi-calendar2-week"></i>
            Upcoming Inspections (Next 14 Days)
        </h3>
        <span class="text-muted"><?= count($upcomingInspections) ?> inspections</span>
    </div>
    <div class="card-body">
        <div class="equipment-list">
            <?php foreach ($upcomingInspections as $equipment): ?>
                <div class="equipment-item">
                    <div class="equipment-info">
                        <div class="equipment-name">
                            <?= htmlspecialchars($equipment['name']) ?>
                            <span style="color: #94a3b8; font-weight: normal; font-size: 14px;">
                                (<?= htmlspecialchars($equipment['equipment_code']) ?>)
                            </span>
                        </div>
                        <div class="equipment-meta">
                            <span><i class="bi bi-calendar"></i> <?= date('M d, Y', strtotime($equipment['next_inspection_due'])) ?></span>
                            <span><?= floor($equipment['days_until']) ?> days</span>
                        </div>
                    </div>
                    <button class="btn-small btn-history" onclick="viewHistory(<?= $equipment['id'] ?>)">
                        <i class="bi bi-clock-history"></i> View History
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function recordMaintenance(equipmentId) {
    window.location.href = `/maintenance/create?equipment_id=${equipmentId}`;
}

function viewHistory(equipmentId) {
    window.location.href = `/maintenance/equipment/${equipmentId}/history`;
}

function completeMaintenance(scheduleId) {
    if (confirm('Mark this scheduled maintenance as completed?')) {
        // In a real implementation, this would open a modal to collect completion details
        window.location.href = `/maintenance/create?schedule_id=${scheduleId}`;
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
