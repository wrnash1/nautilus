<?php $this->layout('layouts/admin', ['title' => $title ?? 'Club Dashboard']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item active">Clubs Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people-fill me-2"></i>Diving Clubs Dashboard</h2>
        <a href="/store/clubs/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Create Club
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Clubs</h6>
                            <h2 class="mb-0"><?= $stats['total_clubs'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-people display-4 opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="/store/clubs" class="text-white-50 small">View all clubs <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Active Members</h6>
                            <h2 class="mb-0"><?= $stats['total_members'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-person-check display-4 opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <span class="text-white-50 small">Across all clubs</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Upcoming Events</h6>
                            <h2 class="mb-0"><?= $stats['upcoming_events'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-calendar-event display-4 opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <span class="text-white-50 small">Scheduled events</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Clubs -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Clubs</h5>
                    <a href="/store/clubs" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentClubs)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-people display-1 text-muted"></i>
                            <p class="mt-3 text-muted">No clubs created yet</p>
                            <a href="/store/clubs/create" class="btn btn-primary">Create First Club</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Club Name</th>
                                        <th>Type</th>
                                        <th>Membership</th>
                                        <th>Dues</th>
                                        <th>Created</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentClubs as $club): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($club['club_name']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($club['club_code'] ?? '') ?></small>
                                            </td>
                                            <td><span class="badge bg-secondary"><?= ucfirst(str_replace('_', ' ', $club['club_type'] ?? 'general')) ?></span></td>
                                            <td><?= ucfirst($club['membership_type'] ?? 'open') ?></td>
                                            <td>$<?= number_format($club['annual_dues'] ?? 0, 2) ?></td>
                                            <td><?= date('M j, Y', strtotime($club['created_at'])) ?></td>
                                            <td>
                                                <a href="/store/clubs/<?= $club['id'] ?>" class="btn btn-sm btn-outline-primary">
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
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/store/clubs/create" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle me-2"></i>Create New Club
                        </a>
                        <a href="/store/clubs" class="btn btn-outline-secondary">
                            <i class="bi bi-list me-2"></i>View All Clubs
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Club Types</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            General Diving
                            <span class="badge bg-primary rounded-pill">Most Popular</span>
                        </li>
                        <li class="list-group-item">Technical Diving</li>
                        <li class="list-group-item">Freediving</li>
                        <li class="list-group-item">Underwater Photography</li>
                        <li class="list-group-item">Marine Conservation</li>
                        <li class="list-group-item">Wreck Diving</li>
                        <li class="list-group-item">Cave Diving</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
