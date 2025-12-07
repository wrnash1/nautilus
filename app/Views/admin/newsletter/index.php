<?php $this->layout('layouts/admin', ['title' => $title ?? 'Newsletter Management']) ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-envelope me-2"></i>Newsletter Management</h2>
        <a href="/store/admin/newsletter/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New Campaign
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($newsletters ?? [])): ?>
            <div class="text-center py-5">
                <i class="bi bi-envelope display-1 text-muted"></i>
                <p class="mt-3 text-muted">No newsletter campaigns yet</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Sent</th>
                            <th>Opens</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($newsletters as $nl): ?>
                        <tr>
                            <td><?= htmlspecialchars($nl['subject']) ?></td>
                            <td><span class="badge bg-secondary"><?= ucfirst($nl['status']) ?></span></td>
                            <td><?= $nl['sent_count'] ?? 0 ?></td>
                            <td><?= $nl['open_count'] ?? 0 ?></td>
                            <td>
                                <a href="/store/admin/newsletter/<?= $nl['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
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
