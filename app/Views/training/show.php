<?php $this->layout('layouts/admin', ['title' => $title ?? 'Training Details']) ?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/store/training">Training</a></li>
            <li class="breadcrumb-item active">Details</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-mortarboard me-2"></i>Training Session</h2>
        <a href="/store/training" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <?php if (isset($training)): ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= htmlspecialchars($training['title'] ?? 'Training Session') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date:</strong> <?= isset($training['date']) ? date('M j, Y', strtotime($training['date'])) : '-' ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Duration:</strong> <?= htmlspecialchars($training['duration'] ?? '-') ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <p><?= nl2br(htmlspecialchars($training['description'] ?? '-')) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Status</h6>
                </div>
                <div class="card-body">
                    <span class="badge bg-<?= ($training['status'] ?? '') === 'completed' ? 'success' : 'warning' ?> fs-5">
                        <?= ucfirst($training['status'] ?? 'Pending') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">Training session not found.</div>
    <?php endif; ?>
</div>
