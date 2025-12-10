<?php
$pageTitle = 'Dive Sites';
$activeMenu = 'dive-sites';

ob_start();
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1><i class="bi bi-geo-alt-fill"></i> Dive Sites</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="/store/dive-sites/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Dive Site
            </a>
        </div>
    </div>

    <?php
    use App\Core\Database;

    $db = Database::getInstance();
    $diveSites = [];
    $tableExists = true;

    try {
        $stmt = $db->query("
            SELECT id, name, location, country, max_depth, difficulty_level, is_active
            FROM dive_sites
            ORDER BY name
        ");
        $diveSites = $stmt->fetchAll();
    } catch (\PDOException $e) {
        $tableExists = false;
    }
    ?>

    <div class="card">
        <div class="card-body">
            <?php if (!$tableExists): ?>
                <div class="alert alert-info">
                    <h4><i class="bi bi-info-circle"></i> Dive Sites Feature Not Yet Configured</h4>
                    <p>The dive sites database table has not been created yet. This feature will be available once the database migrations are completed.</p>
                    <p class="mb-0"><strong>Feature includes:</strong> Dive site locations, depth information, difficulty ratings, weather integration, and site recommendations.</p>
                </div>
            <?php elseif (empty($diveSites)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No dive sites found.
                    <a href="/store/dive-sites/create">Add your first dive site</a>.
                </div>
            <?php else: ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Country</th>
                            <th>Max Depth</th>
                            <th>Difficulty</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($diveSites as $site): ?>
                        <tr>
                            <td>
                                <a href="/store/dive-sites/<?= $site['id'] ?>">
                                    <strong><?= htmlspecialchars($site['name']) ?></strong>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($site['location'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($site['country'] ?? '-') ?></td>
                            <td><?= $site['max_depth'] ? $site['max_depth'] . 'm' : '-' ?></td>
                            <td>
                                <span class="badge bg-<?= $site['difficulty_level'] === 'beginner' ? 'success' : ($site['difficulty_level'] === 'intermediate' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($site['difficulty_level']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($site['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/store/dive-sites/<?= $site['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/app.php';
?>
