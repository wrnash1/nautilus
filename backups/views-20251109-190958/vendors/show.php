<?php
$pageTitle = $vendor['name'];
$activeMenu = 'vendors';

ob_start();
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/vendors">Vendors</a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($vendor['name']) ?></li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-building"></i> <?= htmlspecialchars($vendor['name']) ?></h2>
    <?php if (hasPermission('products.edit')): ?>
    <a href="/vendors/<?= $vendor['id'] ?>/edit" class="btn btn-primary">
        <i class="bi bi-pencil"></i> Edit
    </a>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Contact Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Contact Name:</strong><br>
                    <?= htmlspecialchars($vendor['contact_name'] ?? 'N/A') ?>
                </div>
                <div class="mb-3">
                    <strong>Email:</strong><br>
                    <?php if ($vendor['email']): ?>
                        <a href="mailto:<?= htmlspecialchars($vendor['email']) ?>">
                            <?= htmlspecialchars($vendor['email']) ?>
                        </a>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <strong>Phone:</strong><br>
                    <?php if ($vendor['phone']): ?>
                        <a href="tel:<?= htmlspecialchars($vendor['phone']) ?>">
                            <?= htmlspecialchars($vendor['phone']) ?>
                        </a>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </div>
                <div>
                    <strong>Website:</strong><br>
                    <?php if ($vendor['website']): ?>
                        <a href="<?= htmlspecialchars($vendor['website']) ?>" target="_blank">
                            <?= htmlspecialchars($vendor['website']) ?>
                        </a>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Address</h5>
            </div>
            <div class="card-body">
                <?php if ($vendor['address_line1']): ?>
                    <?= htmlspecialchars($vendor['address_line1']) ?><br>
                    <?php if ($vendor['address_line2']): ?>
                        <?= htmlspecialchars($vendor['address_line2']) ?><br>
                    <?php endif; ?>
                    <?= htmlspecialchars($vendor['city'] ?? '') ?>, 
                    <?= htmlspecialchars($vendor['state'] ?? '') ?> 
                    <?= htmlspecialchars($vendor['postal_code'] ?? '') ?><br>
                    <?= htmlspecialchars($vendor['country'] ?? 'US') ?>
                <?php else: ?>
                    <p class="text-muted">No address on file</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Payment Terms:</strong><br>
                    <?= htmlspecialchars($vendor['payment_terms'] ?? 'N/A') ?>
                </div>
                <div>
                    <strong>Status:</strong><br>
                    <span class="badge bg-<?= $vendor['is_active'] ? 'success' : 'secondary' ?>">
                        <?= $vendor['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($vendor['notes']): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Notes</h5>
    </div>
    <div class="card-body">
        <?= nl2br(htmlspecialchars($vendor['notes'])) ?>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
