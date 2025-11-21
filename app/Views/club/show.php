<?php
$pageTitle = $title ?? 'Club Details';
$activeMenu = 'clubs';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-people me-2"></i><?= htmlspecialchars($club['club_name'] ?? 'Club') ?>
        </h1>
        <div>
            <a href="/store/clubs/<?= $club['id'] ?>/events" class="btn btn-outline-info me-2">
                <i class="bi bi-calendar-event me-1"></i>Events
            </a>
            <a href="/store/clubs" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4">
            <!-- Club Info -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Club Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th class="text-muted">Code:</th>
                            <td><?= htmlspecialchars($club['club_code'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Type:</th>
                            <td><span class="badge bg-secondary"><?= ucfirst($club['club_type'] ?? 'general') ?></span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Membership:</th>
                            <td><?= ucfirst(str_replace('_', ' ', $club['membership_type'] ?? 'open')) ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Annual Dues:</th>
                            <td>$<?= number_format($club['annual_dues'] ?? 0, 2) ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Member Discount:</th>
                            <td><?= $club['discount_percentage'] ?? 0 ?>%</td>
                        </tr>
                        <?php if ($club['min_certification_level'] ?? null): ?>
                            <tr>
                                <th class="text-muted">Min Certification:</th>
                                <td><?= htmlspecialchars($club['min_certification_level']) ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>

                    <?php if ($club['description'] ?? null): ?>
                        <hr>
                        <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($club['description'])) ?></p>
                    <?php endif; ?>

                    <?php if ($club['meeting_schedule'] ?? null): ?>
                        <hr>
                        <h6><i class="bi bi-calendar me-1"></i>Meeting Schedule</h6>
                        <p class="mb-1"><?= htmlspecialchars($club['meeting_schedule']) ?></p>
                        <?php if ($club['meeting_location'] ?? null): ?>
                            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($club['meeting_location']) ?></small>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Upcoming Events</h5>
                    <a href="/store/clubs/<?= $club['id'] ?>/events" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($events ?? [])): ?>
                        <p class="text-muted mb-0">No upcoming events</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($events as $event): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= htmlspecialchars($event['event_name']) ?></strong>
                                        <span class="badge bg-primary"><?= date('M j', strtotime($event['event_date'])) ?></span>
                                    </div>
                                    <small class="text-muted"><?= htmlspecialchars($event['location'] ?? '') ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Members -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Members (<?= count($members ?? []) ?>)</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                        <i class="bi bi-person-plus me-1"></i>Add Member
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($members ?? [])): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-person-x display-4 text-muted"></i>
                            <p class="mt-2 text-muted">No members yet</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Member #</th>
                                        <th>Name</th>
                                        <th>Contact</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                        <th>Status</th>
                                        <th>Dues</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($members as $member): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($member['member_number'] ?? '') ?></td>
                                            <td>
                                                <a href="/store/customers/<?= $member['customer_id'] ?>">
                                                    <?= htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($member['email'] ?? '') ?></small><br>
                                                <small class="text-muted"><?= htmlspecialchars($member['phone'] ?? '') ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $roleColors = ['president' => 'danger', 'officer' => 'warning', 'treasurer' => 'info', 'member' => 'secondary'];
                                                $roleColor = $roleColors[$member['member_role']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $roleColor ?>"><?= ucfirst($member['member_role'] ?? 'member') ?></span>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($member['join_date'])) ?></td>
                                            <td>
                                                <?php
                                                $statusColors = ['active' => 'success', 'pending' => 'warning', 'expired' => 'danger', 'suspended' => 'secondary'];
                                                $statusColor = $statusColors[$member['membership_status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $statusColor ?>"><?= ucfirst($member['membership_status'] ?? 'active') ?></span>
                                            </td>
                                            <td>
                                                <?php if ($member['dues_paid'] ?? false): ?>
                                                    <span class="text-success"><i class="bi bi-check-circle"></i> Paid</span>
                                                <?php else: ?>
                                                    <span class="text-danger"><i class="bi bi-x-circle"></i> Unpaid</span>
                                                <?php endif; ?>
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
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/store/clubs/<?= $club['id'] ?>/members" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Search Customer</label>
                        <input type="text" id="memberSearch" class="form-control" placeholder="Search by name or email...">
                        <input type="hidden" name="customer_id" id="memberCustomerId" required>
                    </div>
                    <div id="memberSearchResults" class="list-group mb-3"></div>
                    <div id="selectedMember" style="display: none;" class="alert alert-info"></div>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="member_role" class="form-select">
                            <option value="member">Member</option>
                            <option value="officer">Officer</option>
                            <option value="treasurer">Treasurer</option>
                            <option value="president">President</option>
                        </select>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="dues_paid" class="form-check-input" id="duesPaid">
                        <label class="form-check-label" for="duesPaid">Dues Paid</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('memberSearch')?.addEventListener('input', function() {
    const query = this.value;
    if (query.length < 2) {
        document.getElementById('memberSearchResults').innerHTML = '';
        return;
    }

    fetch('/store/customers/search?q=' + encodeURIComponent(query))
        .then(r => r.json())
        .then(data => {
            const results = document.getElementById('memberSearchResults');
            results.innerHTML = '';
            (data.customers || []).forEach(c => {
                const item = document.createElement('a');
                item.href = '#';
                item.className = 'list-group-item list-group-item-action';
                item.innerHTML = `<strong>${c.first_name} ${c.last_name}</strong><br><small>${c.email || ''}</small>`;
                item.onclick = (e) => {
                    e.preventDefault();
                    document.getElementById('memberCustomerId').value = c.id;
                    document.getElementById('selectedMember').innerHTML = `<strong>${c.first_name} ${c.last_name}</strong> (${c.email || 'No email'})`;
                    document.getElementById('selectedMember').style.display = 'block';
                    document.getElementById('memberSearch').style.display = 'none';
                    results.innerHTML = '';
                };
                results.appendChild(item);
            });
        });
});
</script>
