<?php
$pageTitle = 'Carousel Slides - Storefront Configuration';
ob_start();
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Carousel Slides</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="/admin/storefront/carousel/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Slide
                    </a>
                    <a href="/admin/storefront" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Configuration
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Manage Carousel Slides</h3>
                <div class="card-tools">
                    <span class="badge badge-info"><?= count($data['slides']) ?> slides</span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($data['slides'])): ?>
                    <div class="text-center p-5">
                        <i class="fas fa-images fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No carousel slides configured. Add your first slide to get started!</p>
                        <a href="/admin/storefront/carousel/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Slide
                        </a>
                    </div>
                <?php else: ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="80">Order</th>
                                <th>Preview</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Button</th>
                                <th width="100">Status</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['slides'] as $slide): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-secondary"><?= $slide['display_order'] ?></span>
                                    </td>
                                    <td>
                                        <img src="<?= htmlspecialchars($slide['image_url']) ?>"
                                             alt="<?= htmlspecialchars($slide['title']) ?>"
                                             style="width: 120px; height: 60px; object-fit: cover; border-radius: 4px;">
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($slide['title']) ?></strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= htmlspecialchars(substr($slide['description'] ?? '', 0, 50)) ?>
                                            <?= strlen($slide['description'] ?? '') > 50 ? '...' : '' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($slide['button_text']): ?>
                                            <span class="badge badge-info">
                                                <?= htmlspecialchars($slide['button_text']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($slide['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/storefront/carousel/edit/<?= $slide['id'] ?>"
                                               class="btn btn-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/admin/storefront/carousel/delete/<?= $slide['id'] ?>"
                                               class="btn btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this slide?')"
                                               title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Tips</h3>
            </div>
            <div class="card-body">
                <ul>
                    <li><strong>Image Dimensions:</strong> Use high-resolution images (1920x600px recommended)</li>
                    <li><strong>Display Order:</strong> Lower numbers appear first in the carousel</li>
                    <li><strong>Image URLs:</strong> You can use URLs from Unsplash, your own server, or upload images to /public/uploads/</li>
                    <li><strong>Buttons:</strong> Optional call-to-action buttons link to any page on your site</li>
                    <li><strong>Active Status:</strong> Only active slides are shown on the storefront</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
?>
