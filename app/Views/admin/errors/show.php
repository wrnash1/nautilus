<?php $this->layout('layouts/admin', ['title' => $title ?? 'Error Details']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/admin/errors">Error Logs</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bug me-2"></i>Error Details</h2>
        <a href="/store/admin/errors" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <?php if (isset($error)): ?>
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><?= htmlspecialchars($error['type'] ?? 'Error') ?></h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong>Message:</strong>
                <p class="mb-0"><?= htmlspecialchars($error['message'] ?? '-') ?></p>
            </div>
            <div class="mb-3">
                <strong>File:</strong> <?= htmlspecialchars($error['file'] ?? '-') ?>
            </div>
            <div class="mb-3">
                <strong>Line:</strong> <?= htmlspecialchars($error['line'] ?? '-') ?>
            </div>
            <div class="mb-3">
                <strong>Time:</strong> <?= isset($error['created_at']) ? date('M j, Y g:i A', strtotime($error['created_at'])) : '-' ?>
            </div>
            <?php if (!empty($error['stack_trace'])): ?>
            <div>
                <strong>Stack Trace:</strong>
                <pre class="bg-dark text-light p-3 rounded mt-2"><code><?= htmlspecialchars($error['stack_trace']) ?></code></pre>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">Error not found</div>
    <?php endif; ?>
</div>
