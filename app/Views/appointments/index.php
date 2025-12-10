<?php
$pageTitle = 'Appointments';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/store/dashboard'],
    ['title' => 'Appointments', 'url' => null]
];

ob_start();
?>

<div class="appointments-page">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>Appointments</h1>
                <p class="subtitle">Manage customer appointments and scheduling</p>
            </div>
            <div class="header-actions">
                <a href="/store/appointments/calendar" class="btn btn-secondary">
                    <i class="fas fa-calendar"></i> Calendar View
                </a>
                <a href="/store/appointments/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Appointment
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
            <?php unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <?php unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" action="/store/appointments" class="filters-form">
            <div class="filter-group">
                <label>Status:</label>
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="scheduled" <?= ($_GET['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                    <option value="confirmed" <?= ($_GET['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    <option value="no_show" <?= ($_GET['status'] ?? '') === 'no_show' ? 'selected' : '' ?>>No Show</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Type:</label>
                <select name="type">
                    <option value="">All Types</option>
                    <option value="fitting" <?= ($_GET['type'] ?? '') === 'fitting' ? 'selected' : '' ?>>Fitting</option>
                    <option value="consultation" <?= ($_GET['type'] ?? '') === 'consultation' ? 'selected' : '' ?>>Consultation</option>
                    <option value="pickup" <?= ($_GET['type'] ?? '') === 'pickup' ? 'selected' : '' ?>>Pickup</option>
                    <option value="other" <?= ($_GET['type'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Assigned To:</label>
                <select name="assigned_to">
                    <option value="">All Staff</option>
                    <?php foreach ($staff as $member): ?>
                        <option value="<?= $member['id'] ?>" <?= ($_GET['assigned_to'] ?? '') == $member['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($member['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Start Date:</label>
                <input type="date" name="start_date" value="<?= $_GET['start_date'] ?? '' ?>">
            </div>

            <div class="filter-group">
                <label>End Date:</label>
                <input type="date" name="end_date" value="<?= $_GET['end_date'] ?? '' ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <a href="/store/appointments" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Appointments Table -->
    <div class="table-container">
        <?php if (empty($appointments)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times fa-3x"></i>
                <h3>No appointments found</h3>
                <p>No appointments match your current filters.</p>
                <a href="/store/appointments/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Appointment
                </a>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Assigned To</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td>
                                <div class="datetime-cell">
                                    <div class="date"><?= date('M j, Y', strtotime($appointment['start_time'])) ?></div>
                                    <div class="time">
                                        <?= date('g:i A', strtotime($appointment['start_time'])) ?> -
                                        <?= date('g:i A', strtotime($appointment['end_time'])) ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="customer-cell">
                                    <strong><?= htmlspecialchars($appointment['customer_name']) ?></strong>
                                    <?php if ($appointment['customer_email']): ?>
                                        <small><?= htmlspecialchars($appointment['customer_email']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="type-badge type-<?= $appointment['appointment_type'] ?>">
                                    <?= ucfirst($appointment['appointment_type']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($appointment['assigned_to_name'] ?? 'Unassigned') ?></td>
                            <td><?= htmlspecialchars($appointment['location'] ?? '-') ?></td>
                            <td>
                                <span class="status-badge status-<?= $appointment['status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $appointment['status'])) ?>
                                </span>
                            </td>
                            <td class="actions-cell">
                                <a href="/store/appointments/<?= $appointment['id'] ?>" class="btn btn-sm btn-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/store/appointments/<?= $appointment['id'] ?>/edit" class="btn btn-sm btn-secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
.appointments-page {
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.header-content h1 {
    margin: 0;
    font-size: 28px;
}

.subtitle {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.filters-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.filters-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #666;
}

.filter-group select,
.filter-group input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.filter-actions {
    display: flex;
    gap: 10px;
}

.table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    overflow: hidden;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #dee2e6;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.datetime-cell .date {
    font-weight: 600;
    color: #333;
}

.datetime-cell .time {
    font-size: 12px;
    color: #666;
}

.customer-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.customer-cell small {
    color: #666;
    font-size: 11px;
}

.type-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.type-fitting { background: #e3f2fd; color: #1976d2; }
.type-consultation { background: #f3e5f5; color: #7b1fa2; }
.type-pickup { background: #e8f5e9; color: #388e3c; }
.type-other { background: #f5f5f5; color: #616161; }

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-scheduled { background: #e3f2fd; color: #1976d2; }
.status-confirmed { background: #e8f5e9; color: #2e7d32; }
.status-completed { background: #f5f5f5; color: #616161; }
.status-cancelled { background: #ffebee; color: #c62828; }
.status-no_show { background: #fff3e0; color: #e65100; }

.actions-cell {
    white-space: nowrap;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    margin: 0 0 10px 0;
    color: #666;
}

.empty-state p {
    margin: 0 0 20px 0;
    color: #999;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
