<?php
$pageTitle = 'Vendors';
$activeMenu = 'vendors';

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-building"></i> Vendors</h2>
    <?php if (hasPermission('products.create')): ?>
    <a href="/vendors/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Vendor
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($vendors)): ?>
        <p class="text-muted text-center py-4">No vendors found.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vendors as $vendor): ?>
                    <tr>
                        <td>
                            <a href="/vendors/<?= $vendor['id'] ?>">
                                <?= htmlspecialchars($vendor['name']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($vendor['contact_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($vendor['email'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($vendor['phone'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-<?= $vendor['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $vendor['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/vendors/<?= $vendor['id'] ?>" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (hasPermission('products.edit')): ?>
                                <a href="/vendors/<?= $vendor['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (hasPermission('products.delete')): ?>
                                <form method="POST" action="/vendors/<?= $vendor['id'] ?>/delete" class="d-inline" 
                                      onsubmit="return confirm('Are you sure you want to delete this vendor?')">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="/vendors?page=<?= $i ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
