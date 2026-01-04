<?php
$pageTitle = $title ?? 'Diving Clubs';
$activeMenu = 'clubs';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-people me-2"></i>Diving Clubs
        </h1>
        <div>
            <a href="/store/clubs/dashboard" class="btn btn-outline-info me-2">
                <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
            <a href="/store/clubs/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New Club
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

    <div class="row">
        <?php if (empty($clubs ?? [])): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-people display-1 text-muted"></i>
                        <h4 class="mt-3">No diving clubs yet</h4>
                        <p class="text-muted">Create your first diving club to start building your dive community.</p>
                        <a href="/store/clubs/create" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>Create First Club
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($clubs as $club): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><?= htmlspecialchars($club['club_name']) ?></h5>
                            <small><?= htmlspecialchars($club['club_code'] ?? '') ?></small>
                        </div>
                        <div class="card-body">
                            <p class="text-muted"><?= htmlspecialchars($club['description'] ?? 'No description') ?></p>

                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="bi bi-person-check me-1"></i>Members:</span>
                                <strong><?= number_format($club['active_members'] ?? 0) ?></strong>
                            </div>

                            <?php if ($club['annual_dues'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="bi bi-currency-dollar me-1"></i>Annual Dues:</span>
                                    <strong>$<?= number_format($club['annual_dues'], 2) ?></strong>
                                </div>
                            <?php endif; ?>

                            <?php if ($club['discount_percentage'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="bi bi-percent me-1"></i>Member Discount:</span>
                                    <strong><?= $club['discount_percentage'] ?>%</strong>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="bi bi-tag me-1"></i>Type:</span>
                                <span class="badge bg-secondary"><?= ucfirst($club['club_type'] ?? 'general') ?></span>
                            </div>

                            <?php if ($club['meeting_schedule'] ?? null): ?>
                                <div class="mt-3 pt-3 border-top">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i><?= htmlspecialchars($club['meeting_schedule']) ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="/store/clubs/<?= $club['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                            <a href="/store/clubs/<?= $club['id'] ?>/events" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-calendar-event me-1"></i>Events
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
