<?php
$pageTitle = $pageTitle ?? 'Email Campaigns';
$activeMenu = 'marketing';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-envelope-paper me-2"></i>Email Campaigns
        </h1>
        <a href="/store/marketing/campaigns/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New Campaign
        </a>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="#">All Campaigns</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Scheduled</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Sent</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Drafts</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <?php if (empty($campaigns ?? [])): ?>
                <div class="text-center py-5">
                    <i class="bi bi-envelope-paper display-1 text-muted"></i>
                    <h4 class="mt-3">No campaigns yet</h4>
                    <p class="text-muted">Create your first email campaign to start engaging with your customers.</p>
                    <a href="/store/marketing/campaigns/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Create Campaign
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Campaign Name</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Sent</th>
                                <th>Opens</th>
                                <th>Clicks</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaigns as $campaign): ?>
                                <tr>
                                    <td>
                                        <a href="/store/marketing/campaigns/<?= $campaign['id'] ?>">
                                            <?= htmlspecialchars($campaign['name']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($campaign['subject'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $campaign['status'] === 'sent' ? 'success' : ($campaign['status'] === 'scheduled' ? 'info' : 'secondary') ?>">
                                            <?= ucfirst($campaign['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($campaign['sent_count'] ?? 0) ?></td>
                                    <td><?= number_format($campaign['open_count'] ?? 0) ?></td>
                                    <td><?= number_format($campaign['click_count'] ?? 0) ?></td>
                                    <td><?= date('M j, Y', strtotime($campaign['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/store/marketing/campaigns/<?= $campaign['id'] ?>/edit" class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="/store/marketing/campaigns/<?= $campaign['id'] ?>" class="btn btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
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
