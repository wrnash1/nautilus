<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar"></i> Create Trip Schedule</h2>
    <a href="/trips/schedules" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/trips/schedules">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="mb-3">
                <label for="trip_id" class="form-label">Trip *</label>
                <select class="form-select" id="trip_id" name="trip_id" required>
                    <option value="">Select a trip...</option>
                    <?php foreach ($trips as $trip): ?>
                        <option value="<?= $trip['id'] ?>">
                            <?= htmlspecialchars($trip['name']) ?> - <?= htmlspecialchars($trip['destination']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="departure_date" class="form-label">Departure Date *</label>
                    <input type="date" class="form-control" id="departure_date" name="departure_date" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="return_date" class="form-label">Return Date *</label>
                    <input type="date" class="form-control" id="return_date" name="return_date" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="max_participants" class="form-label">Max Participants *</label>
                    <input type="number" class="form-control" id="max_participants" name="max_participants" value="20" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="price_override" class="form-label">Price Override</label>
                    <input type="number" step="0.01" class="form-control" id="price_override" name="price_override" placeholder="Leave blank to use trip's default price">
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Create Schedule
                </button>
                <a href="/trips/schedules" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
