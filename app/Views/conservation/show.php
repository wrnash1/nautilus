<?php $this->layout('layouts/admin', ['title' => $title ?? 'Conservation Initiative']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/conservation">Conservation</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($initiative['initiative_name'] ?? 'Details') ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-globe-americas me-2"></i><?= htmlspecialchars($initiative['initiative_name'] ?? '') ?></h2>
        <a href="/store/conservation" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Initiative Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Initiative Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Type:</strong>
                            <?php
                                $typeColors = [
                                    'cleanup' => 'primary',
                                    'reef_restoration' => 'success',
                                    'species_monitoring' => 'info',
                                    'education' => 'warning',
                                    'research' => 'secondary',
                                    'advocacy' => 'danger',
                                ];
                                $color = $typeColors[$initiative['initiative_type']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $color ?>">
                                <?= ucfirst(str_replace('_', ' ', $initiative['initiative_type'])) ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <?php if ($initiative['is_ongoing']): ?>
                                <span class="badge bg-success">Ongoing</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Completed</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($initiative['description']): ?>
                        <p><?= nl2br(htmlspecialchars($initiative['description'])) ?></p>
                    <?php endif; ?>

                    <hr>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h3 class="text-primary mb-0"><?= $initiative['participants_count'] ?? 0 ?></h3>
                                <small class="text-muted">Participants</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h3 class="text-success mb-0"><?= number_format($initiative['volunteer_hours'] ?? 0) ?></h3>
                                <small class="text-muted">Hours</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h3 class="text-info mb-0">$<?= number_format($initiative['funds_raised'] ?? 0, 2) ?></h3>
                                <small class="text-muted">Raised</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h3 class="text-warning mb-0"><?= date('M Y', strtotime($initiative['start_date'])) ?></h3>
                                <small class="text-muted">Started</small>
                            </div>
                        </div>
                    </div>

                    <?php if ($initiative['certification_program'] || $initiative['partner_organizations']): ?>
                        <hr>
                        <div class="row">
                            <?php if ($initiative['certification_program']): ?>
                                <div class="col-md-6">
                                    <strong>Certification:</strong> <?= htmlspecialchars($initiative['certification_program']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($initiative['partner_organizations']): ?>
                                <div class="col-md-6">
                                    <strong>Partners:</strong>
                                    <?php
                                        $partners = json_decode($initiative['partner_organizations'], true);
                                        if (is_array($partners)) {
                                            echo htmlspecialchars(implode(', ', $partners));
                                        }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Participants -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Participants</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addParticipantModal">
                        <i class="bi bi-plus-lg me-1"></i>Add Participant
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($participants)): ?>
                        <p class="text-muted text-center py-3">No participants yet</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Level</th>
                                        <th>Hours</th>
                                        <th>Donations</th>
                                        <th>Joined</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($participants as $p): ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($p['email']) ?></small>
                                            </td>
                                            <td><span class="badge bg-secondary"><?= ucfirst($p['participation_level']) ?></span></td>
                                            <td><?= $p['volunteer_hours'] ?? 0 ?></td>
                                            <td>$<?= number_format($p['donations_total'] ?? 0, 2) ?></td>
                                            <td><?= date('M j, Y', strtotime($p['join_date'])) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-success"
                                                        onclick="logHours(<?= $p['id'] ?>)">
                                                    <i class="bi bi-plus"></i> Hours
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Log Hours -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Log Hours</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($participants)): ?>
                        <p class="text-muted small">Add participants first to log hours.</p>
                    <?php else: ?>
                        <form action="/store/conservation/<?= $initiative['id'] ?>/hours" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                            <div class="mb-3">
                                <label for="participant_id" class="form-label">Participant</label>
                                <select class="form-select" id="participant_id" name="participant_id" required>
                                    <option value="">Select...</option>
                                    <?php foreach ($participants as $p): ?>
                                        <option value="<?= $p['id'] ?>">
                                            <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="hours" class="form-label">Hours</label>
                                <input type="number" class="form-control" id="hours" name="hours" min="1" required>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-plus-lg me-1"></i>Log Hours
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Next Event -->
            <?php if ($initiative['next_event_date']): ?>
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">Next Event</h6>
                        <h4><?= date('M j, Y', strtotime($initiative['next_event_date'])) ?></h4>
                        <?php if ($initiative['meeting_frequency']): ?>
                            <small>Meets: <?= htmlspecialchars($initiative['meeting_frequency']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Participant Modal -->
<div class="modal fade" id="addParticipantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/store/conservation/<?= $initiative['id'] ?>/participant" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="modal-header">
                    <h5 class="modal-title">Add Participant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Customer *</label>
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value="">Select customer...</option>
                        </select>
                        <small class="text-muted">Start typing to search customers</small>
                    </div>

                    <div class="mb-3">
                        <label for="participation_level" class="form-label">Participation Level</label>
                        <select class="form-select" id="participation_level" name="participation_level">
                            <option value="volunteer">Volunteer</option>
                            <option value="coordinator">Coordinator</option>
                            <option value="leader">Leader</option>
                            <option value="donor">Donor</option>
                            <option value="supporter">Supporter</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Participant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Load customers for the modal
document.addEventListener('DOMContentLoaded', function() {
    fetch('/api/customers/search?q=')
        .then(r => r.json())
        .then(data => {
            const select = document.getElementById('customer_id');
            if (data.customers) {
                data.customers.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.first_name + ' ' + c.last_name + ' (' + c.email + ')';
                    select.appendChild(opt);
                });
            }
        })
        .catch(() => {});
});
</script>
