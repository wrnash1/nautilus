<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Promotional Announcements</h1>
    <a href="/store/storefront/announcements/create" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Create New
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Active Banners</h6>
    </div>
    <div class="card-body">
        <?php if (empty($announcements)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-megaphone fs-1 mb-3 d-block"></i>
                <p>No announcements found.</p>
                <a href="/store/storefront/announcements/create" class="btn btn-sm btn-outline-primary">Create One</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">Status</th>
                            <th>Content</th>
                            <th>Type</th>
                            <th>Date Range</th>
                            <th>Views/Clicks</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($announcements as $banner): ?>
                            <tr>
                                <td class="text-center align-middle">
                                    <?php if ($banner['is_active']): ?>
                                        <span class="badge bg-success rounded-pill"><i class="bi bi-check"></i></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill"><i class="bi bi-x"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <?php if ($banner['title']): ?>
                                        <strong><?= htmlspecialchars($banner['title']) ?></strong><br>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($banner['content']) ?>
                                    <?php if ($banner['button_text']): ?>
                                        <br><small class="text-muted">Btn: <?= htmlspecialchars($banner['button_text']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <?php
                                        $badges = [
                                            'info' => 'bg-info text-dark',
                                            'warning' => 'bg-warning text-dark',
                                            'danger' => 'bg-danger',
                                            'success' => 'bg-success',
                                            'promotion' => 'bg-primary'
                                        ];
                                    ?>
                                    <span class="badge <?= $badges[$banner['banner_type']] ?? 'bg-secondary' ?>">
                                        <?= ucfirst($banner['banner_type']) ?>
                                    </span>
                                </td>
                                <td class="align-middle small">
                                    <?php if ($banner['start_date']): ?>
                                        <div>From: <?= date('M j, Y', strtotime($banner['start_date'])) ?></div>
                                    <?php endif; ?>
                                    <?php if ($banner['end_date']): ?>
                                        <div>To: <?= date('M j, Y', strtotime($banner['end_date'])) ?></div>
                                    <?php endif; ?>
                                    <?php if (!$banner['start_date'] && !$banner['end_date']): ?>
                                        <span class="text-muted">Always Active</span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle text-center small">
                                    <div><i class="bi bi-eye"></i> <?= $banner['view_count'] ?></div>
                                    <div><i class="bi bi-cursor"></i> <?= $banner['click_count'] ?></div>
                                </td>
                                <td class="align-middle">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/store/storefront/announcements/<?= $banner['id'] ?>/edit" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="/store/storefront/announcements/<?= $banner['id'] ?>/delete" method="POST" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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
