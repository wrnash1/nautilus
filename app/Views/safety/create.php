<?php $this->layout('layouts/admin', ['title' => $title ?? 'New Safety Check']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/safety-checks">Safety Checks</a></li>
                    <li class="breadcrumb-item active">New Check</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clipboard-check me-2"></i>BWRAF Pre-Dive Safety Check</h2>
        <a href="/store/safety-checks" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <form method="POST" action="/store/safety-checks">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <!-- Diver Information -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Diver Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Diver <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Select Diver --</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>">
                                    <?= htmlspecialchars($customer['last_name'] . ', ' . $customer['first_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Buddy (Optional)</label>
                        <select name="buddy_customer_id" class="form-select">
                            <option value="">-- Select Buddy --</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>">
                                    <?= htmlspecialchars($customer['last_name'] . ', ' . $customer['first_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Dive Site</label>
                        <select name="dive_site_id" class="form-select">
                            <option value="">-- Select Site --</option>
                            <?php foreach ($diveSites as $site): ?>
                                <option value="<?= $site['id'] ?>"><?= htmlspecialchars($site['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Trip</label>
                        <select name="trip_id" class="form-select">
                            <option value="">-- Select Trip --</option>
                            <?php foreach ($trips as $trip): ?>
                                <option value="<?= $trip['id'] ?>">
                                    <?= htmlspecialchars($trip['name']) ?> (<?= date('M j', strtotime($trip['departure_date'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Dive Type</label>
                        <select name="dive_type" class="form-select">
                            <option value="recreational">Recreational</option>
                            <option value="training">Training</option>
                            <option value="advanced">Advanced</option>
                            <option value="technical">Technical</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Planned Depth (ft)</label>
                        <input type="number" name="planned_depth_feet" class="form-control" min="0" max="200">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Planned Duration (min)</label>
                        <input type="number" name="planned_duration_minutes" class="form-control" min="0" max="180">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Dive # Today</label>
                        <input type="number" name="dive_number_today" class="form-control" value="1" min="1" max="10">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Starting PSI</label>
                        <input type="number" name="starting_pressure_psi" class="form-control" min="0" max="4000">
                    </div>
                </div>
            </div>
        </div>

        <!-- BWRAF Checklist -->
        <div class="row">
            <!-- B - BCD -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><strong>B</strong> - BCD (Buoyancy Control Device)</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="bcd_inflator_works" id="bcd_inflator" value="1">
                            <label class="form-check-label" for="bcd_inflator">Inflator works properly</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="bcd_deflator_works" id="bcd_deflator" value="1">
                            <label class="form-check-label" for="bcd_deflator">Deflator works properly</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="bcd_overpressure_valve_clear" id="bcd_overpressure" value="1">
                            <label class="form-check-label" for="bcd_overpressure">Overpressure valve clear</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="bcd_straps_secure" id="bcd_straps" value="1">
                            <label class="form-check-label" for="bcd_straps">All straps secure</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="bcd_integrated_weights_secure" id="bcd_weights" value="1">
                            <label class="form-check-label" for="bcd_weights">Integrated weights secure</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- W - Weights -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><strong>W</strong> - Weights</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="weights_adequate" id="weights_adequate" value="1">
                            <label class="form-check-label" for="weights_adequate">Adequate weight for dive</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="weights_secure" id="weights_secure" value="1">
                            <label class="form-check-label" for="weights_secure">Weights secure</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="weights_releasable" id="weights_releasable" value="1">
                            <label class="form-check-label" for="weights_releasable">Weights easily releasable</label>
                        </div>
                        <div>
                            <label class="form-label">Weight Amount (kg)</label>
                            <input type="number" name="weight_amount_kg" class="form-control" step="0.5" min="0" max="30">
                        </div>
                    </div>
                </div>
            </div>

            <!-- R - Releases -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><strong>R</strong> - Releases</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="bcd_releases_located" id="bcd_releases" value="1">
                            <label class="form-check-label" for="bcd_releases">BCD releases located</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="weight_releases_located" id="weight_releases" value="1">
                            <label class="form-check-label" for="weight_releases">Weight releases located</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="all_releases_functional" id="all_releases" value="1">
                            <label class="form-check-label" for="all_releases">All releases functional</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- A - Air -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><strong>A</strong> - Air</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="tank_valve_fully_open" id="tank_valve" value="1">
                            <label class="form-check-label" for="tank_valve">Tank valve fully open</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="air_on_and_breathable" id="air_breathable" value="1">
                            <label class="form-check-label" for="air_breathable">Air on and breathable</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="pressure_gauge_working" id="pressure_gauge" value="1">
                            <label class="form-check-label" for="pressure_gauge">Pressure gauge working</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="air_quality_good" id="air_quality" value="1">
                            <label class="form-check-label" for="air_quality">Air quality good (smell test)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="reserve_pressure_adequate" id="reserve_pressure" value="1">
                            <label class="form-check-label" for="reserve_pressure">Reserve pressure adequate (500+ PSI)</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- F - Final Check -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><strong>F</strong> - Final Check</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="mask_fits_properly" id="mask_fits" value="1">
                                    <label class="form-check-label" for="mask_fits">Mask fits properly</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="mask_defogged" id="mask_defogged" value="1">
                                    <label class="form-check-label" for="mask_defogged">Mask defogged</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="fins_secure" id="fins_secure" value="1">
                                    <label class="form-check-label" for="fins_secure">Fins secure</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="snorkel_attached" id="snorkel" value="1">
                                    <label class="form-check-label" for="snorkel">Snorkel attached</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="computer_functioning" id="computer" value="1">
                                    <label class="form-check-label" for="computer">Dive computer functioning</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="compass_functioning" id="compass" value="1">
                                    <label class="form-check-label" for="compass">Compass functioning</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="knife_accessible" id="knife" value="1">
                                    <label class="form-check-label" for="knife">Knife/cutting tool accessible</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="smb_accessible" id="smb" value="1">
                                    <label class="form-check-label" for="smb">SMB accessible</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="dive_plan_reviewed" id="dive_plan" value="1">
                                    <label class="form-check-label" for="dive_plan">Dive plan reviewed</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="hand_signals_reviewed" id="hand_signals" value="1">
                                    <label class="form-check-label" for="hand_signals">Hand signals reviewed</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="emergency_procedures_reviewed" id="emergency" value="1">
                                    <label class="form-check-label" for="emergency">Emergency procedures reviewed</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="entry_exit_points_identified" id="entry_exit" value="1">
                                    <label class="form-check-label" for="entry_exit">Entry/exit points identified</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical & Conditions -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>Diver Fitness</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="diver_feels_well" id="feels_well" value="1">
                            <label class="form-check-label" for="feels_well">Diver feels well</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="diver_not_fatigued" id="not_fatigued" value="1">
                            <label class="form-check-label" for="not_fatigued">Not fatigued</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="no_alcohol_24hrs" id="no_alcohol" value="1">
                            <label class="form-check-label" for="no_alcohol">No alcohol in 24 hours</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="no_medications_affecting_diving" id="no_meds" value="1">
                            <label class="form-check-label" for="no_meds">No medications affecting diving</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="surface_interval_adequate" id="surface_interval" value="1">
                            <label class="form-check-label" for="surface_interval">Surface interval adequate (if repeat dive)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cloud-sun me-2"></i>Conditions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Water Temp (°F)</label>
                                <input type="number" name="water_temp_fahrenheit" class="form-control" min="32" max="100">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Visibility (ft)</label>
                                <input type="number" name="visibility_feet" class="form-control" min="0" max="200">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Current</label>
                                <select name="current_strength" class="form-select">
                                    <option value="">-- Select --</option>
                                    <option value="none">None</option>
                                    <option value="light">Light</option>
                                    <option value="moderate">Moderate</option>
                                    <option value="strong">Strong</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card mb-4">
            <div class="card-body">
                <label class="form-label">Issues / Notes</label>
                <textarea name="issues_noted" class="form-control" rows="3"
                          placeholder="Note any issues or concerns..."></textarea>
            </div>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
            <a href="/store/safety-checks" class="btn btn-outline-secondary btn-lg me-md-2">Cancel</a>
            <button type="submit" class="btn btn-success btn-lg">
                <i class="bi bi-check-lg me-1"></i>Complete Safety Check
            </button>
        </div>
    </form>
</div>
