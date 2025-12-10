<?php $this->layout('layouts/admin', ['title' => $title ?? 'Buddy Pairs']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item active">Buddy Pairs</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people me-2"></i>Buddy Pair Management</h2>
        <div>
            <a href="/store/buddies/find-match" class="btn btn-outline-primary me-2">
                <i class="bi bi-search me-1"></i>Find Match
            </a>
            <a href="/store/buddies/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Create Pair
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Active Pairs</h6>
                            <h2 class="mb-0"><?= $stats['active_pairs'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-people-fill display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Permanent Partners</h6>
                            <h2 class="mb-0"><?= $stats['permanent_pairs'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-heart-fill display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Buddy Pairs</h5>
        </div>
        <div class="card-body">
            <?php if (empty($pairs)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-people display-1 text-muted"></i>
                    <p class="mt-3 text-muted">No buddy pairs created yet</p>
                    <a href="/store/buddies/create" class="btn btn-primary">Create First Pair</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Diver 1</th>
                                <th>Diver 2</th>
                                <th>Type</th>
                                <th>Dives Together</th>
                                <th>Last Dive</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pairs as $pair): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($pair['diver1_first'] . ' ' . $pair['diver1_last']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($pair['diver1_email']) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($pair['diver2_first'] . ' ' . $pair['diver2_last']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($pair['diver2_email']) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                            $typeColors = [
                                                'permanent' => 'success',
                                                'preferred' => 'primary',
                                                'trip_specific' => 'info',
                                                'single_dive' => 'secondary'
                                            ];
                                            $color = $typeColors[$pair['relationship_type']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $color ?>">
                                            <?= ucfirst(str_replace('_', ' ', $pair['relationship_type'] ?? 'trip_specific')) ?>
                                        </span>
                                    </td>
                                    <td><?= $pair['dives_together'] ?? 0 ?></td>
                                    <td>
                                        <?= $pair['last_dive_date'] ? date('M j, Y', strtotime($pair['last_dive_date'])) : '-' ?>
                                    </td>
                                    <td>
                                        <?php if ($pair['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php elseif ($pair['status'] === 'inactive'): ?>
                                            <span class="badge bg-warning">Inactive</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= ucfirst($pair['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/store/buddies/<?= $pair['id'] ?>" class="btn btn-sm btn-outline-primary">
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
