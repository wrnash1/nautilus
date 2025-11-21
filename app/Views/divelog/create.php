<?php $this->layout('layouts/admin', ['title' => $title ?? 'Log New Dive']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/dive-logs">Dive Logs</a></li>
                    <li class="breadcrumb-item active">Log New Dive</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-journal-plus me-2"></i>Log New Dive</h5>
                </div>
                <div class="card-body">
                    <form action="/store/dive-logs" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <h6 class="border-bottom pb-2 mb-3">Diver & Location</h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer_id" class="form-label">Diver *</label>
                                <select class="form-select" id="customer_id" name="customer_id" required>
                                    <option value="">Select Diver...</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>">
                                            <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="dive_date" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="dive_date" name="dive_date"
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="dive_site_id" class="form-label">Dive Site (from database)</label>
                                <select class="form-select" id="dive_site_id" name="dive_site_id">
                                    <option value="">Select or enter manually below</option>
                                    <?php foreach ($diveSites as $site): ?>
                                        <option value="<?= $site['id'] ?>">
                                            <?= htmlspecialchars($site['name']) ?>
                                            <?php if ($site['max_depth_feet']): ?>
                                                (max <?= $site['max_depth_feet'] ?> ft)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="dive_site_name" class="form-label">Or Enter Site Name</label>
                                <input type="text" class="form-control" id="dive_site_name" name="dive_site_name"
                                       placeholder="Enter dive site if not in list">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="location" class="form-label">Location/City</label>
                                <input type="text" class="form-control" id="location" name="location"
                                       placeholder="e.g., Cozumel, Mexico">
                            </div>
                            <div class="col-md-6">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country">
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3 mt-4">Dive Profile</h6>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="entry_time" class="form-label">Entry Time</label>
                                <input type="time" class="form-control" id="entry_time" name="entry_time">
                            </div>
                            <div class="col-md-3">
                                <label for="exit_time" class="form-label">Exit Time</label>
                                <input type="time" class="form-control" id="exit_time" name="exit_time">
                            </div>
                            <div class="col-md-3">
                                <label for="bottom_time_minutes" class="form-label">Bottom Time (min)</label>
                                <input type="number" class="form-control" id="bottom_time_minutes" name="bottom_time_minutes" min="0">
                            </div>
                            <div class="col-md-3">
                                <label for="dive_type" class="form-label">Dive Type</label>
                                <select class="form-select" id="dive_type" name="dive_type">
                                    <option value="recreational">Recreational</option>
                                    <option value="training">Training</option>
                                    <option value="night">Night Dive</option>
                                    <option value="drift">Drift Dive</option>
                                    <option value="wreck">Wreck Dive</option>
                                    <option value="deep">Deep Dive</option>
                                    <option value="technical">Technical</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="max_depth_feet" class="form-label">Max Depth (ft)</label>
                                <input type="number" class="form-control" id="max_depth_feet" name="max_depth_feet" min="0" step="0.1">
                            </div>
                            <div class="col-md-3">
                                <label for="average_depth_feet" class="form-label">Avg Depth (ft)</label>
                                <input type="number" class="form-control" id="average_depth_feet" name="average_depth_feet" min="0" step="0.1">
                            </div>
                            <div class="col-md-3">
                                <label for="starting_pressure_psi" class="form-label">Start PSI</label>
                                <input type="number" class="form-control" id="starting_pressure_psi" name="starting_pressure_psi" min="0">
                            </div>
                            <div class="col-md-3">
                                <label for="ending_pressure_psi" class="form-label">End PSI</label>
                                <input type="number" class="form-control" id="ending_pressure_psi" name="ending_pressure_psi" min="0">
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3 mt-4">Conditions & Equipment</h6>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="gas_type" class="form-label">Gas</label>
                                <select class="form-select" id="gas_type" name="gas_type">
                                    <option value="air">Air</option>
                                    <option value="nitrox_32">Nitrox 32</option>
                                    <option value="nitrox_36">Nitrox 36</option>
                                    <option value="trimix">Trimix</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="water_type" class="form-label">Water Type</label>
                                <select class="form-select" id="water_type" name="water_type">
                                    <option value="salt">Salt Water</option>
                                    <option value="fresh">Fresh Water</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="visibility_feet" class="form-label">Visibility (ft)</label>
                                <input type="number" class="form-control" id="visibility_feet" name="visibility_feet" min="0">
                            </div>
                            <div class="col-md-3">
                                <label for="water_temperature_f" class="form-label">Water Temp (F)</label>
                                <input type="number" class="form-control" id="water_temperature_f" name="water_temperature_f">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="weight_used_lbs" class="form-label">Weight (lbs)</label>
                                <input type="number" class="form-control" id="weight_used_lbs" name="weight_used_lbs" min="0" step="0.5">
                            </div>
                            <div class="col-md-3">
                                <label for="wetsuit_type" class="form-label">Exposure Suit</label>
                                <select class="form-select" id="wetsuit_type" name="wetsuit_type">
                                    <option value="">None</option>
                                    <option value="3mm">3mm Wetsuit</option>
                                    <option value="5mm">5mm Wetsuit</option>
                                    <option value="7mm">7mm Wetsuit</option>
                                    <option value="drysuit">Drysuit</option>
                                    <option value="shorty">Shorty</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="air_temperature_f" class="form-label">Air Temp (F)</label>
                                <input type="number" class="form-control" id="air_temperature_f" name="air_temperature_f">
                            </div>
                            <div class="col-md-3">
                                <label for="weather_conditions" class="form-label">Weather</label>
                                <input type="text" class="form-control" id="weather_conditions" name="weather_conditions"
                                       placeholder="e.g., Sunny, calm">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                      placeholder="Wildlife seen, highlights, notes..."></textarea>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/store/dive-logs" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>Log Dive
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
