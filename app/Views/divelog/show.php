<?php $this->layout('layouts/admin', ['title' => $title ?? 'Dive Log Details']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/dive-logs">Dive Logs</a></li>
                    <li class="breadcrumb-item active">Dive #<?= $log['dive_number'] ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-water me-2"></i>Dive #<?= $log['dive_number'] ?>
            <small class="text-muted">- <?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?></small>
        </h2>
        <a href="/store/dive-logs" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Dive Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Dive Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Location</h6>
                            <h4><?= htmlspecialchars($log['site_name_db'] ?? $log['dive_site_name'] ?? 'Unknown Site') ?></h4>
                            <p class="text-muted mb-0">
                                <?= htmlspecialchars($log['location'] ?? '') ?>
                                <?php if ($log['country']): ?>
                                    <?= $log['location'] ? ', ' : '' ?><?= htmlspecialchars($log['country']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="text-muted">Date</h6>
                            <h4><?= date('F j, Y', strtotime($log['dive_date'])) ?></h4>
                            <?php if ($log['entry_time']): ?>
                                <p class="text-muted mb-0">
                                    <?= date('g:i A', strtotime($log['entry_time'])) ?>
                                    <?php if ($log['exit_time']): ?>
                                        - <?= date('g:i A', strtotime($log['exit_time'])) ?>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <!-- Dive Stats Cards -->
                    <div class="row text-center mb-4">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="text-primary mb-0"><?= $log['max_depth_feet'] ?? '-' ?></h3>
                                <small class="text-muted">Max Depth (ft)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="text-success mb-0"><?= $log['bottom_time_minutes'] ?? '-' ?></h3>
                                <small class="text-muted">Bottom Time (min)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="text-info mb-0"><?= $log['visibility_feet'] ?? '-' ?></h3>
                                <small class="text-muted">Visibility (ft)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="text-warning mb-0"><?= $log['water_temperature_f'] ?? '-' ?></h3>
                                <small class="text-muted">Water Temp (F)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th class="text-muted" style="width:40%">Dive Type:</th>
                                    <td><?= ucfirst($log['dive_type'] ?? 'recreational') ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Average Depth:</th>
                                    <td><?= $log['average_depth_feet'] ? $log['average_depth_feet'] . ' ft' : '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Gas:</th>
                                    <td><?= ucfirst(str_replace('_', ' ', $log['gas_type'] ?? 'air')) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Start PSI:</th>
                                    <td><?= $log['starting_pressure_psi'] ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">End PSI:</th>
                                    <td><?= $log['ending_pressure_psi'] ?? '-' ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th class="text-muted" style="width:40%">Water Type:</th>
                                    <td><?= ucfirst($log['water_type'] ?? 'salt') ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Exposure Suit:</th>
                                    <td><?= $log['wetsuit_type'] ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Weight:</th>
                                    <td><?= $log['weight_used_lbs'] ? $log['weight_used_lbs'] . ' lbs' : '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Air Temp:</th>
                                    <td><?= $log['air_temperature_f'] ? $log['air_temperature_f'] . ' F' : '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Weather:</th>
                                    <td><?= htmlspecialchars($log['weather_conditions'] ?? '-') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if ($log['notes']): ?>
                        <hr>
                        <h6>Notes</h6>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($log['notes'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Media -->
            <?php if (!empty($media)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Photos & Videos</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($media as $item): ?>
                            <div class="col-md-4">
                                <?php if ($item['media_type'] === 'photo'): ?>
                                    <img src="<?= htmlspecialchars($item['file_path']) ?>" class="img-fluid rounded" alt="">
                                <?php endif; ?>
                                <?php if ($item['caption']): ?>
                                    <p class="small text-muted mt-1"><?= htmlspecialchars($item['caption']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <!-- Diver Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Diver</h5>
                </div>
                <div class="card-body">
                    <h5><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?></h5>
                    <p class="text-muted mb-1"><?= htmlspecialchars($log['email']) ?></p>
                    <?php if ($log['phone']): ?>
                        <p class="text-muted mb-0"><?= htmlspecialchars($log['phone']) ?></p>
                    <?php endif; ?>
                    <hr>
                    <a href="/store/dive-logs/customer/<?= $log['customer_id'] ?>" class="btn btn-outline-primary btn-sm w-100">
                        View All Dives
                    </a>
                </div>
            </div>

            <!-- Buddy Info -->
            <?php if ($log['buddy_customer_id'] || $log['buddy_name']): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Buddy</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?= htmlspecialchars($log['buddy_name'] ?? 'Assigned Buddy') ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/store/customers/<?= $log['customer_id'] ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-person me-2"></i>View Customer Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
