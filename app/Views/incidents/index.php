<?php $this->layout('layouts/admin', ['title' => $pageTitle ?? 'Incident Reports']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item active">Incident Reports</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Incident Reports</h2>
        <a href="/incidents/report" class="btn btn-danger">
            <i class="bi bi-plus-lg me-1"></i>New Report
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($incidents)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-shield-check display-1 text-success"></i>
                    <p class="mt-3 text-muted">No incident reports filed</p>
                    <p class="text-muted small">This is good news! No incidents to report.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Injured Person</th>
                                <th>Severity</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incidents as $incident): ?>
                                <tr>
                                    <td>
                                        <?= date('M j, Y', strtotime($incident['incident_date'])) ?>
                                        <?php if ($incident['incident_time']): ?>
                                            <br><small class="text-muted"><?= date('g:i A', strtotime($incident['incident_time'])) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $typeClass = match($incident['incident_type'] ?? '') {
                                                'dci_injury', 'dci_fatality' => 'bg-danger',
                                                'non_dci_injury', 'non_dci_fatality' => 'bg-warning text-dark',
                                                'equipment_malfunction' => 'bg-info',
                                                'near_miss' => 'bg-secondary',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <span class="badge <?= $typeClass ?>">
                                            <?= ucwords(str_replace('_', ' ', $incident['incident_type'] ?? 'Other')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($incident['first_name']): ?>
                                            <?= htmlspecialchars($incident['first_name'] . ' ' . $incident['last_name']) ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars($incident['injured_person_name'] ?? '-') ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $severityClass = match($incident['severity_level'] ?? '') {
                                                'critical', 'fatal' => 'bg-danger',
                                                'high', 'serious' => 'bg-warning text-dark',
                                                'medium', 'moderate' => 'bg-info',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <span class="badge <?= $severityClass ?>">
                                            <?= ucfirst($incident['severity_level'] ?? 'Unknown') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars(substr($incident['incident_location'] ?? '-', 0, 30)) ?></td>
                                    <td>
                                        <?php if (!empty($incident['follow_up_required']) && empty($incident['follow_up_completed'])): ?>
                                            <span class="badge bg-warning text-dark">Follow-up Needed</span>
                                        <?php elseif (!empty($incident['padi_notified'])): ?>
                                            <span class="badge bg-success">PADI Notified</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/store/incidents/<?= $incident['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mt-4 bg-warning bg-opacity-10 border-warning">
        <div class="card-body">
            <h6><i class="bi bi-info-circle me-2"></i>Important Reminder</h6>
            <p class="mb-0 small">
                All diving incidents must be reported to PADI within 48 hours of occurrence as per PADI Form 10120 requirements.
                Critical incidents should be reported immediately.
            </p>
        </div>
    </div>
</div>
