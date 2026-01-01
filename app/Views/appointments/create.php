<?php
$pageTitle = 'Create Appointment';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/store/dashboard'],
    ['title' => 'Appointments', 'url' => '/store/appointments'],
    ['title' => 'Create', 'url' => null]
];

ob_start();
?>

<div class="create-appointment-page">
    <div class="page-header">
        <h1>Create New Appointment</h1>
        <p class="subtitle">Schedule a new customer appointment</p>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <?php unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/store/appointments" class="appointment-form">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="form-section">
            <h2>Customer Information</h2>

            <div class="form-group required">
                <label for="customer_id">Customer</label>
                <select name="customer_id" id="customer_id" required>
                    <option value="">Select a customer...</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['id'] ?>">
                            <?= htmlspecialchars($customer['name']) ?>
                            <?php if ($customer['email']): ?>
                                - <?= htmlspecialchars($customer['email']) ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-section">
            <h2>Appointment Details</h2>

            <div class="form-row">
                <div class="form-group required">
                    <label for="appointment_type">Type</label>
                    <select name="appointment_type" id="appointment_type" required>
                        <option value="">Select type...</option>
                        <option value="fitting">Equipment Fitting</option>
                        <option value="consultation">Consultation</option>
                        <option value="pickup">Pickup</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="assigned_to">Assign To</label>
                    <select name="assigned_to" id="assigned_to">
                        <option value="">Unassigned</option>
                        <?php foreach ($staff as $member): ?>
                            <option value="<?= $member['id'] ?>">
                                <?= htmlspecialchars($member['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group required">
                    <label for="start_time">Start Time</label>
                    <input type="datetime-local" name="start_time" id="start_time" required>
                </div>

                <div class="form-group required">
                    <label for="end_time">End Time</label>
                    <input type="datetime-local" name="end_time" id="end_time" required>
                </div>
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" name="location" id="location" placeholder="e.g., Main Store, Pool Area">
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="scheduled" selected>Scheduled</option>
                    <option value="confirmed">Confirmed</option>
                </select>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="4" placeholder="Any additional notes..."></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create Appointment
            </button>
            <a href="/store/appointments" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<style>
.create-appointment-page {
    padding: 20px;
    max-width: 900px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    margin: 0;
    font-size: 28px;
}

.subtitle {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
}

.appointment-form {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e0e0e0;
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h2 {
    margin: 0 0 20px 0;
    font-size: 18px;
    color: #333;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 20px;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
    font-size: 14px;
}

.form-group.required label::after {
    content: ' *';
    color: #dc3545;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

.form-group textarea {
    resize: vertical;
}

.form-actions {
    display: flex;
    gap: 10px;
    padding-top: 20px;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
}

.alert-error {
    background: #f8d7da;
    color: #842029;
    border: 1px solid #f5c2c7;
}
</style>

<script>
// Auto-calculate end time (default 1 hour after start)
document.getElementById('start_time').addEventListener('change', function() {
    const startTime = new Date(this.value);
    if (!isNaN(startTime.getTime())) {
        const endTime = new Date(startTime.getTime() + (60 * 60 * 1000)); // Add 1 hour
        const endInput = document.getElementById('end_time');

        if (!endInput.value) {
            endInput.value = endTime.toISOString().slice(0, 16);
        }
    }
});

// Form validation
document.querySelector('.appointment-form').addEventListener('submit', function(e) {
    const startTime = new Date(document.getElementById('start_time').value);
    const endTime = new Date(document.getElementById('end_time').value);

    if (endTime <= startTime) {
        e.preventDefault();
        alert('End time must be after start time');
        return false;
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
