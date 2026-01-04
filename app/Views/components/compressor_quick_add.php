<!-- Compressor Quick Add Widget -->
<div class="card border-primary mb-3">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-speedometer2"></i> Compressor Hours</span>
        <button class="btn btn-sm btn-light" onclick="toggleCompressorWidget()">
            <i class="bi bi-chevron-down" id="compressorWidgetIcon"></i>
        </button>
    </div>
    <div class="card-body" id="compressorWidgetBody">
        <form id="quickAddHoursForm" onsubmit="quickAddHours(event)">
            <div class="mb-3">
                <label for="compressor_select" class="form-label">
                    <i class="bi bi-gear-fill"></i> Compressor
                </label>
                <select class="form-select" id="compressor_select" name="compressor_id" required>
                    <option value="">Select Compressor...</option>
                    <?php
                    use App\Services\Equipment\CompressorService;
                    $compressorService = new CompressorService();
                    $compressors = $compressorService->getAllCompressors();
                    foreach ($compressors as $comp):
                    ?>
                    <option value="<?= $comp['id'] ?>" data-current-hours="<?= $comp['current_hours'] ?>">
                        <?= htmlspecialchars($comp['name']) ?> (<?= number_format($comp['current_hours'], 1) ?> hrs)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="hours_to_add" class="form-label">
                    <i class="bi bi-clock-fill"></i> Hours to Add
                </label>
                <input type="number"
                       class="form-control form-control-lg"
                       id="hours_to_add"
                       name="hours_to_add"
                       step="0.1"
                       min="0.1"
                       max="100"
                       placeholder="0.0"
                       required>
                <div class="form-text">
                    Current: <span id="current_hours_display">-</span> hours
                </div>
            </div>

            <div class="mb-3">
                <label for="quick_notes" class="form-label">Notes (Optional)</label>
                <textarea class="form-control"
                          id="quick_notes"
                          name="notes"
                          rows="2"
                          placeholder="Any notes about this run..."></textarea>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle"></i> Add Hours
                </button>
                <a href="/equipment/compressors" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-gear"></i> Full Compressor Management
                </a>
            </div>
        </form>

        <!-- Recent Activity -->
        <div class="mt-3" id="recentCompressorActivity"></div>
    </div>
</div>

<!-- Maintenance Alerts (if any) -->
<?php
$alerts = $compressorService->getActiveAlerts();
if (!empty($alerts)):
?>
<div class="card border-warning mb-3">
    <div class="card-header bg-warning text-dark">
        <i class="bi bi-exclamation-triangle-fill"></i> Maintenance Alerts (<?= count($alerts) ?>)
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php foreach ($alerts as $alert): ?>
            <div class="list-group-item list-group-item-<?= $alert['severity'] === 'critical' ? 'danger' : 'warning' ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong><?= htmlspecialchars($alert['compressor_name']) ?></strong>
                        <br>
                        <small><?= htmlspecialchars($alert['message']) ?></small>
                    </div>
                    <a href="/equipment/compressors/<?= $alert['compressor_id'] ?>"
                       class="btn btn-sm btn-outline-dark">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Update current hours display when compressor selected
document.getElementById('compressor_select').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const currentHours = option.getAttribute('data-current-hours');
    document.getElementById('current_hours_display').textContent =
        currentHours ? parseFloat(currentHours).toFixed(1) : '-';
});

// Toggle widget visibility
function toggleCompressorWidget() {
    const body = document.getElementById('compressorWidgetBody');
    const icon = document.getElementById('compressorWidgetIcon');

    if (body.style.display === 'none') {
        body.style.display = 'block';
        icon.classList.remove('bi-chevron-right');
        icon.classList.add('bi-chevron-down');
    } else {
        body.style.display = 'none';
        icon.classList.remove('bi-chevron-down');
        icon.classList.add('bi-chevron-right');
    }
}

// Quick add hours
function quickAddHours(event) {
    event.preventDefault();

    const form = document.getElementById('quickAddHoursForm');
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');

    // Disable button
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';

    fetch('/api/compressors/add-hours', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showSuccessMessage('Hours added successfully!');

            // Update current hours display
            const compressorSelect = document.getElementById('compressor_select');
            const option = compressorSelect.options[compressorSelect.selectedIndex];
            const newHours = parseFloat(option.getAttribute('data-current-hours')) +
                           parseFloat(formData.get('hours_to_add'));
            option.setAttribute('data-current-hours', newHours.toFixed(1));
            option.text = option.text.replace(/\([\d.]+/, '(' + newHours.toFixed(1));

            // Reset form
            document.getElementById('hours_to_add').value = '';
            document.getElementById('quick_notes').value = '';
            document.getElementById('current_hours_display').textContent = newHours.toFixed(1);

            // Reload page if maintenance alert triggered
            if (data.alerts_triggered) {
                setTimeout(() => location.reload(), 1500);
            }
        } else {
            showErrorMessage(data.message || 'Failed to add hours');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('An error occurred. Please try again.');
    })
    .finally(() => {
        // Re-enable button
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="bi bi-plus-circle"></i> Add Hours';
    });
}

// Helper functions for messages
function showSuccessMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.getElementById('compressorWidgetBody').insertBefore(
        alertDiv,
        document.getElementById('quickAddHoursForm')
    );

    setTimeout(() => alertDiv.remove(), 3000);
}

function showErrorMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.getElementById('compressorWidgetBody').insertBefore(
        alertDiv,
        document.getElementById('quickAddHoursForm')
    );
}
</script>

<style>
#compressorWidgetBody {
    transition: all 0.3s ease;
}

.form-control-lg {
    font-size: 1.5rem;
    text-align: center;
    font-weight: bold;
}

#current_hours_display {
    font-weight: bold;
    color: #0066cc;
}
</style>
