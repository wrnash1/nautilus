<?php $this->layout('layouts/admin', ['title' => $title ?? 'Safety Check Details']) ?>

<?php
    $isPassed = $check['check_status'] === 'passed';
    $isFailed = $check['check_status'] === 'failed';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/safety-checks">Safety Checks</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clipboard-check me-2"></i>Safety Check Details</h2>
        <a href="/store/safety-checks" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <?php if ($isPassed): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            <strong>All checks passed!</strong> Diver is cleared for dive.
        </div>
    <?php elseif ($isFailed): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Safety check failed!</strong> Issues must be resolved before diving.
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="bi bi-hourglass-split me-2"></i>
            <strong>Check incomplete.</strong> Some items need verification.
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Diver Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Diver Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-muted" style="width:40%">Diver:</th>
                                    <td><strong><?= htmlspecialchars($check['first_name'] . ' ' . $check['last_name']) ?></strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Buddy:</th>
                                    <td>
                                        <?php if ($check['buddy_first_name']): ?>
                                            <?= htmlspecialchars($check['buddy_first_name'] . ' ' . $check['buddy_last_name']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Dive Site:</th>
                                    <td><?= htmlspecialchars($check['dive_site_name'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Trip:</th>
                                    <td><?= htmlspecialchars($check['trip_name'] ?? '-') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-muted" style="width:40%">Dive Type:</th>
                                    <td><span class="badge bg-secondary"><?= ucfirst($check['dive_type']) ?></span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Planned Depth:</th>
                                    <td><?= $check['planned_depth_feet'] ? $check['planned_depth_feet'] . ' ft' : '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Planned Duration:</th>
                                    <td><?= $check['planned_duration_minutes'] ? $check['planned_duration_minutes'] . ' min' : '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Starting PSI:</th>
                                    <td><?= $check['starting_pressure_psi'] ?? '-' ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BWRAF Results -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">BWRAF Checklist Results</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- B - BCD -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-info"><strong>B</strong> - BCD</h6>
                            <ul class="list-unstyled mb-0">
                                <li><i class="bi bi-<?= $check['bcd_inflator_works'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Inflator works</li>
                                <li><i class="bi bi-<?= $check['bcd_deflator_works'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Deflator works</li>
                                <li><i class="bi bi-<?= $check['bcd_overpressure_valve_clear'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Overpressure valve clear</li>
                                <li><i class="bi bi-<?= $check['bcd_straps_secure'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Straps secure</li>
                                <li><i class="bi bi-<?= $check['bcd_integrated_weights_secure'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Integrated weights secure</li>
                            </ul>
                        </div>

                        <!-- W - Weights -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-warning"><strong>W</strong> - Weights</h6>
                            <ul class="list-unstyled mb-0">
                                <li><i class="bi bi-<?= $check['weights_adequate'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Adequate weight</li>
                                <li><i class="bi bi-<?= $check['weights_secure'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Weights secure</li>
                                <li><i class="bi bi-<?= $check['weights_releasable'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Weights releasable</li>
                            </ul>
                            <?php if ($check['weight_amount_kg']): ?>
                                <small class="text-muted">Weight: <?= $check['weight_amount_kg'] ?> kg</small>
                            <?php endif; ?>
                        </div>

                        <!-- R - Releases -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-danger"><strong>R</strong> - Releases</h6>
                            <ul class="list-unstyled mb-0">
                                <li><i class="bi bi-<?= $check['bcd_releases_located'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> BCD releases located</li>
                                <li><i class="bi bi-<?= $check['weight_releases_located'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Weight releases located</li>
                                <li><i class="bi bi-<?= $check['all_releases_functional'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> All releases functional</li>
                            </ul>
                        </div>

                        <!-- A - Air -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-success"><strong>A</strong> - Air</h6>
                            <ul class="list-unstyled mb-0">
                                <li><i class="bi bi-<?= $check['tank_valve_fully_open'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Tank valve fully open</li>
                                <li><i class="bi bi-<?= $check['air_on_and_breathable'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Air on and breathable</li>
                                <li><i class="bi bi-<?= $check['pressure_gauge_working'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Pressure gauge working</li>
                                <li><i class="bi bi-<?= $check['air_quality_good'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Air quality good</li>
                                <li><i class="bi bi-<?= $check['reserve_pressure_adequate'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Reserve pressure adequate</li>
                            </ul>
                        </div>

                        <!-- F - Final -->
                        <div class="col-12">
                            <h6 class="text-primary"><strong>F</strong> - Final Check</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="bi bi-<?= $check['mask_fits_properly'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Mask fits properly</li>
                                        <li><i class="bi bi-<?= $check['mask_defogged'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Mask defogged</li>
                                        <li><i class="bi bi-<?= $check['fins_secure'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Fins secure</li>
                                        <li><i class="bi bi-<?= $check['snorkel_attached'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Snorkel attached</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="bi bi-<?= $check['computer_functioning'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Computer functioning</li>
                                        <li><i class="bi bi-<?= $check['compass_functioning'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Compass functioning</li>
                                        <li><i class="bi bi-<?= $check['knife_accessible'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Knife accessible</li>
                                        <li><i class="bi bi-<?= $check['smb_accessible'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> SMB accessible</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="bi bi-<?= $check['dive_plan_reviewed'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Dive plan reviewed</li>
                                        <li><i class="bi bi-<?= $check['hand_signals_reviewed'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Hand signals reviewed</li>
                                        <li><i class="bi bi-<?= $check['emergency_procedures_reviewed'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Emergency procedures</li>
                                        <li><i class="bi bi-<?= $check['entry_exit_points_identified'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>"></i> Entry/exit identified</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Issues -->
            <?php if (!empty($check['issues_noted'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">Issues Noted</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?= nl2br(htmlspecialchars($check['issues_noted'])) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card mb-4 <?= $isPassed ? 'bg-success' : ($isFailed ? 'bg-danger' : 'bg-warning') ?> text-white">
                <div class="card-body text-center">
                    <i class="bi bi-<?= $isPassed ? 'check-circle' : ($isFailed ? 'x-circle' : 'hourglass-split') ?> display-1"></i>
                    <h3 class="mt-3"><?= ucfirst($check['check_status']) ?></h3>
                    <p class="mb-0"><?= date('M j, Y g:i A', strtotime($check['checked_at'] ?? $check['created_at'])) ?></p>
                </div>
            </div>

            <!-- Verified By -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Verified By</h6>
                </div>
                <div class="card-body">
                    <?php if ($check['checker_first_name']): ?>
                        <p class="mb-0">
                            <i class="bi bi-person me-2"></i>
                            <?= htmlspecialchars($check['checker_first_name'] . ' ' . $check['checker_last_name']) ?>
                        </p>
                    <?php else: ?>
                        <p class="text-muted mb-0">Not recorded</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Conditions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Conditions</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th class="text-muted">Water Temp:</th>
                            <td><?= $check['water_temp_fahrenheit'] ? $check['water_temp_fahrenheit'] . '°F' : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Visibility:</th>
                            <td><?= $check['visibility_feet'] ? $check['visibility_feet'] . ' ft' : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Current:</th>
                            <td><?= $check['current_strength'] ? ucfirst($check['current_strength']) : '-' ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Diver Fitness -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Diver Fitness</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li><i class="bi bi-<?= $check['diver_feels_well'] ? 'check text-success' : 'x text-danger' ?>"></i> Feels well</li>
                        <li><i class="bi bi-<?= $check['diver_not_fatigued'] ? 'check text-success' : 'x text-danger' ?>"></i> Not fatigued</li>
                        <li><i class="bi bi-<?= $check['no_alcohol_24hrs'] ? 'check text-success' : 'x text-danger' ?>"></i> No alcohol (24h)</li>
                        <li><i class="bi bi-<?= $check['no_medications_affecting_diving'] ? 'check text-success' : 'x text-danger' ?>"></i> No medications</li>
                        <li><i class="bi bi-<?= $check['surface_interval_adequate'] ? 'check text-success' : 'x text-danger' ?>"></i> Surface interval OK</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
