<?php $this->layout('layouts/admin', ['title' => $title ?? 'Buddy Pair Details']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/buddies">Buddy Pairs</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people me-2"></i>Buddy Pair Details</h2>
        <a href="/store/buddies" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Divers Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Paired Divers</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="bi bi-person-circle display-4 text-primary"></i>
                                <h5 class="mt-2 mb-1"><?= htmlspecialchars($pair['diver1_first'] . ' ' . $pair['diver1_last']) ?></h5>
                                <p class="text-muted mb-1"><?= htmlspecialchars($pair['diver1_email']) ?></p>
                                <?php if ($pair['diver1_phone']): ?>
                                    <p class="text-muted mb-0"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($pair['diver1_phone']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <i class="bi bi-arrow-left-right display-4 text-success"></i>
                                <p class="mb-0 small text-muted">Buddies</p>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="bi bi-person-circle display-4 text-primary"></i>
                                <h5 class="mt-2 mb-1"><?= htmlspecialchars($pair['diver2_first'] . ' ' . $pair['diver2_last']) ?></h5>
                                <p class="text-muted mb-1"><?= htmlspecialchars($pair['diver2_email']) ?></p>
                                <?php if ($pair['diver2_phone']): ?>
                                    <p class="text-muted mb-0"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($pair['diver2_phone']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pairing Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Pairing Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-muted" style="width:40%">Type:</th>
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
                                </tr>
                                <tr>
                                    <th class="text-muted">Status:</th>
                                    <td>
                                        <?php if ($pair['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php elseif ($pair['status'] === 'inactive'): ?>
                                            <span class="badge bg-warning">Inactive</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= ucfirst($pair['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Paired On:</th>
                                    <td><?= date('M j, Y', strtotime($pair['paired_at'])) ?></td>
                                </tr>
                                <?php if ($pair['trip_name']): ?>
                                <tr>
                                    <th class="text-muted">Trip:</th>
                                    <td><?= htmlspecialchars($pair['trip_name']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-muted" style="width:40%">Dives Together:</th>
                                    <td><span class="badge bg-primary fs-6"><?= $pair['dives_together'] ?? 0 ?></span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Last Dive:</th>
                                    <td><?= $pair['last_dive_date'] ? date('M j, Y', strtotime($pair['last_dive_date'])) : 'Never' ?></td>
                                </tr>
                                <?php if ($pair['compatibility_score']): ?>
                                <tr>
                                    <th class="text-muted">Compatibility:</th>
                                    <td><?= $pair['compatibility_score'] ?>/10</td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <?php if ($pair['notes']): ?>
                        <hr>
                        <h6>Notes</h6>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($pair['notes'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Record Dive -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-water me-2"></i>Record Dive</h5>
                </div>
                <div class="card-body">
                    <form action="/store/buddies/<?= $pair['id'] ?>/dive" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="mb-3">
                            <label for="dive_date" class="form-label">Dive Date</label>
                            <input type="date" class="form-control" id="dive_date" name="dive_date"
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg me-1"></i>Record Dive Together
                        </button>
                    </form>
                </div>
            </div>

            <!-- Update Status -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Update Status</h5>
                </div>
                <div class="card-body">
                    <form action="/store/buddies/<?= $pair['id'] ?>/status" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?= $pair['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $pair['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="completed" <?= $pair['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="dissolved" <?= $pair['status'] === 'dissolved' ? 'selected' : '' ?>>Dissolved</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason (if dissolving)</label>
                            <textarea class="form-control" id="reason" name="reason" rows="2"></textarea>
                        </div>

                        <button type="submit" class="btn btn-outline-primary w-100">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
