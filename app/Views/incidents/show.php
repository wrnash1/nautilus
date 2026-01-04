<?php $this->layout('layouts/admin', ['title' => $pageTitle ?? 'Incident Report']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/incidents">Incident Reports</a></li>
                    <li class="breadcrumb-item active">Report #<?= $incident['id'] ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Incident Report #<?= $incident['id'] ?></h2>
        <div>
            <button class="btn btn-outline-secondary me-2" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
            <a href="/store/incidents" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <?php
        $severityClass = match($incident['severity_level'] ?? '') {
            'critical', 'fatal' => 'danger',
            'high', 'serious' => 'warning',
            'medium', 'moderate' => 'info',
            default => 'secondary'
        };
    ?>
    <div class="alert alert-<?= $severityClass ?>">
        <div class="d-flex justify-content-between align-items-center">
            <span>
                <strong>Severity: <?= ucfirst($incident['severity_level'] ?? 'Unknown') ?></strong> |
                Type: <?= ucwords(str_replace('_', ' ', $incident['incident_type'] ?? 'Other')) ?>
            </span>
            <span><?= date('F j, Y', strtotime($incident['incident_date'])) ?></span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Incident Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Incident Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Date & Time</h6>
                            <p class="mb-0">
                                <?= date('F j, Y', strtotime($incident['incident_date'])) ?>
                                <?php if ($incident['incident_time']): ?>
                                    at <?= date('g:i A', strtotime($incident['incident_time'])) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Location</h6>
                            <p class="mb-0"><?= htmlspecialchars($incident['incident_location'] ?? '-') ?></p>
                            <?php if ($incident['incident_gps_latitude'] && $incident['incident_gps_longitude']): ?>
                                <small class="text-muted">
                                    GPS: <?= $incident['incident_gps_latitude'] ?>, <?= $incident['incident_gps_longitude'] ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <h6 class="text-muted">Description</h6>
                    <p><?= nl2br(htmlspecialchars($incident['incident_description'] ?? '-')) ?></p>

                    <?php if (!empty($incident['immediate_actions_taken'])): ?>
                        <h6 class="text-muted">Immediate Actions Taken</h6>
                        <p><?= nl2br(htmlspecialchars($incident['immediate_actions_taken'])) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($incident['medical_treatment_provided'])): ?>
                        <h6 class="text-muted">Medical Treatment Provided</h6>
                        <p><?= nl2br(htmlspecialchars($incident['medical_treatment_provided'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Injured Person -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Injured Person</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-muted" style="width:40%">Name:</th>
                                    <td>
                                        <?php if ($incident['first_name']): ?>
                                            <?= htmlspecialchars($incident['first_name'] . ' ' . $incident['last_name']) ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars($incident['injured_person_name'] ?? '-') ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Age:</th>
                                    <td><?= htmlspecialchars($incident['injured_person_age'] ?? '-') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-muted" style="width:40%">Certification:</th>
                                    <td><?= htmlspecialchars($incident['injured_person_certification'] ?? '-') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Environmental Conditions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Environmental Conditions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6 class="text-muted">Water Temp</h6>
                            <p><?= $incident['water_temperature'] ? $incident['water_temperature'] . '°F' : '-' ?></p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Depth</h6>
                            <p><?= $incident['depth_at_incident'] ? $incident['depth_at_incident'] . ' ft' : '-' ?></p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Visibility</h6>
                            <p><?= htmlspecialchars($incident['visibility'] ?? '-') ?></p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Current</h6>
                            <p><?= ucfirst(htmlspecialchars($incident['current_conditions'] ?? '-')) ?></p>
                        </div>
                    </div>
                    <?php if (!empty($incident['environmental_conditions'])): ?>
                        <h6 class="text-muted">Additional Conditions</h6>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($incident['environmental_conditions'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Witnesses -->
            <?php if (!empty($incident['witness_1_name']) || !empty($incident['witness_2_name'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Witnesses</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($incident['witness_1_name'])): ?>
                            <div class="mb-3">
                                <h6><?= htmlspecialchars($incident['witness_1_name']) ?></h6>
                                <?php if (!empty($incident['witness_1_contact'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($incident['witness_1_contact']) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($incident['witness_1_statement'])): ?>
                                    <p class="mt-2 mb-0 bg-light p-2 rounded"><?= nl2br(htmlspecialchars($incident['witness_1_statement'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($incident['witness_2_name'])): ?>
                            <div>
                                <h6><?= htmlspecialchars($incident['witness_2_name']) ?></h6>
                                <?php if (!empty($incident['witness_2_contact'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($incident['witness_2_contact']) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($incident['witness_2_statement'])): ?>
                                    <p class="mt-2 mb-0 bg-light p-2 rounded"><?= nl2br(htmlspecialchars($incident['witness_2_statement'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card mb-4 border-<?= $severityClass ?>">
                <div class="card-header bg-<?= $severityClass ?> text-<?= in_array($severityClass, ['warning', 'info']) ? 'dark' : 'white' ?>">
                    <h6 class="mb-0">Report Status</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-<?= $incident['padi_notified'] ? 'check-circle text-success' : 'x-circle text-danger' ?>"></i>
                            PADI Notified
                            <?php if ($incident['padi_notification_date']): ?>
                                <br><small class="text-muted ps-4"><?= date('M j, Y', strtotime($incident['padi_notification_date'])) ?></small>
                            <?php endif; ?>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-<?= $incident['insurance_notified'] ? 'check-circle text-success' : 'x-circle text-danger' ?>"></i>
                            Insurance Notified
                            <?php if ($incident['insurance_claim_number']): ?>
                                <br><small class="text-muted ps-4">Claim #<?= htmlspecialchars($incident['insurance_claim_number']) ?></small>
                            <?php endif; ?>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-<?= $incident['emergency_services_called'] ? 'check-circle text-success' : 'dash-circle text-secondary' ?>"></i>
                            Emergency Services Called
                        </li>
                        <li>
                            <i class="bi bi-<?= $incident['follow_up_required'] ? ($incident['follow_up_completed'] ?? false ? 'check-circle text-success' : 'exclamation-circle text-warning') : 'dash-circle text-secondary' ?>"></i>
                            Follow-up <?= $incident['follow_up_required'] ? 'Required' : 'Not Required' ?>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Equipment Involved -->
            <?php if (!empty($incident['equipment_involved'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Equipment Involved</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><?= nl2br(htmlspecialchars($incident['equipment_involved'])) ?></p>
                        <?php if (!empty($incident['equipment_serial_numbers'])): ?>
                            <small class="text-muted">Serial #: <?= htmlspecialchars($incident['equipment_serial_numbers']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Reported By -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Report Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th class="text-muted">Reported By:</th>
                            <td>
                                <?= htmlspecialchars($incident['reported_by_name'] ?? $incident['reported_by_username'] ?? '-') ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Submitted:</th>
                            <td><?= date('M j, Y', strtotime($incident['created_at'])) ?></td>
                        </tr>
                        <?php if (!empty($incident['instructor_name'])): ?>
                            <tr>
                                <th class="text-muted">Instructor:</th>
                                <td>
                                    <?= htmlspecialchars($incident['instructor_name']) ?>
                                    <?php if ($incident['instructor_padi_number']): ?>
                                        <br><small class="text-muted">PADI #<?= htmlspecialchars($incident['instructor_padi_number']) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Photos -->
            <?php if ($incident['photos_attached'] && !empty($incident['photo_filenames'])): ?>
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Photos</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php
                            $photos = json_decode($incident['photo_filenames'], true) ?: [];
                            foreach ($photos as $photo):
                            ?>
                                <div class="col-6">
                                    <a href="/uploads/incidents/<?= htmlspecialchars($photo) ?>" target="_blank">
                                        <img src="/uploads/incidents/<?= htmlspecialchars($photo) ?>" class="img-fluid rounded" alt="Incident photo">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
